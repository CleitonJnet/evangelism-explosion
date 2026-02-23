<?php

use App\Livewire\Pages\App\Teacher\Training\Schedule;
use App\Models\Role;
use App\Models\Training;
use App\Models\TrainingScheduleItem;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Livewire;

function createTeacherForScheduleAttentionModal(): User
{
    $teacher = User::factory()->create();
    $teacherRole = Role::query()->firstOrCreate(['name' => 'Teacher']);
    $teacher->roles()->syncWithoutDetaching([$teacherRole->id]);

    return $teacher;
}

it('shows the schedule attention modal once for teacher without schedule adjustments', function (): void {
    $teacher = createTeacherForScheduleAttentionModal();
    $training = Training::factory()->create([
        'teacher_id' => $teacher->id,
        'schedule_attention_shown_at' => null,
        'schedule_adjusted_at' => null,
    ]);

    $this->actingAs($teacher)
        ->get(route('app.teacher.trainings.schedule', $training))
        ->assertOk()
        ->assertSee('Atenção aos horários das sessões')
        ->assertSee('Entendi e gerar programação padrão')
        ->assertDontSee('Entendi e vou ajustar');

    $training->refresh();

    expect($training->schedule_attention_shown_at)->not->toBeNull()
        ->and($training->schedule_adjusted_at)->toBeNull();

    $this->actingAs($teacher)
        ->get(route('app.teacher.trainings.schedule', $training))
        ->assertOk()
        ->assertDontSee('Atenção aos horários das sessões');
});

it('shows only the acknowledgment button when schedule already has sessions', function (): void {
    $teacher = createTeacherForScheduleAttentionModal();
    $training = Training::factory()->create([
        'teacher_id' => $teacher->id,
        'schedule_attention_shown_at' => null,
        'schedule_adjusted_at' => null,
    ]);

    $eventDate = $training->eventDates()->orderBy('date')->firstOrFail();
    $dateKey = Carbon::parse((string) $eventDate->date)->format('Y-m-d');

    TrainingScheduleItem::query()->create([
        'training_id' => $training->id,
        'section_id' => null,
        'date' => $dateKey,
        'starts_at' => Carbon::parse($dateKey.' '.$eventDate->start_time),
        'ends_at' => Carbon::parse($dateKey.' '.$eventDate->start_time)->addMinutes(30),
        'type' => 'SECTION',
        'title' => 'Sessão inicial',
        'position' => 1,
        'planned_duration_minutes' => 30,
        'suggested_duration_minutes' => 30,
        'min_duration_minutes' => 24,
        'origin' => 'AUTO',
        'status' => 'OK',
        'conflict_reason' => null,
        'meta' => null,
    ]);

    $this->actingAs($teacher)
        ->get(route('app.teacher.trainings.schedule', $training))
        ->assertOk()
        ->assertSee('Atenção aos horários das sessões')
        ->assertSee('Entendi e vou ajustar')
        ->assertDontSee('Entendi e gerar programação padrão');
});

it('generates default schedule when teacher confirms via attention modal action', function (): void {
    $teacher = createTeacherForScheduleAttentionModal();
    $training = Training::factory()->create([
        'teacher_id' => $teacher->id,
        'schedule_attention_shown_at' => null,
        'schedule_adjusted_at' => null,
    ]);

    expect($training->scheduleItems()->count())->toBe(0);

    Livewire::actingAs($teacher)
        ->test(Schedule::class, ['training' => $training])
        ->call('confirmScheduleAttentionAndGenerateDefault')
        ->assertHasNoErrors();

    expect($training->fresh()->scheduleItems()->count())->toBeGreaterThan(0);
});

it('does not show schedule attention modal after teacher adjusts any schedule item', function (): void {
    $teacher = createTeacherForScheduleAttentionModal();
    $training = Training::factory()->create([
        'teacher_id' => $teacher->id,
        'schedule_attention_shown_at' => null,
        'schedule_adjusted_at' => null,
    ]);

    $eventDate = $training->eventDates()->orderBy('date')->firstOrFail();
    $dateKey = Carbon::parse((string) $eventDate->date)->format('Y-m-d');

    $item = TrainingScheduleItem::query()->create([
        'training_id' => $training->id,
        'section_id' => null,
        'date' => $dateKey,
        'starts_at' => Carbon::parse($dateKey.' '.$eventDate->start_time),
        'ends_at' => Carbon::parse($dateKey.' '.$eventDate->start_time)->addMinutes(30),
        'type' => 'SECTION',
        'title' => 'Sessão inicial',
        'position' => 1,
        'planned_duration_minutes' => 30,
        'suggested_duration_minutes' => 30,
        'min_duration_minutes' => 24,
        'origin' => 'AUTO',
        'status' => 'OK',
        'conflict_reason' => null,
        'meta' => null,
    ]);

    Livewire::actingAs($teacher)
        ->test(Schedule::class, ['training' => $training])
        ->set("durationInputs.{$item->id}", 35)
        ->call('applyDuration', $item->id)
        ->assertHasNoErrors();

    $training->refresh();

    expect($training->schedule_adjusted_at)->not->toBeNull();

    $this->actingAs($teacher)
        ->get(route('app.teacher.trainings.schedule', $training))
        ->assertOk()
        ->assertDontSee('Atenção aos horários das sessões');
});
