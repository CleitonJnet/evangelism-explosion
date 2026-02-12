<?php

namespace App\Helpers;

use App\Models\EventDate;
use App\Models\TrainingScheduleItem;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class DayScheduleHelper
{
    public const STATUS_UNDER = 'under';

    public const STATUS_OVER = 'over';

    public const STATUS_OK = 'ok';

    /**
     * @param  Collection<int, TrainingScheduleItem>  $items
     */
    public static function planStatus(string|CarbonInterface $dateKey, ?string $endTime, Collection $items): string
    {
        if ($endTime === null) {
            return self::STATUS_UNDER;
        }

        $dateValue = is_string($dateKey) ? $dateKey : $dateKey->format('Y-m-d');
        $plannedEndMinutes = Carbon::parse($dateValue.' '.$endTime)->hour * 60
            + Carbon::parse($dateValue.' '.$endTime)->minute;

        $lastEndMinutes = $items
            ->filter(fn (TrainingScheduleItem $item) => $item->ends_at)
            ->max(fn (TrainingScheduleItem $item) => $item->ends_at->hour * 60 + $item->ends_at->minute);

        if (! $lastEndMinutes) {
            return self::STATUS_UNDER;
        }

        if ($lastEndMinutes < $plannedEndMinutes) {
            return self::STATUS_UNDER;
        }

        if ($lastEndMinutes > $plannedEndMinutes) {
            return self::STATUS_OVER;
        }

        return self::STATUS_OK;
    }

    /**
     * @param  Collection<int, TrainingScheduleItem>  $items
     */
    public static function hasDayMatch(string|CarbonInterface $dateKey, ?string $endTime, Collection $items): bool
    {
        return self::planStatus($dateKey, $endTime, $items) === self::STATUS_OK;
    }

    /**
     * @param  Collection<int, EventDate>  $eventDates
     * @param  Collection<int, TrainingScheduleItem>  $scheduleItems
     */
    public static function hasAllDaysMatch(Collection $eventDates, Collection $scheduleItems): bool
    {
        if ($eventDates->isEmpty()) {
            return false;
        }

        return $eventDates->every(function ($eventDate) use ($scheduleItems): bool {
            $dateValue = is_string($eventDate->date)
                ? $eventDate->date
                : Carbon::parse((string) $eventDate->date)->format('Y-m-d');

            $itemsForDay = $scheduleItems->filter(function (TrainingScheduleItem $item) use ($dateValue): bool {
                if ($item->date === null) {
                    return false;
                }

                return $item->date->format('Y-m-d') === $dateValue;
            });

            return self::hasDayMatch($dateValue, $eventDate->end_time, $itemsForDay);
        });
    }
}
