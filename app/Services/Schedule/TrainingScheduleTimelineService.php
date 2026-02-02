<?php

namespace App\Services\Schedule;

use App\Models\EventDate;
use App\Models\TrainingScheduleItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TrainingScheduleTimelineService
{
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

            $this->reflowDay($trainingId, $dateKey);

            if ($oldDate && $oldDate !== $dateKey) {
                $this->reflowDay($trainingId, $oldDate);
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

                $cursor = $endsAt->copy()->addSecond();
            }
        });
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
