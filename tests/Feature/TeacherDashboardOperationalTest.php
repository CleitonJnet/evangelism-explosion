<?php

use App\Models\Church;
use App\Models\ChurchTemp;
use App\Models\Course;
use App\Models\Role;
use App\Models\StpApproach;
use App\Models\StpSession;
use App\Models\Training;
use App\Models\User;
use App\TrainingStatus;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createTeacherForDashboard(): User
{
    $teacher = User::factory()->create();
    $teacherRole = Role::query()->firstOrCreate(['name' => 'Teacher']);
    $teacher->roles()->syncWithoutDetaching([$teacherRole->id]);

    return $teacher;
}

function createDashboardCourse(string $name): Course
{
    return Course::factory()->create([
        'name' => $name,
        'type' => 'Workshop',
    ]);
}

function moveTrainingIntoDate(Training $training, CarbonInterface $date): void
{
    foreach ($training->eventDates->values() as $index => $eventDate) {
        $eventDate->update(['date' => $date->copy()->addDays($index)->toDateString()]);
    }
}

it('uses the annual period by default on teacher dashboard', function (): void {
    $teacher = createTeacherForDashboard();
    $course = createDashboardCourse('Painel Anual');
    $training = Training::factory()->create([
        'teacher_id' => $teacher->id,
        'course_id' => $course->id,
        'status' => TrainingStatus::Scheduled,
    ]);
    moveTrainingIntoDate($training, now()->startOfYear()->addDays(10));

    $previousYearTraining = Training::factory()->create([
        'teacher_id' => $teacher->id,
        'course_id' => createDashboardCourse('Painel Ano Anterior')->id,
        'status' => TrainingStatus::Scheduled,
    ]);
    moveTrainingIntoDate($previousYearTraining, now()->subYear()->startOfYear()->addDays(10));

    $response = $this
        ->actingAs($teacher)
        ->get(route('app.teacher.dashboard'));

    $response->assertOk();
    $response->assertSee('Período atual: Anual');

    /** @var array<string, mixed> $dashboard */
    $dashboard = $response->viewData('dashboard');
    expect(collect($dashboard['kpis'])->firstWhere('key', 'trainings_in_period')['value'])->toBe(1)
        ->and($dashboard['rangeLabel'])->toContain(now()->startOfYear()->translatedFormat('d/m/Y'))
        ->and($dashboard['rangeLabel'])->toContain(now()->endOfYear()->translatedFormat('d/m/Y'));
});

it('shows only owned and assisted trainings on teacher dashboard', function (): void {
    $teacher = createTeacherForDashboard();
    $assistantCourse = createDashboardCourse('Treinamento Auxiliar');
    $ownedCourse = createDashboardCourse('Treinamento Titular');
    $hiddenCourse = createDashboardCourse('Treinamento Oculto');

    $ownedTraining = Training::factory()->create([
        'teacher_id' => $teacher->id,
        'course_id' => $ownedCourse->id,
        'status' => TrainingStatus::Scheduled,
    ]);
    moveTrainingIntoDate($ownedTraining, now()->addDays(7));

    $assistedTraining = Training::factory()->create([
        'teacher_id' => User::factory()->create()->id,
        'course_id' => $assistantCourse->id,
        'status' => TrainingStatus::Scheduled,
    ]);
    $assistedTraining->assistantTeachers()->attach($teacher->id);
    moveTrainingIntoDate($assistedTraining, now()->addDays(14));

    $hiddenTraining = Training::factory()->create([
        'teacher_id' => User::factory()->create()->id,
        'course_id' => $hiddenCourse->id,
        'status' => TrainingStatus::Scheduled,
    ]);
    moveTrainingIntoDate($hiddenTraining, now()->addDays(21));

    $response = $this
        ->actingAs($teacher)
        ->get(route('app.teacher.dashboard'));

    $response->assertOk();

    /** @var array<string, mixed> $dashboard */
    $dashboard = $response->viewData('dashboard');
    $visibleLabels = collect($dashboard['operational']['nextTrainings'])->pluck('label');

    expect($visibleLabels)->toContain('Workshop - Treinamento Titular')
        ->and($visibleLabels)->toContain('Workshop - Treinamento Auxiliar')
        ->and($visibleLabels)->not->toContain('Workshop - Treinamento Oculto');
});

