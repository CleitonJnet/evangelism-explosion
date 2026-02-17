<?php

use App\Livewire\SwiperWrapperEvents;
use App\Models\Course;
use App\Models\Ministry;
use App\Models\Training;
use App\TrainingStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('lists public events including extra courses ordered by date and time when ministry is not informed', function () {
    $ministryA = Ministry::query()->create([
        'initials' => 'MINA',
        'name' => 'Ministerio A',
    ]);
    $ministryB = Ministry::query()->create([
        'initials' => 'MINB',
        'name' => 'Ministerio B',
    ]);

    $courseAlpha = Course::factory()->create([
        'execution' => 0,
        'name' => 'Curso Alpha',
        'ministry_id' => $ministryA->id,
    ]);
    $courseExtra = Course::factory()->create([
        'execution' => 1,
        'name' => 'Curso Extra',
        'ministry_id' => $ministryA->id,
    ]);
    $courseGamma = Course::factory()->create([
        'execution' => 0,
        'name' => 'Curso Gamma',
        'ministry_id' => $ministryB->id,
    ]);
    $courseExcluded = Course::factory()->create([
        'execution' => 1,
        'name' => 'Curso Excluido',
        'ministry_id' => $ministryB->id,
    ]);

    expect($courseExtra->id)->toBe(2);

    $trainingAlpha = Training::factory()->create([
        'course_id' => $courseAlpha->id,
        'status' => TrainingStatus::Scheduled,
    ]);
    $trainingExtra = Training::factory()->create([
        'course_id' => $courseExtra->id,
        'status' => TrainingStatus::Scheduled,
    ]);
    $trainingGamma = Training::factory()->create([
        'course_id' => $courseGamma->id,
        'status' => TrainingStatus::Scheduled,
    ]);
    $trainingExcluded = Training::factory()->create([
        'course_id' => $courseExcluded->id,
        'status' => TrainingStatus::Scheduled,
    ]);

    $trainingAlpha->eventDates()->delete();
    $trainingExtra->eventDates()->delete();
    $trainingGamma->eventDates()->delete();
    $trainingExcluded->eventDates()->delete();

    $trainingAlpha->eventDates()->create([
        'date' => now()->addDays(5)->toDateString(),
        'start_time' => '10:00:00',
        'end_time' => '12:00:00',
    ]);
    $trainingExtra->eventDates()->create([
        'date' => now()->addDays(4)->toDateString(),
        'start_time' => '08:00:00',
        'end_time' => '10:00:00',
    ]);
    $trainingGamma->eventDates()->create([
        'date' => now()->addDays(3)->toDateString(),
        'start_time' => '09:00:00',
        'end_time' => '11:00:00',
    ]);
    $trainingExcluded->eventDates()->create([
        'date' => now()->addDays(2)->toDateString(),
        'start_time' => '07:00:00',
        'end_time' => '09:00:00',
    ]);

    $events = Livewire::test(SwiperWrapperEvents::class)->viewData('events');

    expect($events->pluck('id')->all())->toBe([
        $trainingGamma->id,
        $trainingExtra->id,
        $trainingAlpha->id,
    ]);
    expect($events->pluck('id')->all())->not->toContain($trainingExcluded->id);
});

it('orders by ministry when ministry filter is informed', function () {
    $ministryA = Ministry::query()->create([
        'initials' => 'MINA',
        'name' => 'Ministerio A',
    ]);
    $ministryB = Ministry::query()->create([
        'initials' => 'MINB',
        'name' => 'Ministerio B',
    ]);

    $courseFromMinistryA = Course::factory()->create([
        'execution' => 0,
        'name' => 'Curso A',
        'ministry_id' => $ministryA->id,
    ]);
    $courseFromMinistryB = Course::factory()->create([
        'execution' => 0,
        'name' => 'Curso B',
        'ministry_id' => $ministryB->id,
    ]);

    $trainingFromMinistryA = Training::factory()->create([
        'course_id' => $courseFromMinistryA->id,
        'status' => TrainingStatus::Scheduled,
    ]);
    $trainingFromMinistryB = Training::factory()->create([
        'course_id' => $courseFromMinistryB->id,
        'status' => TrainingStatus::Scheduled,
    ]);

    $trainingFromMinistryA->eventDates()->delete();
    $trainingFromMinistryB->eventDates()->delete();

    $trainingFromMinistryA->eventDates()->create([
        'date' => now()->addDays(8)->toDateString(),
        'start_time' => '10:00:00',
        'end_time' => '12:00:00',
    ]);
    $trainingFromMinistryB->eventDates()->create([
        'date' => now()->addDays(3)->toDateString(),
        'start_time' => '08:00:00',
        'end_time' => '10:00:00',
    ]);

    $events = Livewire::test(SwiperWrapperEvents::class, [
        'ministry' => [$ministryB->id, $ministryA->id],
    ])->viewData('events');

    expect($events->pluck('id')->all())->toBe([
        $trainingFromMinistryA->id,
        $trainingFromMinistryB->id,
    ]);
});

