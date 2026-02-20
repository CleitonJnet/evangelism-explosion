<?php

use App\Livewire\Pages\App\Teacher\Training\StpApproachesBoard;
use App\Models\Church;
use App\Models\Role;
use App\Models\StpApproach;
use App\Models\StpSession;
use App\Models\StpTeam;
use App\Models\Training;
use App\Models\User;
use App\Services\Stp\StpBoardService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function createTeacherForStpBoard(): User
{
    $teacher = User::factory()->create(['church_id' => null]);
    $teacherRole = Role::query()->firstOrCreate(['name' => 'Teacher']);
    $teacher->roles()->syncWithoutDetaching([$teacherRole->id]);

    return $teacher;
}

it('renders the stp approaches board page', function () {
    $teacher = createTeacherForStpBoard();
    $church = Church::factory()->create();
    $training = Training::factory()->create([
        'teacher_id' => $teacher->id,
        'church_id' => $church->id,
    ]);

    $response = $this
        ->actingAs($teacher)
        ->get(route('app.teacher.trainings.stp.approaches', $training));

    $response->assertOk();
    $response->assertSeeLivewire(StpApproachesBoard::class);
    $response->assertSeeText('Distribuição de Visitas STP');
});

it('moves approaches between queue and team with persistence', function () {
    $teacher = createTeacherForStpBoard();
    $church = Church::factory()->create();
    $training = Training::factory()->create([
        'teacher_id' => $teacher->id,
        'church_id' => $church->id,
    ]);

    $mentor = User::factory()->create(['name' => 'Mentor 01']);
    $session = StpSession::query()->create([
        'training_id' => $training->id,
        'sequence' => 1,
    ]);
    $team = StpTeam::query()->create([
        'stp_session_id' => $session->id,
        'mentor_user_id' => $mentor->id,
        'name' => 'Equipe 01',
        'position' => 0,
    ]);

    $approach = StpApproach::query()->create([
        'training_id' => $training->id,
        'stp_session_id' => $session->id,
        'stp_team_id' => null,
        'type' => 'visitor',
        'status' => 'planned',
        'position' => 0,
        'person_name' => 'Pessoa da Fila',
        'created_by_user_id' => $teacher->id,
    ]);
    $existingTeamApproach = StpApproach::query()->create([
        'training_id' => $training->id,
        'stp_session_id' => $session->id,
        'stp_team_id' => $team->id,
        'type' => 'visitor',
        'status' => 'assigned',
        'position' => 0,
        'person_name' => 'Pessoa da Equipe',
        'created_by_user_id' => $teacher->id,
    ]);

    Livewire::actingAs($teacher)
        ->test(StpApproachesBoard::class, ['training' => $training])
        ->call('selectSession', $session->id)
        ->call('moveApproach', $approach->id, 'team:'.$team->id, 1, 'queue');

    $approach->refresh();

    expect($approach->stp_team_id)->toBe($team->id)
        ->and($approach->status->value)->toBe('assigned')
        ->and($approach->position)->toBe(1)
        ->and($existingTeamApproach->fresh()->position)->toBe(0);

    Livewire::actingAs($teacher)
        ->test(StpApproachesBoard::class, ['training' => $training])
        ->call('selectSession', $session->id)
        ->call('moveApproach', $approach->id, 'queue', 0, 'team:'.$team->id);

    $approach->refresh();

    expect($approach->stp_team_id)->toBeNull()
        ->and($approach->status->value)->toBe('planned')
        ->and($approach->position)->toBe(0);
});

it('saves approach draft through the report modal', function () {
    $teacher = createTeacherForStpBoard();
    $church = Church::factory()->create();
    $training = Training::factory()->create([
        'teacher_id' => $teacher->id,
        'church_id' => $church->id,
    ]);
    $session = StpSession::query()->create([
        'training_id' => $training->id,
        'sequence' => 1,
    ]);
    $approach = StpApproach::query()->create([
        'training_id' => $training->id,
        'stp_session_id' => $session->id,
        'stp_team_id' => null,
        'type' => 'visitor',
        'status' => 'planned',
        'position' => 0,
        'person_name' => 'Pessoa da Fila',
        'created_by_user_id' => $teacher->id,
    ]);

    Livewire::actingAs($teacher)
        ->test(StpApproachesBoard::class, ['training' => $training])
        ->call('selectSession', $session->id)
        ->call('openApproachModal', $approach->id)
        ->set('form.person_name', 'Pessoa Atualizada')
        ->set('form.approach_date', now()->toDateString())
        ->set('form.payload.notes', 'Observação visitante')
        ->set('form.payload.listeners.0.name', 'Ouvidor 1')
        ->set('form.payload.listeners.0.diagnostic_answer', 'christ')
        ->set('form.payload.listeners.0.result', 'decision')
        ->call('saveApproachDraft');

    $approach->refresh();

    expect($approach->person_name)->toBe('Pessoa Atualizada')
        ->and(data_get($approach->payload, 'notes'))->toBe('Observação visitante')
        ->and(data_get($approach->payload, 'listeners.0.name'))->toBe('Ouvidor 1')
        ->and($approach->people_count)->toBe(1)
        ->and($approach->gospel_explained_times)->toBe(1)
        ->and($approach->status->value)->toBe('planned')
        ->and($approach->reported_by_user_id)->toBe($teacher->id);
});

