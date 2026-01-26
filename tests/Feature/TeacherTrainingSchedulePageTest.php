<?php

use App\Models\Role;
use App\Models\Training;
use App\Models\TrainingScheduleItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows the training schedule page for teachers', function () {
    $teacher = User::factory()->create();
    $role = Role::query()->create(['name' => 'Teacher']);
    $training = Training::factory()->create();
    $eventDate = $training->eventDates->first();

    TrainingScheduleItem::factory()->create([
        'training_id' => $training->id,
        'date' => $eventDate?->date,
        'starts_at' => Carbon::parse($eventDate?->date.' 09:00:00'),
        'ends_at' => Carbon::parse($eventDate?->date.' 10:00:00'),
    ]);

    $teacher->roles()->attach($role->id);

    $this->actingAs($teacher)
        ->get(route('app.teacher.training.schedule', $training))
        ->assertSuccessful()
        ->assertSee('Programação do treinamento')
        ->assertSee('Agenda do treinamento')
        ->assertSee('Arrastar para reordenar');
});