it('renders the main kpis plus stp and discipleship blocks on teacher dashboard', function (): void {
    $teacher = createTeacherForDashboard();
    $church = Church::factory()->create();
    $pendingTemp = ChurchTemp::query()->create([
        'name' => 'Igreja Pendente Dashboard',
        'city' => 'Recife',
        'state' => 'PE',
        'status' => 'pending',
        'normalized_name' => 'igreja pendente dashboard',
    ]);
    $course = createDashboardCourse('Dashboard Operacional');

    $trainingWithIssue = Training::factory()->create([
        'teacher_id' => $teacher->id,
        'church_id' => $church->id,
        'course_id' => $course->id,
        'status' => TrainingStatus::Scheduled,
    ]);
    moveTrainingIntoDate($trainingWithIssue, now()->addDays(12));

    $studentWithPendingChurch = User::factory()->create([
        'church_id' => null,
        'church_temp_id' => $pendingTemp->id,
    ]);
    $trainingWithIssue->students()->attach($studentWithPendingChurch->id);

    $trainingWithoutIssue = Training::factory()->create([
        'teacher_id' => $teacher->id,
        'church_id' => $church->id,
        'course_id' => createDashboardCourse('Dashboard Concluído')->id,
        'status' => TrainingStatus::Planning,
    ]);
    moveTrainingIntoDate($trainingWithoutIssue, now()->addDays(18));

    $studentWithChurch = User::factory()->create([
        'church_id' => $church->id,
        'church_temp_id' => null,
    ]);
    $trainingWithoutIssue->students()->attach($studentWithChurch->id, [
        'payment' => 1,
    ]);

    $session = StpSession::factory()->create([
        'training_id' => $trainingWithIssue->id,
        'sequence' => 1,
    ]);

    StpApproach::factory()
        ->for($trainingWithIssue, 'training')
        ->for($session, 'session')
        ->withDiscipleship([
            'sessions_planned' => 2,
            'sessions_completed' => 1,
            'follow_up_pending' => true,
        ])
        ->create([
            'created_by_user_id' => $teacher->id,
        ]);

    $response = $this
        ->actingAs($teacher)
        ->get(route('app.teacher.dashboard'));

    $response->assertOk();
    $response->assertSee('Treinamentos no período');
    $response->assertSee('Treinamentos concluídos');
    $response->assertSee('Pendências de programação');
    $response->assertSee('Pendências de validação/igreja');
    $response->assertSee('Sessões STP previstas');
    $response->assertSee('Sessões de discipulado previstas');
    $response->assertSee('Resultados STP');
    $response->assertSee('Continuidade do cuidado');
    $response->assertSee('Dashboard do Professor');
    $response->assertSee('Inscrições por período');
    $response->assertSee('Situação financeira');
});

it('applies the custom date range filter on teacher dashboard within the teacher scope', function (): void {
    $teacher = createTeacherForDashboard();
    $insideCourse = createDashboardCourse('Curso Dentro do Intervalo Professor');
    $outsideCourse = createDashboardCourse('Curso Fora do Intervalo Professor');
    $hiddenCourse = createDashboardCourse('Curso Oculto no Intervalo Professor');

    $insideTraining = Training::factory()->create([
        'teacher_id' => $teacher->id,
        'course_id' => $insideCourse->id,
        'status' => TrainingStatus::Scheduled,
    ]);
    moveTrainingIntoDate($insideTraining, now()->subDays(20));

    $outsideTraining = Training::factory()->create([
        'teacher_id' => $teacher->id,
        'course_id' => $outsideCourse->id,
        'status' => TrainingStatus::Scheduled,
    ]);
    moveTrainingIntoDate($outsideTraining, now()->subMonths(5));

    $hiddenTraining = Training::factory()->create([
        'teacher_id' => User::factory()->create()->id,
        'course_id' => $hiddenCourse->id,
        'status' => TrainingStatus::Scheduled,
    ]);
    moveTrainingIntoDate($hiddenTraining, now()->subDays(18));

    $startDate = now()->subMonth()->toDateString();
    $endDate = now()->subDays(10)->toDateString();

    $response = $this
        ->actingAs($teacher)
        ->get(route('app.teacher.dashboard', [
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]));

    $response->assertOk();
    $response->assertSee('Período atual: Período personalizado');
    $response->assertSee('name="start_date"', false);
    $response->assertSee('name="end_date"', false);
    $response->assertSee('value="'.$startDate.'"', false);
    $response->assertSee('value="'.$endDate.'"', false);
    $response->assertSee('Curso Dentro do Intervalo Professor');
    $response->assertDontSee('Curso Fora do Intervalo Professor');
    $response->assertDontSee('Curso Oculto no Intervalo Professor');

    /** @var array<string, mixed> $dashboard */
    $dashboard = $response->viewData('dashboard');
    $trainingsKpi = collect($dashboard['kpis'])->firstWhere('key', 'trainings_in_period');
    expect($trainingsKpi['value'])->toBe(1)
        ->and($dashboard['filters']['startDate'])->toBe($startDate)
        ->and($dashboard['filters']['endDate'])->toBe($endDate)
        ->and($dashboard['filters']['usingCustomRange'])->toBeTrue();
});