it('marks approach as done and fills reported by', function () {
    $teacher = createTeacherForStpBoard();
    $church = Church::factory()->create();
    $training = Training::factory()->create([
        'teacher_id' => $teacher->id,
        'church_id' => $church->id,
    ]);
    $mentor = User::factory()->create(['name' => 'Mentor 01']);
    $session = StpSession::query()->create([
        'training_id' => $training->id,
        'sequence' => 1,
    ]);
    $team = StpTeam::query()->create([
        'stp_session_id' => $session->id,
        'mentor_user_id' => $mentor->id,
        'name' => 'Equipe 01',
        'position' => 0,
    ]);
    $approach = StpApproach::query()->create([
        'training_id' => $training->id,
        'stp_session_id' => $session->id,
        'stp_team_id' => $team->id,
        'type' => 'security_questionnaire',
        'status' => 'assigned',
        'position' => 0,
        'person_name' => 'Pessoa da Equipe',
        'created_by_user_id' => $teacher->id,
    ]);

    Livewire::actingAs($teacher)
        ->test(StpApproachesBoard::class, ['training' => $training])
        ->call('selectSession', $session->id)
        ->call('openApproachModal', $approach->id)
        ->set('form.approach_date', now()->toDateString())
        ->set('form.payload.listeners.0.name', 'Ouvidor 1')
        ->set('form.payload.listeners.0.diagnostic_answer', 'works')
        ->set('form.payload.listeners.0.result', 'no_decision_interested')
        ->call('markAsDone');

    $approach->refresh();

    expect($approach->status->value)->toBe('done')
        ->and($approach->people_count)->toBe(1)
        ->and($approach->reported_by_user_id)->toBe($teacher->id);
});

it('requires team assignment before concluding approach', function () {
    $teacher = createTeacherForStpBoard();
    $church = Church::factory()->create();
    $training = Training::factory()->create([
        'teacher_id' => $teacher->id,
        'church_id' => $church->id,
    ]);
    $session = StpSession::query()->create([
        'training_id' => $training->id,
        'sequence' => 1,
    ]);
    $approach = StpApproach::query()->create([
        'training_id' => $training->id,
        'stp_session_id' => $session->id,
        'stp_team_id' => null,
        'type' => 'visitor',
        'status' => 'planned',
        'position' => 0,
        'person_name' => 'Sem Equipe',
        'created_by_user_id' => $teacher->id,
    ]);

    Livewire::actingAs($teacher)
        ->test(StpApproachesBoard::class, ['training' => $training])
        ->call('selectSession', $session->id)
        ->call('openApproachModal', $approach->id)
        ->set('form.approach_date', now()->toDateString())
        ->set('form.payload.listeners.0.name', 'Ouvidor 1')
        ->set('form.payload.listeners.0.diagnostic_answer', 'christ')
        ->set('form.payload.listeners.0.result', 'decision')
        ->call('markAsDone')
        ->assertHasErrors(['form.stp_team_id']);
});

it('allows owner teacher to mark approach as reviewed', function () {
    $teacher = createTeacherForStpBoard();
    $church = Church::factory()->create();
    $training = Training::factory()->create([
        'teacher_id' => $teacher->id,
        'church_id' => $church->id,
    ]);
    $mentor = User::factory()->create(['name' => 'Mentor 01']);
    $session = StpSession::query()->create([
        'training_id' => $training->id,
        'sequence' => 1,
    ]);
    $team = StpTeam::query()->create([
        'stp_session_id' => $session->id,
        'mentor_user_id' => $mentor->id,
        'name' => 'Equipe 01',
        'position' => 0,
    ]);
    $approach = StpApproach::query()->create([
        'training_id' => $training->id,
        'stp_session_id' => $session->id,
        'stp_team_id' => $team->id,
        'type' => 'visitor',
        'status' => 'done',
        'position' => 0,
        'person_name' => 'Pessoa Revisão',
        'created_by_user_id' => $teacher->id,
        'reported_by_user_id' => $teacher->id,
    ]);

    Livewire::actingAs($teacher)
        ->test(StpApproachesBoard::class, ['training' => $training])
        ->call('selectSession', $session->id)
        ->call('openApproachModal', $approach->id)
        ->call('markAsReviewed');

    $approach->refresh();

    expect($approach->status->value)->toBe('reviewed')
        ->and($approach->reviewed_by_user_id)->toBe($teacher->id)
        ->and($approach->reviewed_at)->not->toBeNull();
});

it('prevents mentor from moving approach of another mentor team', function () {
    $teacher = createTeacherForStpBoard();
    $church = Church::factory()->create();
    $training = Training::factory()->create([
        'teacher_id' => $teacher->id,
        'church_id' => $church->id,
    ]);

    $mentorActor = User::factory()->create(['name' => 'Mentor Ator']);
    $mentorOwner = User::factory()->create(['name' => 'Mentor Dono']);

    $session = StpSession::factory()->create([
        'training_id' => $training->id,
        'sequence' => 1,
    ]);

    $teamOwner = StpTeam::factory()->create([
        'stp_session_id' => $session->id,
        'mentor_user_id' => $mentorOwner->id,
        'name' => 'Equipe 01',
        'position' => 0,
    ]);

    $approach = StpApproach::factory()->create([
        'training_id' => $training->id,
        'stp_session_id' => $session->id,
        'stp_team_id' => $teamOwner->id,
        'status' => 'assigned',
        'position' => 0,
        'created_by_user_id' => $teacher->id,
    ]);

    expect(function () use ($mentorActor, $approach, $session): void {
        $this->actingAs($mentorActor);

        app(StpBoardService::class)->moveApproach(
            $approach->id,
            $session->id,
            null,
            0,
            $approach->stp_team_id,
        );
    })->toThrow(AuthorizationException::class);
});
