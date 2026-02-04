<?php

namespace App\Helpers;

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
        $plannedEndTimestamp = Carbon::parse($dateValue.' '.$endTime)->getTimestamp();

        $lastEndTimestamp = $items
            ->filter(fn (TrainingScheduleItem $item) => $item->ends_at)
            ->max(fn (TrainingScheduleItem $item) => $item->ends_at->getTimestamp());

        if (! $lastEndTimestamp) {
            return self::STATUS_UNDER;
        }

        if ($lastEndTimestamp < $plannedEndTimestamp) {
            return self::STATUS_UNDER;
        }

        if ($lastEndTimestamp > $plannedEndTimestamp) {
            return self::STATUS_OVER;
        }

        return self::STATUS_OK;
    }
}
