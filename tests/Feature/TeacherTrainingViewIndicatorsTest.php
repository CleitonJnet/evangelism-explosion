<?php

use App\Livewire\Pages\App\Teacher\Training\View as TrainingView;
use App\Models\Church;
use App\Models\StpApproach;
use App\Models\StpSession;
use App\Models\Training;
use App\Models\TrainingNewChurch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('builds registration indicators from enrolled students instead of training columns', function () {
    $hostChurch = Church::factory()->create();
    $churchA = Church::factory()->create();
    $churchB = Church::factory()->create();

    $training = Training::factory()->create([
        'church_id' => $hostChurch->id,
    ]);

    $pastorStudent = User::factory()->create([
        'church_id' => $churchA->id,
        'is_pastor' => 1,
    ]);
    $regularStudent = User::factory()->create([
        'church_id' => $churchA->id,
        'is_pastor' => 0,
    ]);
    $pastorFromAnotherChurch = User::factory()->create([
        'church_id' => $churchB->id,
        'is_pastor' => 1,
    ]);

    $training->students()->attach($pastorStudent->id, ['kit' => 1, 'accredited' => 0, 'payment' => 0]);
    $training->students()->attach($regularStudent->id, ['kit' => 0, 'accredited' => 0, 'payment' => 0]);
    $training->students()->attach($pastorFromAnotherChurch->id, ['kit' => 1, 'accredited' => 0, 'payment' => 0]);

    $newChurchA = Church::factory()->create();
    $newChurchB = Church::factory()->create();
    $actor = User::factory()->create();

    TrainingNewChurch::query()->create([
        'training_id' => $training->id,
        'church_id' => $newChurchA->id,
        'source_church_temp_id' => null,
        'created_by' => $actor->id,
    ]);
    TrainingNewChurch::query()->create([
        'training_id' => $training->id,
        'church_id' => $newChurchB->id,
        'source_church_temp_id' => null,
        'created_by' => $actor->id,
    ]);

    $session = StpSession::factory()->create([
        'training_id' => $training->id,
    ]);

    StpApproach::factory()->create([
        'training_id' => $training->id,
        'stp_session_id' => $session->id,
        'status' => 'done',
        'payload' => [
            'listeners' => [
                ['name' => 'Pessoa 1', 'result' => 'decision'],
                ['name' => 'Pessoa 2', 'result' => 'no_decision_interested'],
                ['name' => 'Pessoa 3', 'result' => 'decision'],
            ],
        ],
        'result' => null,
        'people_count' => null,
    ]);

    Livewire::test(TrainingView::class, ['training' => $training])
        ->assertSet('totalRegistrations', 3)
        ->assertSet('totalParticipatingChurches', 2)
        ->assertSet('totalPastors', 2)
        ->assertSet('totalUsedKits', 2)
        ->assertSet('totalNewChurches', 2)
        ->assertSet('totalDecisions', 2)
        ->assertSet('resumoStp.decisao', 2);
});