it('uses today as the end date when only the start date is informed on teacher dashboard', function (): void {
    $teacher = createTeacherForDashboard();
    $insideCourse = createDashboardCourse('Curso Do Comeco Ate Hoje Professor');
    $outsideCourse = createDashboardCourse('Curso Antes Do Comeco Professor');

    $insideTraining = Training::factory()->create([
        'teacher_id' => $teacher->id,
        'course_id' => $insideCourse->id,
        'status' => TrainingStatus::Scheduled,
    ]);
    moveTrainingIntoDate($insideTraining, now()->subDays(5));

    $outsideTraining = Training::factory()->create([
        'teacher_id' => $teacher->id,
        'course_id' => $outsideCourse->id,
        'status' => TrainingStatus::Scheduled,
    ]);
    moveTrainingIntoDate($outsideTraining, now()->subMonths(4));

    $startDate = now()->subMonth()->toDateString();
    $today = now()->toDateString();

    $response = $this
        ->actingAs($teacher)
        ->get(route('app.teacher.dashboard', [
            'start_date' => $startDate,
        ]));

    $response->assertOk();
    $response->assertSee('Período atual: Período personalizado');
    $response->assertSee('value="'.$startDate.'"', false);

    /** @var array<string, mixed> $dashboard */
    $dashboard = $response->viewData('dashboard');
    $trainingsKpi = collect($dashboard['kpis'])->firstWhere('key', 'trainings_in_period');

    expect($trainingsKpi['value'])->toBe(1)
        ->and($dashboard['filters']['startDate'])->toBe($startDate)
        ->and($dashboard['filters']['endDate'])->toBe($today)
        ->and($dashboard['filters']['usingCustomRange'])->toBeTrue();
});

it('does not include trainings where the teacher acts only as mentor on teacher dashboard', function (): void {
    $teacher = createTeacherForDashboard();
    $mentorRole = Role::query()->firstOrCreate(['name' => 'Mentor']);
    $teacher->roles()->syncWithoutDetaching([$mentorRole->id]);

    $ownedTraining = Training::factory()->create([
        'teacher_id' => $teacher->id,
        'course_id' => createDashboardCourse('Treinamento Titular Professor')->id,
        'status' => TrainingStatus::Scheduled,
    ]);
    moveTrainingIntoDate($ownedTraining, now()->addDays(5));

    $mentoredOnlyTraining = Training::factory()->create([
        'teacher_id' => User::factory()->create()->id,
        'course_id' => createDashboardCourse('Treinamento So Mentor Professor')->id,
        'status' => TrainingStatus::Scheduled,
    ]);
    $mentoredOnlyTraining->mentors()->attach($teacher->id, ['created_by' => User::factory()->create()->id]);
    moveTrainingIntoDate($mentoredOnlyTraining, now()->addDays(7));

    $response = $this
        ->actingAs($teacher)
        ->get(route('app.teacher.dashboard'));

    $response->assertOk();
    $response->assertSee('Treinamento Titular Professor');
    $response->assertDontSee('Treinamento So Mentor Professor');

    /** @var array<string, mixed> $dashboard */
    $dashboard = $response->viewData('dashboard');
    $trainingsKpi = collect($dashboard['kpis'])->firstWhere('key', 'trainings_in_period');

    expect($trainingsKpi['value'])->toBe(1);
});

it('does not include director-wide trainings on teacher dashboard for users with both roles', function (): void {
    $teacher = createTeacherForDashboard();
    $directorRole = Role::query()->firstOrCreate(['name' => 'Director']);
    $teacher->roles()->syncWithoutDetaching([$directorRole->id]);

    $ownedTraining = Training::factory()->create([
        'teacher_id' => $teacher->id,
        'course_id' => createDashboardCourse('Treinamento Do Professor Diretor')->id,
        'status' => TrainingStatus::Scheduled,
    ]);
    moveTrainingIntoDate($ownedTraining, now()->addDays(5));

    $directorOnlyTraining = Training::factory()->create([
        'teacher_id' => User::factory()->create()->id,
        'course_id' => createDashboardCourse('Treinamento So Da Diretoria')->id,
        'status' => TrainingStatus::Scheduled,
    ]);
    moveTrainingIntoDate($directorOnlyTraining, now()->addDays(7));

    $response = $this
        ->actingAs($teacher)
        ->get(route('app.teacher.dashboard'));

    $response->assertOk();
    $response->assertSee('Treinamento Do Professor Diretor');
    $response->assertDontSee('Treinamento So Da Diretoria');

    /** @var array<string, mixed> $dashboard */
    $dashboard = $response->viewData('dashboard');
    $trainingsKpi = collect($dashboard['kpis'])->firstWhere('key', 'trainings_in_period');

    expect($trainingsKpi['value'])->toBe(1);
});
