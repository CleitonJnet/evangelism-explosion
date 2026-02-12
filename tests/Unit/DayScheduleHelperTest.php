<?php

use App\Helpers\DayScheduleHelper;
use App\Models\EventDate;
use App\Models\TrainingScheduleItem;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Tests\TestCase;

uses(TestCase::class);

it('returns under when the day ends before the last item', function () {
    $items = new Collection([
        new TrainingScheduleItem(['ends_at' => Carbon::parse('2026-02-02 16:00:00')]),
    ]);

    $status = DayScheduleHelper::planStatus('2026-02-02', '17:00:00', $items);

    expect($status)->toBe(DayScheduleHelper::STATUS_UNDER);
});

it('returns over when the last item exceeds the day end', function () {
    $items = new Collection([
        new TrainingScheduleItem(['ends_at' => Carbon::parse('2026-02-02 18:30:00')]),
    ]);

    $status = DayScheduleHelper::planStatus('2026-02-02', '18:00:00', $items);

    expect($status)->toBe(DayScheduleHelper::STATUS_OVER);
});

it('returns ok when the last item matches the day end', function () {
    $items = new Collection([
        new TrainingScheduleItem(['ends_at' => Carbon::parse('2026-02-02 18:00:00')]),
    ]);

    $status = DayScheduleHelper::planStatus('2026-02-02', '18:00:00', $items);

    expect($status)->toBe(DayScheduleHelper::STATUS_OK);
});

it('returns ok when the last item differs only by seconds', function () {
    $items = new Collection([
        new TrainingScheduleItem(['ends_at' => Carbon::parse('2026-02-02 18:00:25')]),
    ]);

    $status = DayScheduleHelper::planStatus('2026-02-02', '18:00:00', $items);

    expect($status)->toBe(DayScheduleHelper::STATUS_OK);
});

it('returns false when the day end time is missing', function () {
    $items = new Collection([
        new TrainingScheduleItem(['ends_at' => Carbon::parse('2026-02-02 18:00:00')]),
    ]);

    $matched = DayScheduleHelper::hasDayMatch('2026-02-02', null, $items);

    expect($matched)->toBeFalse();
});

it('returns true when all days match the schedule end', function () {
    $eventDates = new Collection([
        new EventDate(['date' => '2026-02-02', 'end_time' => '18:00:00']),
        new EventDate(['date' => '2026-02-03', 'end_time' => '12:00:00']),
    ]);

    $scheduleItems = new Collection([
        new TrainingScheduleItem([
            'date' => '2026-02-02',
            'ends_at' => Carbon::parse('2026-02-02 18:00:00'),
        ]),
        new TrainingScheduleItem([
            'date' => '2026-02-03',
            'ends_at' => Carbon::parse('2026-02-03 12:00:00'),
        ]),
    ]);

    $matched = DayScheduleHelper::hasAllDaysMatch($eventDates, $scheduleItems);

    expect($matched)->toBeTrue();
});
