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
    moveTrainingIntoDate($training, now()->addDays(10));

    $response = $this
        ->actingAs($teacher)
        ->get(route('app.teacher.dashboard'));

    $response->assertOk();
    $response->assertSee('Período atual: Anual');
    $response->assertSee('Painel Anual');
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
    $response->assertSee('Treinamento Titular');
    $response->assertSee('Treinamento Auxiliar');
    $response->assertDontSee('Treinamento Oculto');
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
    $response->assertSee('Treinamentos futuros');
    $response->assertSee('Treinamentos concluídos');
    $response->assertSee('Inscritos');
    $response->assertSee('Pagantes');
    $response->assertSee('Pendências de programação');
    $response->assertSee('Pendências de validação/igreja');
    $response->assertSee('Sessões STP previstas');
    $response->assertSee('Sessões de discipulado previstas');
    $response->assertSee('Impacto evangelístico');
    $response->assertSee('Discipulado paralelo');
    $response->assertSee('Dashboard Operacional');
});
