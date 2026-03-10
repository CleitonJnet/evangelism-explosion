<?php

use App\Livewire\Pages\App\Teacher\Training\View as TrainingView;
use App\Models\Church;
use App\Models\Role;
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

    $teacher = User::factory()->create();
    $teacherRole = Role::query()->firstOrCreate(['name' => 'Teacher']);
    $teacher->roles()->syncWithoutDetaching([$teacherRole->id]);

    $training = Training::factory()->create([
        'church_id' => $hostChurch->id,
        'teacher_id' => $teacher->id,
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

    Livewire::actingAs($teacher)
        ->test(TrainingView::class, ['training' => $training])
        ->assertSeeInOrder([
            'Total de alunos:',
            '3',
            'Total de igrejas:',
            '2',
            'Total de igrejas novas:',
            '2',
            'Total de pastores:',
            '2',
            'Total de decisões:',
            '2',
        ])
        ->assertSeeInOrder([
            'Decisão',
            '2',
            'Sem decisão/ interessado',
            '1',
        ]);
});
