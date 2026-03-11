<?php

use App\Models\Church;
use App\Models\Course;
use App\Models\Role;
use App\Models\StpApproach;
use App\Models\StpSession;
use App\Models\Training;
use App\Models\TrainingNewChurch;
use App\Models\User;
use App\TrainingStatus;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createDirectorForDashboard(): User
{
    $director = User::factory()->create();
    $role = Role::query()->firstOrCreate(['name' => 'Director']);
    $director->roles()->syncWithoutDetaching([$role->id]);

    return $director;
}

function createNonDirectorForDashboard(): User
{
    $teacher = User::factory()->create();
    $role = Role::query()->firstOrCreate(['name' => 'Teacher']);
    $teacher->roles()->syncWithoutDetaching([$role->id]);

    return $teacher;
}

function moveDirectorTrainingIntoDate(Training $training, CarbonInterface $date): void
{
    foreach ($training->eventDates->values() as $index => $eventDate) {
        $eventDate->update(['date' => $date->copy()->addDays($index)->toDateString()]);
    }
}

it('uses the annual period by default on director dashboard', function (): void {
    $director = createDirectorForDashboard();
    $course = Course::factory()->create([
        'name' => 'Curso Nacional',
        'execution' => 0,
    ]);
    $training = Training::factory()->create([
        'course_id' => $course->id,
        'status' => TrainingStatus::Scheduled,
    ]);
    moveDirectorTrainingIntoDate($training, now()->addDays(10));

    $response = $this
        ->actingAs($director)
        ->get(route('app.director.dashboard'));

    $response->assertOk();
    $response->assertSee('Período atual: Anual');
    $response->assertSee('Curso Nacional');
});

it('loads the national kpis and leadership teachers list', function (): void {
    $director = createDirectorForDashboard();
    $church = Church::factory()->create(['name' => 'Igreja Nacional']);
    $leadershipCourse = Course::factory()->create([
        'name' => 'Mentorear para Multiplicar',
        'execution' => 0,
    ]);
    $implementationCourse = Course::factory()->create([
        'name' => 'Curso de Implementação',
        'execution' => 1,
    ]);

    $leadershipTeacher = User::factory()->create([
        'name' => 'Professora Líder',
        'church_id' => $church->id,
    ]);
    $teacherRole = Role::query()->firstOrCreate(['name' => 'Teacher']);
    $leadershipTeacher->roles()->syncWithoutDetaching([$teacherRole->id]);
    $leadershipCourse->teachers()->attach($leadershipTeacher->id, ['status' => 1]);

    $training = Training::factory()->create([
        'course_id' => $leadershipCourse->id,
        'church_id' => $church->id,
        'teacher_id' => $leadershipTeacher->id,
        'price' => '100,00',
        'price_church' => '20,00',
        'discount' => '10,00',
        'status' => TrainingStatus::Completed,
    ]);
    moveDirectorTrainingIntoDate($training, now()->subDays(20));

    $paidStudent = User::factory()->create([
        'church_id' => $church->id,
        'is_pastor' => true,
    ]);
    $training->students()->attach($paidStudent->id, [
        'payment' => 1,
    ]);

    $session = StpSession::factory()->create([
        'training_id' => $training->id,
        'sequence' => 1,
    ]);

    StpApproach::factory()
        ->for($training, 'training')
        ->for($session, 'session')
        ->withDiscipleship([
            'status' => 'in_progress',
            'sessions_planned' => 2,
            'sessions_completed' => 1,
            'follow_up_pending' => true,
        ])
        ->create([
            'created_by_user_id' => $leadershipTeacher->id,
            'gospel_explained_times' => 1,
            'people_count' => 2,
            'status' => 'done',
            'result' => 'decision',
            'follow_up_scheduled_at' => now(),
        ]);

    TrainingNewChurch::query()->create([
        'training_id' => $training->id,
        'church_id' => Church::factory()->create()->id,
        'created_by' => $director->id,
    ]);

    $response = $this
        ->actingAs($director)
        ->get(route('app.director.dashboard'));

    $response->assertOk();
    $response->assertSee('Treinamentos no período');
    $response->assertSee('Igrejas alcançadas');
    $response->assertSee('Novas igrejas');
    $response->assertSee('Professores ativos');
    $response->assertSee('Pagantes');
    $response->assertSee('Taxa de pagamento');
    $response->assertSee('Pastores treinados');
    $response->assertSee('Evangelho explicado');
    $response->assertSee('Saldo EE');
    $response->assertSee('Professores por curso de liderança');
    $response->assertSee('Mentorear para Multiplicar');
    $response->assertSee('Professora Líder');
    $response->assertDontSee('Curso de Implementação');
});

