<?php

use App\Models\Church;
use App\Models\Course;
use App\Models\EventDate;
use App\Models\Section;
use App\Models\Training;
use App\Models\TrainingScheduleItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows the public event schedule grouped by weekday and turn in time order', function () {
    $course = Course::factory()->create([
        'type' => 'Treinamento',
        'name' => 'Evangelismo Essencial',
    ]);
    $church = Church::factory()->create(['name' => 'Igreja Central']);
    $teacher = User::factory()->create(['name' => 'Professor Teste']);

    $training = Training::factory()->create([
        'course_id' => $course->id,
        'church_id' => $church->id,
        'teacher_id' => $teacher->id,
    ]);

    $training->eventDates()->delete();
    $training->scheduleItems()->delete();

    EventDate::query()->create([
        'training_id' => $training->id,
        'date' => '2026-03-07',
        'start_time' => '08:00:00',
        'end_time' => '20:00:00',
    ]);

    EventDate::query()->create([
        'training_id' => $training->id,
        'date' => '2026-03-08',
        'start_time' => '13:00:00',
        'end_time' => '17:00:00',
    ]);

    $section = Section::factory()->create([
        'course_id' => $course->id,
        'name' => 'Bloco Principal',
        'devotional' => 'Conectando-se as Pessoas',
    ]);

    TrainingScheduleItem::query()->create([
        'training_id' => $training->id,
        'section_id' => null,
        'date' => '2026-03-07',
        'starts_at' => Carbon::parse('2026-03-07 19:00:00'),
        'ends_at' => Carbon::parse('2026-03-07 20:00:00'),
        'type' => 'MEAL',
        'title' => 'Jantar',
        'position' => 2,
        'planned_duration_minutes' => 60,
        'suggested_duration_minutes' => 60,
        'min_duration_minutes' => 45,
        'origin' => 'AUTO',
        'status' => 'OK',
    ]);

    TrainingScheduleItem::query()->create([
        'training_id' => $training->id,
        'section_id' => $section->id,
        'date' => '2026-03-07',
        'starts_at' => Carbon::parse('2026-03-07 09:00:00'),
        'ends_at' => Carbon::parse('2026-03-07 10:30:00'),
        'type' => 'SECTION',
        'title' => 'Abertura e visao geral',
        'position' => 1,
        'planned_duration_minutes' => 90,
        'suggested_duration_minutes' => 90,
        'min_duration_minutes' => 60,
        'origin' => 'AUTO',
        'status' => 'OK',
    ]);

    TrainingScheduleItem::query()->create([
        'training_id' => $training->id,
        'section_id' => null,
        'date' => '2026-03-08',
        'starts_at' => Carbon::parse('2026-03-08 14:00:00'),
        'ends_at' => Carbon::parse('2026-03-08 15:00:00'),
        'type' => 'BREAK',
        'title' => 'Intervalo',
        'position' => 1,
        'planned_duration_minutes' => 60,
        'suggested_duration_minutes' => 60,
        'min_duration_minutes' => 45,
        'origin' => 'AUTO',
        'status' => 'OK',
    ]);

    $this->get(route('web.event.schedule', $training))
        ->assertSuccessful()
        ->assertSee('Programa')
        ->assertSee('Baixar PDF')
        ->assertSee('Devocional:')
        ->assertSee('Conectando-se as Pessoas')
        ->assertSee('1h 30m')
        ->assertSee('1h')
        ->assertSee('07/03')
        ->assertSee('08/03')
        ->assertSeeInOrder([
            '07/03',
            '09:00 - 10:30',
            '19:00 - 20:00',
            '08/03',
            '14:00 - 15:00',
        ], false);
});

it('shows an unpublished message when schedule items are empty', function () {
    $course = Course::factory()->create();
    $church = Church::factory()->create();
    $teacher = User::factory()->create();

    $training = Training::factory()->create([
        'course_id' => $course->id,
        'church_id' => $church->id,
        'teacher_id' => $teacher->id,
    ]);

    $training->scheduleItems()->delete();

    $this->get(route('web.event.schedule', $training))
        ->assertSuccessful()
        ->assertSee('ainda');
});

it('downloads the public event schedule pdf', function () {
    $course = Course::factory()->create();
    $church = Church::factory()->create();
    $teacher = User::factory()->create();

    $training = Training::factory()->create([
        'course_id' => $course->id,
        'church_id' => $church->id,
        'teacher_id' => $teacher->id,
    ]);

    $eventDate = EventDate::query()
        ->where('training_id', $training->id)
        ->first();

    TrainingScheduleItem::query()->create([
        'training_id' => $training->id,
        'section_id' => null,
        'date' => (string) $eventDate?->date,
        'starts_at' => Carbon::parse((string) $eventDate?->date.' 09:00:00'),
        'ends_at' => Carbon::parse((string) $eventDate?->date.' 10:00:00'),
        'type' => 'SECTION',
        'title' => 'Sessao de abertura',
        'position' => 1,
        'planned_duration_minutes' => 60,
        'suggested_duration_minutes' => 60,
        'min_duration_minutes' => 45,
        'origin' => 'AUTO',
        'status' => 'OK',
    ]);

    $this->get(route('web.event.schedule.pdf', $training))
        ->assertSuccessful()
        ->assertDownload('programacao-evento-'.$training->id.'.pdf');
});
