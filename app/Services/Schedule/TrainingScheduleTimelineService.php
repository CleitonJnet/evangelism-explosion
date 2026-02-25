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

    private const DURATION_STEP_MINUTES = 5;

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

    public function rebalanceDayFromItemToEventWindow(int $trainingId, string $dateKey, int $itemId): void
    {
        DB::transaction(function () use ($trainingId, $dateKey, $itemId): void {
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

            $anchorIndex = $items->search(fn (TrainingScheduleItem $item): bool => $item->id === $itemId);

            if ($anchorIndex === false) {
                $this->rebalanceDayToEventWindow($trainingId, $dateKey);

                return;
            }

            $dayStart = Carbon::parse($dateKey.' '.$eventDate->start_time);
            $dayEnd = Carbon::parse($dateKey.' '.$eventDate->end_time);
            $targetMinutes = max(0, $dayStart->diffInMinutes($dayEnd, false));

            $lockedItems = $items->slice(0, $anchorIndex);
            $lockedMinutes = $lockedItems->sum(fn (TrainingScheduleItem $item): int => max(0, (int) $item->planned_duration_minutes));
            $availableMinutes = $targetMinutes - $lockedMinutes;

            $adjustableWindowItems = $items->slice($anchorIndex)->values();
            $durationsById = [];

            foreach ($adjustableWindowItems as $windowItem) {
                $durationsById[$windowItem->id] = max(0, (int) $windowItem->planned_duration_minutes);
            }

            $currentWindowMinutes = array_sum($durationsById);
            $difference = $availableMinutes - $currentWindowMinutes;

            $followingItems = $adjustableWindowItems->slice(1)->values();

            if ($difference > 0) {
                $difference = $this->addMinutesToFollowingItems($followingItems, $durationsById, $difference);
            } elseif ($difference < 0) {
                $difference = $this->reduceMinutesFromFollowingItems($followingItems, $durationsById, abs($difference));
            }

            if ($difference > 0) {
                $this->ensureTailBreakForRemainingGap($trainingId, $dateKey, $adjustableWindowItems, $durationsById, $difference, $dayEnd);
            }

            foreach ($adjustableWindowItems as $windowItem) {
                $newDuration = max(0, (int) ($durationsById[$windowItem->id] ?? $windowItem->planned_duration_minutes));

                if ((int) $windowItem->planned_duration_minutes !== $newDuration) {
                    $windowItem->planned_duration_minutes = $newDuration;
                    $windowItem->save();
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

        $storedMin = max(0, (int) ($storedMinDuration ?? 0));
        $baseMin = max($computedMin, $storedMin);
        $baseMin = min(self::MAX_SECTION_DURATION_MINUTES, $baseMin);
        $min = max(self::DURATION_STEP_MINUTES, $this->roundDownToStep($baseMin));

        $baseMax = min(self::MAX_SECTION_DURATION_MINUTES, $computedMax);
        $max = max($min, $this->roundUpToStep($baseMax));

        if ($max < $min) {
            $max = $min;
        }

        return [$min, $max];
    }

    /**
     * @param  Collection<int, TrainingScheduleItem>  $followingItems
     * @param  array<int, int>  $durationsById
     */
    private function addMinutesToFollowingItems(Collection $followingItems, array &$durationsById, int $minutesToAdd): int
    {
        if ($minutesToAdd <= 0 || $followingItems->isEmpty()) {
            return $minutesToAdd;
        }

        foreach ($followingItems as $item) {
            if ($minutesToAdd <= 0) {
                break;
            }

            if (! $this->isAdjustableSection($item)) {
                continue;
            }

            $current = (int) ($durationsById[$item->id] ?? $item->planned_duration_minutes);
            $suggested = max(1, (int) ($item->suggested_duration_minutes ?? $current ?: 1));
            [, $maxBound] = $this->resolveSectionBounds($suggested, $item->min_duration_minutes);
            $increase = max(0, $maxBound - $current);

            if ($increase <= 0) {
                continue;
            }

            $delta = min($increase, $minutesToAdd);
            $durationsById[$item->id] = $current + $delta;
            $minutesToAdd -= $delta;
        }

        if ($minutesToAdd <= 0) {
            return 0;
        }

        foreach ($followingItems as $item) {
            if ($minutesToAdd <= 0) {
                break;
            }

            if ($item->type !== 'BREAK') {
                continue;
            }

            $current = (int) ($durationsById[$item->id] ?? $item->planned_duration_minutes);
            $durationsById[$item->id] = $current + $minutesToAdd;
            $minutesToAdd = 0;
        }

        return $minutesToAdd;
    }

    /**
     * @param  Collection<int, TrainingScheduleItem>  $followingItems
     * @param  array<int, int>  $durationsById
     */
    private function reduceMinutesFromFollowingItems(Collection $followingItems, array &$durationsById, int $minutesToReduce): int
    {
        if ($minutesToReduce <= 0 || $followingItems->isEmpty()) {
            return 0;
        }

        foreach ($followingItems->reverse()->values() as $item) {
            if ($minutesToReduce <= 0) {
                break;
            }

            $current = (int) ($durationsById[$item->id] ?? $item->planned_duration_minutes);

            if ($item->type === 'BREAK') {
                $minAllowed = self::DURATION_STEP_MINUTES;
            } elseif ($this->isAdjustableSection($item)) {
                $suggested = max(1, (int) ($item->suggested_duration_minutes ?? $current ?: 1));
                [$minAllowed] = $this->resolveSectionBounds($suggested, $item->min_duration_minutes);
            } else {
                continue;
            }

            $reducible = max(0, $current - $minAllowed);

            if ($reducible <= 0) {
                continue;
            }

            $delta = min($reducible, $minutesToReduce);
            $durationsById[$item->id] = $current - $delta;
            $minutesToReduce -= $delta;
        }

        return $minutesToReduce > 0 ? -$minutesToReduce : 0;
    }

    /**
     * @param  Collection<int, TrainingScheduleItem>  $windowItems
     * @param  array<int, int>  $durationsById
     */
    private function ensureTailBreakForRemainingGap(
        int $trainingId,
        string $dateKey,
        Collection $windowItems,
        array &$durationsById,
        int $remainingMinutes,
        Carbon $dayEnd,
    ): void {
        if ($remainingMinutes <= 0) {
            return;
        }

        $lastItem = $windowItems->last();

        if ($lastItem && $lastItem->type === 'BREAK') {
            $current = (int) ($durationsById[$lastItem->id] ?? $lastItem->planned_duration_minutes);
            $durationsById[$lastItem->id] = $current + $remainingMinutes;

            return;
        }

        $breakItem = TrainingScheduleItem::query()->create([
            'training_id' => $trainingId,
            'section_id' => null,
            'date' => $dateKey,
            'starts_at' => $dayEnd->copy(),
            'ends_at' => $dayEnd->copy()->addMinutes($remainingMinutes),
            'type' => 'BREAK',
            'title' => 'Intervalo',
            'position' => ((int) ($lastItem?->position ?? 0)) + 1,
            'planned_duration_minutes' => $remainingMinutes,
            'suggested_duration_minutes' => null,
            'min_duration_minutes' => null,
            'origin' => 'AUTO',
            'status' => 'OK',
            'conflict_reason' => null,
            'meta' => ['auto_reason' => 'window_fill'],
        ]);

        $windowItems->push($breakItem);
        $durationsById[$breakItem->id] = $remainingMinutes;
    }

    private function roundUpToStep(int $value): int
    {
        return (int) (ceil($value / self::DURATION_STEP_MINUTES) * self::DURATION_STEP_MINUTES);
    }

    private function roundDownToStep(int $value): int
    {
        return (int) (floor($value / self::DURATION_STEP_MINUTES) * self::DURATION_STEP_MINUTES);
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
