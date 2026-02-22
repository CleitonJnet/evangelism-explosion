<?php

namespace App\Services\Schedule;

use App\Models\EventDate;
use App\Models\TrainingScheduleItem;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TrainingScheduleTimelineService
{
    private const SECTION_TOLERANCE_PERCENT = 20;

    private const MAX_SECTION_DURATION_MINUTES = 120;

    public function moveAfter(int $trainingId, int $itemId, string $dateKey, ?int $afterItemId): void
    {
        DB::transaction(function () use ($trainingId, $itemId, $dateKey, $afterItemId): void {
            $item = TrainingScheduleItem::query()
                ->where('training_id', $trainingId)
                ->findOrFail($itemId);

            $oldDate = $item->date?->format('Y-m-d');

            $targetIds = TrainingScheduleItem::query()
                ->where('training_id', $trainingId)
                ->where('date', $dateKey)
                ->orderBy('position')
                ->orderBy('id')
                ->pluck('id')
                ->reject(fn (int $id): bool => $id === $itemId)
                ->values();

            if ($afterItemId === null) {
                $insertIndex = 0;
            } else {
                $afterIndex = $targetIds->search($afterItemId);
                $insertIndex = $afterIndex === false ? $targetIds->count() : $afterIndex + 1;
            }

            $targetIds->splice($insertIndex, 0, [$itemId]);

            if ($item->date?->format('Y-m-d') !== $dateKey) {
                $item->date = $dateKey;
                $item->save();
            }

            $this->syncPositions($trainingId, $dateKey, $targetIds->all());

            if ($oldDate && $oldDate !== $dateKey) {
                $oldIds = TrainingScheduleItem::query()
                    ->where('training_id', $trainingId)
                    ->where('date', $oldDate)
                    ->orderBy('position')
                    ->orderBy('id')
                    ->pluck('id')
                    ->all();

                $this->syncPositions($trainingId, $oldDate, $oldIds);
            }

            $this->rebalanceDayToEventWindow($trainingId, $dateKey);

            if ($oldDate && $oldDate !== $dateKey) {
                $this->rebalanceDayToEventWindow($trainingId, $oldDate);
            }
        });
    }

    public function reflowDay(int $trainingId, string $dateKey): void
    {
        DB::transaction(function () use ($trainingId, $dateKey): void {
            $eventDate = EventDate::query()
                ->where('training_id', $trainingId)
                ->where('date', $dateKey)
                ->first();

            if (! $eventDate?->start_time) {
                return;
            }

            $items = TrainingScheduleItem::query()
                ->where('training_id', $trainingId)
                ->whereDate('date', $dateKey)
                ->orderBy('position')
                ->orderBy('id')
                ->get();

            if ($items->isEmpty()) {
                return;
            }

            $cursor = Carbon::parse($dateKey.' '.$eventDate->start_time);

            foreach ($items as $item) {
                $durationMinutes = (int) $item->planned_duration_minutes;
                $startsAt = $cursor->copy();
                $endsAt = $startsAt->copy()->addMinutes($durationMinutes);

                $item->starts_at = $startsAt;
                $item->ends_at = $endsAt;
                $item->date = $dateKey;
                $item->save();

                $cursor = $endsAt->copy();
            }
        });
    }

    public function rebalanceDayToEventWindow(int $trainingId, string $dateKey): void
    {
        DB::transaction(function () use ($trainingId, $dateKey): void {
            $eventDate = EventDate::query()
                ->where('training_id', $trainingId)
                ->where('date', $dateKey)
                ->first();

            if (! $eventDate?->start_time || ! $eventDate?->end_time) {
                return;
            }

            $items = TrainingScheduleItem::query()
                ->where('training_id', $trainingId)
                ->whereDate('date', $dateKey)
                ->orderBy('position')
                ->orderBy('id')
                ->get();

            if ($items->isEmpty()) {
                return;
            }

            $dayStart = Carbon::parse($dateKey.' '.$eventDate->start_time);
            $dayEnd = Carbon::parse($dateKey.' '.$eventDate->end_time);
            $targetMinutes = max(0, $dayStart->diffInMinutes($dayEnd, false));

            $durationsById = [];
            $fixedMinutes = 0;
            $adjustableIds = [];
            $mins = [];
            $maxs = [];

            foreach ($items as $item) {
                $duration = max(0, (int) $item->planned_duration_minutes);
                $durationsById[$item->id] = $duration;

                if ($this->isAdjustableSection($item)) {
                    $adjustableIds[] = $item->id;
                    $suggested = max(1, (int) ($item->suggested_duration_minutes ?? $duration ?: 1));
                    [$minBound, $maxBound] = $this->resolveSectionBounds(
                        $suggested,
                        $item->min_duration_minutes,
                    );
                    $mins[$item->id] = $minBound;
                    $maxs[$item->id] = $maxBound;

                    continue;
                }

                $fixedMinutes += $duration;
            }

            if ($adjustableIds !== []) {
                $availableForSections = max(0, $targetMinutes - $fixedMinutes);
                $scaled = $this->scaleDurationsBounded($adjustableIds, $durationsById, $mins, $maxs, $availableForSections);

                foreach ($scaled as $itemId => $duration) {
                    $durationsById[$itemId] = $duration;
                }
            }

            $currentTotal = array_sum($durationsById);
            $difference = $targetMinutes - $currentTotal;

            if ($difference !== 0) {
                $difference = $this->adjustBreakDurations($items, $durationsById, $difference);
            }

            if ($difference > 0) {
                $lastItem = $items->last();

                if ($lastItem) {
                    $durationsById[$lastItem->id] = ($durationsById[$lastItem->id] ?? 0) + $difference;
                }
            }

            if ($difference < 0) {
                $this->reduceDurationsFromTail($items, $durationsById, abs($difference));
            }

            foreach ($items as $item) {
                $newDuration = max(0, (int) ($durationsById[$item->id] ?? $item->planned_duration_minutes));

                if ((int) $item->planned_duration_minutes !== $newDuration) {
                    $item->planned_duration_minutes = $newDuration;
                    $item->save();
                }
            }

            $this->reflowDay($trainingId, $dateKey);
        });
    }

    /**
     * @param  Collection<int, TrainingScheduleItem>  $items
     * @param  array<int, int>  $durationsById
     */
    private function adjustBreakDurations(Collection $items, array &$durationsById, int $difference): int
    {
        if ($difference > 0) {
            return $difference;
        }

        $excess = abs($difference);
        $breakItems = $items
            ->filter(fn (TrainingScheduleItem $item): bool => $item->type === 'BREAK')
            ->values();

        foreach ($breakItems as $breakItem) {
            if ($excess <= 0) {
                break;
            }

            $current = (int) ($durationsById[$breakItem->id] ?? 0);
            $min = 5;
            $reducible = max(0, $current - $min);

            if ($reducible <= 0) {
                continue;
            }

            $decrease = min($reducible, $excess);
            $durationsById[$breakItem->id] = $current - $decrease;
            $excess -= $decrease;
        }

        return -$excess;
    }

    /**
     * @param  Collection<int, TrainingScheduleItem>  $items
     * @param  array<int, int>  $durationsById
     */
    private function reduceDurationsFromTail(Collection $items, array &$durationsById, int $minutesToReduce): void
    {
        if ($minutesToReduce <= 0) {
            return;
        }

        $reversed = $items->reverse()->values();

        foreach ($reversed as $item) {
            if ($minutesToReduce <= 0) {
                break;
            }

            $current = (int) ($durationsById[$item->id] ?? 0);
            $minAllowed = 1;

            if ($this->isAdjustableSection($item)) {
                $suggested = max(1, (int) ($item->suggested_duration_minutes ?? $current ?: 1));
                [$minBound] = $this->resolveSectionBounds($suggested, $item->min_duration_minutes);
                $minAllowed = $minBound;
            } elseif ($item->type === 'BREAK') {
                $minAllowed = 5;
            }

            $reducible = max(0, $current - $minAllowed);

            if ($reducible <= 0) {
                continue;
            }

            $decrease = min($reducible, $minutesToReduce);
            $durationsById[$item->id] = $current - $decrease;
            $minutesToReduce -= $decrease;
        }
    }

    /**
     * @param  array<int, int>  $itemIds
     * @param  array<int, int>  $durations
     * @param  array<int, int>  $mins
     * @param  array<int, int>  $maxs
     * @return array<int, int>
     */
    private function scaleDurationsBounded(
        array $itemIds,
        array $durations,
        array $mins,
        array $maxs,
        int $available,
    ): array {
        $total = 0;

        foreach ($itemIds as $itemId) {
            $total += $durations[$itemId] ?? 0;
        }

        if ($total <= 0) {
            return [];
        }

        $scaled = [];
        $fractions = [];
        $sum = 0;

        foreach ($itemIds as $itemId) {
            $raw = (($durations[$itemId] ?? 0) / $total) * $available;
            $floor = (int) floor($raw);
            $min = (int) ($mins[$itemId] ?? 1);
            $max = (int) ($maxs[$itemId] ?? $available);
            $value = max($min, min($max, $floor));
            $scaled[$itemId] = $value;
            $sum += $value;
            $fractions[$itemId] = $raw - $floor;
        }

        if ($sum < $available) {
            $remainder = $available - $sum;
            arsort($fractions);

            while ($remainder > 0) {
                $progress = false;

                foreach (array_keys($fractions) as $itemId) {
                    if ($remainder <= 0) {
                        break;
                    }

                    $max = (int) ($maxs[$itemId] ?? $available);

                    if (($scaled[$itemId] ?? 0) >= $max) {
                        continue;
                    }

                    $scaled[$itemId]++;
                    $remainder--;
                    $progress = true;
                }

                if (! $progress) {
                    break;
                }
            }
        }

        if ($sum > $available) {
            $excess = $sum - $available;
            asort($fractions);

            while ($excess > 0) {
                $progress = false;

                foreach (array_keys($fractions) as $itemId) {
                    if ($excess <= 0) {
                        break;
                    }

                    $min = (int) ($mins[$itemId] ?? 1);

                    if (($scaled[$itemId] ?? 0) <= $min) {
                        continue;
                    }

                    $scaled[$itemId]--;
                    $excess--;
                    $progress = true;
                }

                if (! $progress) {
                    break;
                }
            }
        }

        return $scaled;
    }

    private function isAdjustableSection(TrainingScheduleItem $item): bool
    {
        if ($item->type !== 'SECTION' || ! $item->section_id) {
            return false;
        }

        $meta = is_array($item->meta) ? $item->meta : [];

        return ($meta['fixed_duration'] ?? false) !== true;
    }

    /**
     * @return array{0: int, 1: int}
     */
    private function resolveSectionBounds(int $suggested, ?int $storedMinDuration): array
    {
        $computedMin = max(1, (int) ceil($suggested * (1 - (self::SECTION_TOLERANCE_PERCENT / 100))));
        $computedMax = max(1, (int) floor($suggested * (1 + (self::SECTION_TOLERANCE_PERCENT / 100))));

        $min = max(1, (int) ($storedMinDuration ?? $computedMin));
        $min = min(self::MAX_SECTION_DURATION_MINUTES, $min);

        $max = min(self::MAX_SECTION_DURATION_MINUTES, $computedMax);

        if ($max < $min) {
            $max = $min;
        }

        return [$min, $max];
    }

    /**
     * @param  array<int, int>  $orderedIds
     */
    private function syncPositions(int $trainingId, string $dateKey, array $orderedIds): void
    {
        $position = 1;

        foreach ($orderedIds as $id) {
            TrainingScheduleItem::query()
                ->where('training_id', $trainingId)
                ->where('date', $dateKey)
                ->whereKey($id)
                ->update(['position' => $position]);

            $position++;
        }
    }
}
