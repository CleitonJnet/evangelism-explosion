<?php

use App\Models\Church;
use App\Models\EventDate;
use App\Models\HostChurch;
use App\Models\HostChurchAdmin;
use App\Models\Training;
use App\Models\User;
use App\Services\HostChurchReadModel;
use App\TrainingStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('summarizes registered host churches with institutional and operational data', function (): void {
    $church = Church::factory()->create(['name' => 'Igreja Base Central']);
    $hostChurch = HostChurch::query()->create([
        'church_id' => $church->id,
        'since_date' => '2024-01-10',
        'notes' => 'Base homologada para treinamentos.',
    ]);

    $activeCertifiedAdmin = User::factory()->create();
    $inactiveAdmin = User::factory()->create();

    HostChurchAdmin::query()->create([
        'host_church_id' => $hostChurch->id,
        'user_id' => $activeCertifiedAdmin->id,
        'certified_at' => '2024-02-15',
        'status' => 'active',
    ]);

    HostChurchAdmin::query()->create([
        'host_church_id' => $hostChurch->id,
        'user_id' => $inactiveAdmin->id,
        'certified_at' => null,
        'status' => 'inactive',
    ]);

    $completedTraining = Training::factory()->create([
        'church_id' => $church->id,
        'status' => TrainingStatus::Completed,
    ]);
    $completedTraining->eventDates()->delete();

    $planningTraining = Training::factory()->create([
        'church_id' => $church->id,
        'status' => TrainingStatus::Planning,
    ]);
    $planningTraining->eventDates()->delete();

    EventDate::query()->create([
        'training_id' => $completedTraining->id,
        'date' => '2026-02-10',
        'start_time' => '19:00:00',
        'end_time' => '21:00:00',
    ]);

    EventDate::query()->create([
        'training_id' => $planningTraining->id,
        'date' => '2026-03-15',
        'start_time' => '08:00:00',
        'end_time' => '17:00:00',
    ]);

    $summary = app(HostChurchReadModel::class)->registeredHostsQuery()->firstOrFail();

    expect($summary->church->id)->toBe($church->id)
        ->and($summary->notes)->toBe('Base homologada para treinamentos.')
        ->and((int) $summary->admins_count)->toBe(2)
        ->and((int) $summary->active_admins_count)->toBe(1)
        ->and((int) $summary->certified_admins_count)->toBe(1)
        ->and((int) $summary->trainings_count)->toBe(2)
        ->and((int) $summary->completed_trainings_count)->toBe(1)
        ->and($summary->latest_training_event_date)->toBe('2026-03-15');
});

it('lists churches with training activity that are not registered as host churches', function (): void {
    $registeredChurch = Church::factory()->create(['name' => 'Igreja Registrada']);
    HostChurch::query()->create([
        'church_id' => $registeredChurch->id,
    ]);

    $derivedChurch = Church::factory()->create(['name' => 'Igreja Derivada']);
    $inactiveChurch = Church::factory()->create(['name' => 'Igreja Sem Evento']);

    $registeredTraining = Training::factory()->create([
        'church_id' => $registeredChurch->id,
        'status' => TrainingStatus::Completed,
    ]);
    $registeredTraining->eventDates()->delete();

    $derivedTrainingOne = Training::factory()->create([
        'church_id' => $derivedChurch->id,
        'status' => TrainingStatus::Completed,
    ]);
    $derivedTrainingOne->eventDates()->delete();

    $derivedTrainingTwo = Training::factory()->create([
        'church_id' => $derivedChurch->id,
        'status' => TrainingStatus::Scheduled,
    ]);
    $derivedTrainingTwo->eventDates()->delete();

    EventDate::query()->create([
        'training_id' => $registeredTraining->id,
        'date' => '2026-01-20',
        'start_time' => '09:00:00',
        'end_time' => '17:00:00',
    ]);

    EventDate::query()->create([
        'training_id' => $derivedTrainingOne->id,
        'date' => '2026-02-05',
        'start_time' => '09:00:00',
        'end_time' => '17:00:00',
    ]);

    EventDate::query()->create([
        'training_id' => $derivedTrainingTwo->id,
        'date' => '2026-04-22',
        'start_time' => '09:00:00',
        'end_time' => '17:00:00',
    ]);

    $candidates = app(HostChurchReadModel::class)->derivedCandidateChurchesQuery()->get();

    expect($candidates->pluck('id')->all())
        ->toContain($derivedChurch->id)
        ->not->toContain($registeredChurch->id)
        ->not->toContain($inactiveChurch->id);

    $derivedSummary = $candidates->firstWhere('id', $derivedChurch->id);

    expect($derivedSummary)->not->toBeNull()
        ->and((int) $derivedSummary->trainings_count)->toBe(2)
        ->and((int) $derivedSummary->completed_trainings_count)->toBe(1)
        ->and($derivedSummary->latest_training_event_date)->toBe('2026-04-22');
});
