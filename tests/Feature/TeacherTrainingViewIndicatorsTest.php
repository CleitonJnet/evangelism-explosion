<?php

use App\Livewire\Pages\App\Teacher\Training\View as TrainingView;
use App\Models\Church;
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
        'pastor' => 'Y',
    ]);
    $regularStudent = User::factory()->create([
        'church_id' => $churchA->id,
        'pastor' => 'N',
    ]);
    $pastorFromAnotherChurch = User::factory()->create([
        'church_id' => $churchB->id,
        'pastor' => 'Y',
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

    Livewire::test(TrainingView::class, ['training' => $training])
        ->assertSet('totalRegistrations', 3)
        ->assertSet('totalParticipatingChurches', 2)
        ->assertSet('totalPastors', 2)
        ->assertSet('totalUsedKits', 2)
        ->assertSet('totalNewChurches', 2);
});
