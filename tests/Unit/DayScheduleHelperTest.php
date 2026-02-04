<?php

use App\Helpers\DayScheduleHelper;
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
