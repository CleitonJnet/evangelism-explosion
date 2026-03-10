<?php

use App\Enums\StpApproachResult;
use App\Enums\StpApproachStatus;
use App\Enums\StpApproachType;
use App\Models\Church;
use App\Models\Course;
use App\Models\Role;
use App\Models\StpApproach;
use App\Models\StpSession;
use App\Models\StpTeam;
use App\Models\Training;
use App\Models\User;
use App\TrainingStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createUserWithMentorModuleRole(string $roleName): User
{
    $user = User::factory()->create();
    $role = Role::query()->firstOrCreate(['name' => $roleName]);
    $user->roles()->syncWithoutDetaching([$role->id]);

    return $user;
}

function createTrainingForMentorModule(string $courseName, string $churchName, User $teacher, User $mentor): Training
{
    $course = Course::factory()->create([
        'execution' => 0,
        'type' => 'Clinica',
        'name' => $courseName,
    ]);
    $church = Church::factory()->create([
        'name' => $churchName,
    ]);
    $training = Training::factory()->create([
        'course_id' => $course->id,
        'teacher_id' => $teacher->id,
        'church_id' => $church->id,
        'status' => TrainingStatus::Scheduled,
    ]);

    $training->mentors()->attach($mentor->id, ['created_by' => $teacher->id]);

    return $training->fresh();
}

it('shows mentors only their trainings in the mentor module', function (): void {
    $mentor = createUserWithMentorModuleRole('Mentor');
    $otherMentor = createUserWithMentorModuleRole('Mentor');
    $teacher = createUserWithMentorModuleRole('Teacher');

    $visibleTraining = createTrainingForMentorModule('Treinamento Mentor Visivel', 'Igreja Mentor Visivel', $teacher, $mentor);
    createTrainingForMentorModule('Treinamento Mentor Oculto', 'Igreja Mentor Oculta', $teacher, $otherMentor);

    $this->actingAs($mentor)
        ->get(route('app.mentor.trainings.index'))
        ->assertOk()
        ->assertSee('Treinamento Mentor Visivel')
        ->assertDontSee('Treinamento Mentor Oculto');

    $this->actingAs($mentor)
        ->get(route('app.mentor.dashboard'))
        ->assertOk()
        ->assertSee('Treinamento Mentor Visivel')
        ->assertSee(route('app.mentor.trainings.show', $visibleTraining), false);

    $this->actingAs($mentor)
        ->get(route('app.mentor.trainings.show', $visibleTraining))
        ->assertOk();
});

it('does not expose finance or critical edit actions to mentors', function (): void {
    $mentor = createUserWithMentorModuleRole('Mentor');
    $teacher = createUserWithMentorModuleRole('Teacher');
    $training = createTrainingForMentorModule('Treinamento Seguro', 'Igreja Segura', $teacher, $mentor);

    $this->actingAs($mentor)
        ->get(route('app.mentor.trainings.show', $training))
        ->assertOk()
        ->assertDontSee('Financeiro')
        ->assertDontSee('Excluir')
        ->assertDontSee('Relato')
        ->assertDontSee('Professores')
        ->assertDontSee('Sede');

    $this->actingAs($mentor)
        ->get(route('app.teacher.trainings.show', $training))
        ->assertForbidden();
});

it('blocks mentor edit attempts on teacher training update endpoints', function (): void {
    $mentor = createUserWithMentorModuleRole('Mentor');
    $teacher = createUserWithMentorModuleRole('Teacher');
    $training = createTrainingForMentorModule('Treinamento Sem Edicao', 'Igreja Sem Edicao', $teacher, $mentor);

    $this->actingAs($mentor)
        ->put(route('app.teacher.trainings.testimony.update', $training), [
            'notes' => '<p>Tentativa indevida</p>',
        ])
        ->assertForbidden();
});

it('shows mentors the stp and ojt data only for trainings they mentor', function (): void {
    $mentor = createUserWithMentorModuleRole('Mentor');
    $otherMentor = createUserWithMentorModuleRole('Mentor');
    $teacher = createUserWithMentorModuleRole('Teacher');

    $visibleTraining = createTrainingForMentorModule('Treinamento STP Mentor', 'Igreja STP Mentor', $teacher, $mentor);
    $hiddenTraining = createTrainingForMentorModule('Treinamento STP Oculto', 'Igreja STP Oculta', $teacher, $otherMentor);

    $visibleSession = StpSession::query()->create([
        'training_id' => $visibleTraining->id,
        'sequence' => 1,
        'label' => 'Sessão Alpha',
        'starts_at' => now()->addDay(),
        'ends_at' => now()->addDay()->addHours(2),
        'status' => 'planned',
    ]);
    $visibleTeam = StpTeam::query()->create([
        'stp_session_id' => $visibleSession->id,
        'mentor_user_id' => $mentor->id,
        'name' => 'Equipe Alpha',
        'position' => 0,
    ]);
    $visibleTeam->students()->attach(User::factory()->create(['name' => 'Aluno Mentor'])->id, ['position' => 0]);
    StpApproach::query()->create([
        'training_id' => $visibleTraining->id,
        'stp_session_id' => $visibleSession->id,
        'stp_team_id' => $visibleTeam->id,
        'type' => StpApproachType::Visitor->value,
        'status' => StpApproachStatus::Done->value,
        'position' => 1,
        'person_name' => 'Contato Alpha',
        'people_count' => 1,
        'result' => StpApproachResult::Decision->value,
        'created_by_user_id' => $teacher->id,
        'follow_up_scheduled_at' => now()->addDays(3),
    ]);

    $hiddenSession = StpSession::query()->create([
        'training_id' => $hiddenTraining->id,
        'sequence' => 1,
        'label' => 'Sessão Oculta',
        'starts_at' => now()->addDays(2),
        'ends_at' => now()->addDays(2)->addHours(2),
        'status' => 'planned',
    ]);
    StpTeam::query()->create([
        'stp_session_id' => $hiddenSession->id,
        'mentor_user_id' => $otherMentor->id,
        'name' => 'Equipe Oculta',
        'position' => 0,
    ]);

    $this->actingAs($mentor)
        ->get(route('app.mentor.trainings.ojt', $visibleTraining))
        ->assertOk()
        ->assertSee('Sessão Alpha')
        ->assertSee('Equipe Alpha')
        ->assertSee('Decisões')
        ->assertDontSee('Equipe Oculta');

    $this->actingAs($mentor)
        ->get(route('app.mentor.ojt.sessions.show', $visibleSession))
        ->assertOk()
        ->assertSee('Equipe Alpha')
        ->assertDontSee('Equipe Oculta');

    $this->actingAs($mentor)
        ->get(route('app.mentor.trainings.ojt', $hiddenTraining))
        ->assertForbidden();

    $this->actingAs($mentor)
        ->get(route('app.mentor.trainings.show', $hiddenTraining))
        ->assertForbidden();
});
