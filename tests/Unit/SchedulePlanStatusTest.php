<?php

use App\Livewire\Pages\App\Teacher\Training\Schedule;
use App\Models\TrainingScheduleItem;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Tests\TestCase;

uses(TestCase::class);

function callSchedulePlanStatus(Schedule $component, string $dateKey, ?string $endTime, Collection $items): string
{
    $method = new ReflectionMethod($component, 'resolvePlanStatus');
    $method->setAccessible(true);

    return $method->invoke($component, $dateKey, $endTime, $items);
}

/**
 * @return array{hours: int, minutes: int}
 */
function callSchedulePlanDiff(Schedule $component, ?string $endTime, Collection $items): array
{
    $method = new ReflectionMethod($component, 'resolvePlanDiff');
    $method->setAccessible(true);

    return $method->invoke($component, $endTime, $items);
}

it('returns ok when end time matches last item hour and minute', function () {
    $component = new Schedule;
    $items = new Collection([
        new TrainingScheduleItem(['ends_at' => Carbon::parse('2026-02-02 21:30:59')]),
    ]);

    $status = callSchedulePlanStatus($component, '2026-02-02', '21:30:00', $items);

    expect($status)->toBe(Schedule::PLAN_STATUS_OK);
});

it('returns under when last item ends before planned end time', function () {
    $component = new Schedule;
    $items = new Collection([
        new TrainingScheduleItem(['ends_at' => Carbon::parse('2026-02-02 21:15:00')]),
    ]);

    $status = callSchedulePlanStatus($component, '2026-02-02', '21:30:00', $items);

    expect($status)->toBe(Schedule::PLAN_STATUS_UNDER);
});

it('returns over when last item ends after planned end time', function () {
    $component = new Schedule;
    $items = new Collection([
        new TrainingScheduleItem(['ends_at' => Carbon::parse('2026-02-02 21:45:00')]),
    ]);

    $status = callSchedulePlanStatus($component, '2026-02-02', '21:30:00', $items);

    expect($status)->toBe(Schedule::PLAN_STATUS_OVER);
});

it('returns diff in hours and minutes for under/over', function () {
    $component = new Schedule;

    $underItems = new Collection([
        new TrainingScheduleItem(['ends_at' => Carbon::parse('2026-02-02 20:40:00')]),
    ]);
    $underDiff = callSchedulePlanDiff($component, '21:30:00', $underItems);

    expect($underDiff)->toBe(['hours' => 0, 'minutes' => 50]);

    $overItems = new Collection([
        new TrainingScheduleItem(['ends_at' => Carbon::parse('2026-02-02 22:10:00')]),
    ]);
    $overDiff = callSchedulePlanDiff($component, '21:30:00', $overItems);

    expect($overDiff)->toBe(['hours' => 0, 'minutes' => 40]);
});