it('applies the period filter on director dashboard', function (): void {
    $director = createDirectorForDashboard();
    $recentCourse = Course::factory()->create([
        'name' => 'Curso Recente Dashboard Diretor',
        'execution' => 0,
    ]);
    $olderCourse = Course::factory()->create([
        'name' => 'Curso Antigo Dashboard Diretor',
        'execution' => 0,
    ]);

    $recentTraining = Training::factory()->create([
        'course_id' => $recentCourse->id,
        'status' => TrainingStatus::Scheduled,
    ]);
    moveDirectorTrainingIntoDate($recentTraining, now()->subMonths(2));

    $olderTraining = Training::factory()->create([
        'course_id' => $olderCourse->id,
        'status' => TrainingStatus::Scheduled,
    ]);
    moveDirectorTrainingIntoDate($olderTraining, now()->subMonths(8));

    $response = $this
        ->actingAs($director)
        ->get(route('app.director.dashboard', ['period' => 'quarter']));

    $response->assertOk();
    $response->assertSee('Período atual: Trimestral');

    /** @var array<string, mixed> $dashboard */
    $dashboard = $response->viewData('dashboard');
    $trainingsKpi = collect($dashboard['kpis'])->firstWhere('key', 'trainings');
    $distributionChart = collect($dashboard['charts'])->firstWhere('id', 'director-distribution-course');

    expect($trainingsKpi['value'])->toBe(1)
        ->and($distributionChart['labels'])->toContain('Curso Recente Dashboard Diretor')
        ->and($distributionChart['labels'])->not->toContain('Curso Antigo Dashboard Diretor');
});

it('applies the custom date range filter on director dashboard', function (): void {
    $director = createDirectorForDashboard();
    $insideCourse = Course::factory()->create([
        'name' => 'Curso Dentro do Intervalo',
        'execution' => 0,
    ]);
    $outsideCourse = Course::factory()->create([
        'name' => 'Curso Fora do Intervalo',
        'execution' => 0,
    ]);

    $insideTraining = Training::factory()->create([
        'course_id' => $insideCourse->id,
        'status' => TrainingStatus::Scheduled,
    ]);
    moveDirectorTrainingIntoDate($insideTraining, now()->subDays(20));

    $outsideTraining = Training::factory()->create([
        'course_id' => $outsideCourse->id,
        'status' => TrainingStatus::Scheduled,
    ]);
    moveDirectorTrainingIntoDate($outsideTraining, now()->subMonths(5));

    $response = $this
        ->actingAs($director)
        ->get(route('app.director.dashboard', [
            'start_date' => now()->subMonth()->toDateString(),
            'end_date' => now()->subDays(10)->toDateString(),
        ]));

    $response->assertOk();
    $response->assertSee('Período atual: Período personalizado');
    $response->assertSee('name="start_date"', false);
    $response->assertSee('name="end_date"', false);
    $response->assertSee('value="'.now()->subMonth()->toDateString().'"', false);
    $response->assertSee('value="'.now()->subDays(10)->toDateString().'"', false);

    /** @var array<string, mixed> $dashboard */
    $dashboard = $response->viewData('dashboard');
    $trainingsKpi = collect($dashboard['kpis'])->firstWhere('key', 'trainings');
    $distributionChart = collect($dashboard['charts'])->firstWhere('id', 'director-distribution-course');

    expect($trainingsKpi['value'])->toBe(1)
        ->and($distributionChart['labels'])->toContain('Curso Dentro do Intervalo')
        ->and($distributionChart['labels'])->not->toContain('Curso Fora do Intervalo')
        ->and($dashboard['filters']['usingCustomRange'])->toBeTrue();
});

it('uses today as the end date when only the start date is informed', function (): void {
    $director = createDirectorForDashboard();
    $insideCourse = Course::factory()->create([
        'name' => 'Curso Do Comeco Ate Hoje',
        'execution' => 0,
    ]);
    $outsideCourse = Course::factory()->create([
        'name' => 'Curso Antes Do Comeco',
        'execution' => 0,
    ]);

    $insideTraining = Training::factory()->create([
        'course_id' => $insideCourse->id,
        'status' => TrainingStatus::Scheduled,
    ]);
    moveDirectorTrainingIntoDate($insideTraining, now()->subDays(5));

    $outsideTraining = Training::factory()->create([
        'course_id' => $outsideCourse->id,
        'status' => TrainingStatus::Scheduled,
    ]);
    moveDirectorTrainingIntoDate($outsideTraining, now()->subMonths(4));

    $startDate = now()->subMonth()->toDateString();
    $today = now()->toDateString();

    $response = $this
        ->actingAs($director)
        ->get(route('app.director.dashboard', [
            'start_date' => $startDate,
        ]));

    $response->assertOk();
    $response->assertSee('Período atual: Período personalizado');
    $response->assertSee('value="'.$startDate.'"', false);

    /** @var array<string, mixed> $dashboard */
    $dashboard = $response->viewData('dashboard');
    $trainingsKpi = collect($dashboard['kpis'])->firstWhere('key', 'trainings');
    $distributionChart = collect($dashboard['charts'])->firstWhere('id', 'director-distribution-course');

    expect($trainingsKpi['value'])->toBe(1)
        ->and($distributionChart['labels'])->toContain('Curso Do Comeco Ate Hoje')
        ->and($distributionChart['labels'])->not->toContain('Curso Antes Do Comeco')
        ->and($dashboard['filters']['startDate'])->toBe($startDate)
        ->and($dashboard['filters']['endDate'])->toBe($today)
        ->and($dashboard['filters']['usingCustomRange'])->toBeTrue();
});

it('restricts the dashboard to directors only', function (): void {
    $teacher = createNonDirectorForDashboard();

    $this->actingAs($teacher)
        ->get(route('app.director.dashboard'))
        ->assertForbidden();
});