it('does not use course name as tie breaker on home events carousel ordering', function () {
    $ministry = Ministry::query()->create([
        'initials' => 'MIN',
        'name' => 'Ministerio A',
    ]);

    $courseZulu = Course::factory()->create([
        'execution' => 0,
        'name' => 'Curso Zulu',
        'ministry_id' => $ministry->id,
    ]);
    $courseAlpha = Course::factory()->create([
        'execution' => 0,
        'name' => 'Curso Alpha',
        'ministry_id' => $ministry->id,
    ]);

    $trainingFirst = Training::factory()->create([
        'course_id' => $courseZulu->id,
        'status' => TrainingStatus::Scheduled,
    ]);
    $trainingSecond = Training::factory()->create([
        'course_id' => $courseAlpha->id,
        'status' => TrainingStatus::Scheduled,
    ]);

    $trainingFirst->eventDates()->delete();
    $trainingSecond->eventDates()->delete();

    $date = now()->addDays(10)->toDateString();

    $trainingFirst->eventDates()->create([
        'date' => $date,
        'start_time' => '09:00:00',
        'end_time' => '11:00:00',
    ]);
    $trainingSecond->eventDates()->create([
        'date' => $date,
        'start_time' => '09:00:00',
        'end_time' => '11:00:00',
    ]);

    $events = Livewire::test(SwiperWrapperEvents::class)->viewData('events');

    expect($events->pluck('id')->all())->toBe([
        $trainingFirst->id,
        $trainingSecond->id,
    ]);
});

it('filters events by audience type', function () {
    $ministry = Ministry::query()->create([
        'initials' => 'MNA',
        'name' => 'Ministerio A',
    ]);

    $courseLeader = Course::factory()->create([
        'execution' => 0,
        'name' => 'Curso Lideres',
        'ministry_id' => $ministry->id,
    ]);
    $courseMembers = Course::factory()->create([
        'execution' => 1,
        'name' => 'Curso Membros',
        'ministry_id' => $ministry->id,
    ]);

    expect($courseMembers->id)->toBe(2);

    $trainingLeader = Training::factory()->create([
        'course_id' => $courseLeader->id,
        'status' => TrainingStatus::Scheduled,
    ]);
    $trainingMembers = Training::factory()->create([
        'course_id' => $courseMembers->id,
        'status' => TrainingStatus::Scheduled,
    ]);

    $trainingLeader->eventDates()->delete();
    $trainingMembers->eventDates()->delete();

    $trainingLeader->eventDates()->create([
        'date' => now()->addDays(6)->toDateString(),
        'start_time' => '10:00:00',
        'end_time' => '12:00:00',
    ]);
    $trainingMembers->eventDates()->create([
        'date' => now()->addDays(7)->toDateString(),
        'start_time' => '10:00:00',
        'end_time' => '12:00:00',
    ]);

    $leadersEvents = Livewire::test(SwiperWrapperEvents::class, [
        'ministry' => $ministry->id,
        'audience' => 'leaders',
    ])->viewData('events');

    $membersEvents = Livewire::test(SwiperWrapperEvents::class, [
        'ministry' => $ministry->id,
        'audience' => 'members',
    ])->viewData('events');

    expect($leadersEvents->pluck('id')->all())->toBe([$trainingLeader->id]);
    expect($membersEvents->pluck('id')->all())->toBe([$trainingMembers->id]);
});

it('does not render schedule request card for members audience', function () {
    $ministry = Ministry::query()->create([
        'initials' => 'MNA',
        'name' => 'Ministerio A',
    ]);

    Course::factory()->create([
        'execution' => 0,
        'name' => 'Curso Lideres',
        'ministry_id' => $ministry->id,
    ]);
    $courseMembers = Course::factory()->create([
        'execution' => 1,
        'name' => 'Curso Membros',
        'ministry_id' => $ministry->id,
    ]);

    $trainingMembers = Training::factory()->create([
        'course_id' => $courseMembers->id,
        'status' => TrainingStatus::Scheduled,
    ]);

    $trainingMembers->eventDates()->delete();
    $trainingMembers->eventDates()->create([
        'date' => now()->addDays(7)->toDateString(),
        'start_time' => '10:00:00',
        'end_time' => '12:00:00',
    ]);

    Livewire::test(SwiperWrapperEvents::class, [
        'ministry' => $ministry->id,
        'audience' => 'members',
    ])->assertDontSee('Agende um Treinamento Local');
});

it('renders schedule request card for leaders only when there are no execution zero events', function () {
    $ministry = Ministry::query()->create([
        'initials' => 'MNA',
        'name' => 'Ministerio A',
    ]);

    Course::factory()->create([
        'execution' => 0,
        'name' => 'Curso Lideres',
        'ministry_id' => $ministry->id,
    ]);
    $courseMembers = Course::factory()->create([
        'execution' => 1,
        'name' => 'Curso Membros',
        'ministry_id' => $ministry->id,
    ]);

    $trainingMembers = Training::factory()->create([
        'course_id' => $courseMembers->id,
        'status' => TrainingStatus::Scheduled,
    ]);

    $trainingMembers->eventDates()->delete();
    $trainingMembers->eventDates()->create([
        'date' => now()->addDays(7)->toDateString(),
        'start_time' => '10:00:00',
        'end_time' => '12:00:00',
    ]);

    Livewire::test(SwiperWrapperEvents::class, [
        'ministry' => $ministry->id,
        'audience' => 'leaders',
    ])->assertSee('Agende um Treinamento Local');

    $courseLeaders = Course::query()->where('execution', 0)->firstOrFail();
    $trainingLeaders = Training::factory()->create([
        'course_id' => $courseLeaders->id,
        'status' => TrainingStatus::Scheduled,
    ]);

    $trainingLeaders->eventDates()->delete();
    $trainingLeaders->eventDates()->create([
        'date' => now()->addDays(9)->toDateString(),
        'start_time' => '10:00:00',
        'end_time' => '12:00:00',
    ]);

    Livewire::test(SwiperWrapperEvents::class, [
        'ministry' => $ministry->id,
        'audience' => 'leaders',
    ])->assertDontSee('Agende um Treinamento Local');
});
