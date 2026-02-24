<?php

use App\Models\Training;
use App\Models\User;
use App\TrainingStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('updates the training status from the event status volt button', function (): void {
    $teacher = User::factory()->create();
    $training = Training::factory()->create([
        'teacher_id' => $teacher->id,
        'status' => TrainingStatus::Planning,
    ]);

    Livewire::actingAs($teacher)
        ->test('pages.app.teacher.training.event-status-button', ['trainingId' => $training->id])
        ->assertSee(__('Planejamento'))
        ->call('updateStatus', TrainingStatus::Completed->value)
        ->assertSet('status', TrainingStatus::Completed->value)
        ->assertSee(__('Concluído'));

    expect($training->fresh()->status)->toBe(TrainingStatus::Completed);
});

it('forbids another teacher from interacting with the event status volt button', function (): void {
    $ownerTeacher = User::factory()->create();
    $otherTeacher = User::factory()->create();
    $training = Training::factory()->create([
        'teacher_id' => $ownerTeacher->id,
    ]);

    Livewire::actingAs($otherTeacher)
        ->test('pages.app.teacher.training.event-status-button', ['trainingId' => $training->id])
        ->assertForbidden();
});
