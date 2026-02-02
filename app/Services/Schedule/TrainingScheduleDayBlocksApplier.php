<?php

namespace App\Services\Schedule;

use App\Models\EventDate;
use App\Models\TrainingScheduleItem;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TrainingScheduleDayBlocksApplier
{
    public function __construct(
        public TrainingScheduleTimelineService $timelineService,
    ) {}

    public function ensureBlockItem(int $trainingId, string $dateKey, string $blockKey): void
    {
        DB::transaction(function () use ($trainingId, $dateKey, $blockKey): void {
            $items = $this->itemsForDay($trainingId, $dateKey);

            if ($items->first(fn (TrainingScheduleItem $item): bool => $this->matchesBlockItem($item, $blockKey))) {
                return;
            }

            $spec = $this->resolveBlockSpec($blockKey, $dateKey, $trainingId);
            $insertIndex = $this->resolveInsertIndex($items, $blockKey, $spec['target_time']);

            $newItem = TrainingScheduleItem::query()->create([
                'training_id' => $trainingId,
                'section_id' => null,
                'date' => $dateKey,
                'starts_at' => $spec['starts_at'],
                'ends_at' => $spec['ends_at'],
                'type' => $spec['type'],
                'title' => $spec['title'],
                'planned_duration_minutes' => $spec['duration'],
                'suggested_duration_minutes' => null,
                'min_duration_minutes' => null,
                'origin' => 'TEACHER',
                'status' => 'OK',
                'conflict_reason' => null,
                'meta' => $spec['meta'],
            ]);

            $items->splice($insertIndex, 0, [$newItem]);
            $this->syncPositions($items);

            $this->timelineService->reflowDay($trainingId, $dateKey);
        });
    }

    public function removeBlockItem(int $trainingId, string $dateKey, string $blockKey): void
    {
        DB::transaction(function () use ($trainingId, $dateKey, $blockKey): void {
            $items = $this->itemsForDay($trainingId, $dateKey);

            $idsToDelete = $items
                ->filter(fn (TrainingScheduleItem $item): bool => $this->matchesBlockItem($item, $blockKey))
                ->pluck('id')
                ->values()
                ->all();

            if ($idsToDelete === []) {
                return;
            }

            TrainingScheduleItem::query()
                ->whereIn('id', $idsToDelete)
                ->delete();

            $remaining = $items->reject(fn (TrainingScheduleItem $item): bool => in_array($item->id, $idsToDelete, true))->values();
            $this->syncPositions($remaining);

            $this->timelineService->reflowDay($trainingId, $dateKey);
        });
    }

    public function apply(int $trainingId, string $dateKey, string $blockKey, bool $enabled): void
    {
        if ($enabled) {
            $this->ensureBlockItem($trainingId, $dateKey, $blockKey);

            return;
        }

        $this->removeBlockItem($trainingId, $dateKey, $blockKey);
    }

    /**
     * @return array{
     *     type: string,
     *     title: string,
     *     duration: int,
     *     meta: array<string, mixed>|null,
     *     starts_at: Carbon,
     *     ends_at: Carbon,
     *     target_time: Carbon|null
     * }
     */
    private function resolveBlockSpec(string $blockKey, string $dateKey, int $trainingId): array
    {
        $dayStart = $this->resolveDayStart($trainingId, $dateKey);

        return match ($blockKey) {
            'welcome' => [
                'type' => 'WELCOME',
                'title' => 'Boas-vindas',
                'duration' => 20,
                'meta' => ['block' => 'welcome', 'anchor' => 'welcome'],
                'starts_at' => $dayStart->copy(),
                'ends_at' => $dayStart->copy()->addMinutes(20),
                'target_time' => null,
            ],
            'devotional' => [
                'type' => 'DEVOTIONAL',
                'title' => 'Devocional',
                'duration' => 15,
                'meta' => ['block' => 'devotional', 'anchor' => 'devotional'],
                'starts_at' => $dayStart->copy(),
                'ends_at' => $dayStart->copy()->addMinutes(15),
                'target_time' => null,
            ],
            'breakfast' => $this->mealSpec('Café', $dateKey, 'breakfast', '07:50:00', 40, $dayStart),
            'lunch' => $this->mealSpec('Almoço', $dateKey, 'lunch', '12:00:00', 90, $dayStart),
            'snack' => $this->mealSpec('Lanche', $dateKey, 'snack', '15:30:00', 30, $dayStart, 'afternoon_snack'),
            'dinner' => $this->mealSpec('Jantar', $dateKey, 'dinner', '18:00:00', 60, $dayStart),
            default => [
                'type' => 'SECTION',
                'title' => 'Sessão',
                'duration' => 0,
                'meta' => null,
                'starts_at' => $dayStart->copy(),
                'ends_at' => $dayStart->copy(),
                'target_time' => null,
            ],
        };
    }

    /**
     * @return array{
     *     type: string,
     *     title: string,
     *     duration: int,
     *     meta: array<string, mixed>,
     *     starts_at: Carbon,
     *     ends_at: Carbon,
     *     target_time: Carbon
     * }
     */
    private function mealSpec(
        string $title,
        string $dateKey,
        string $subkind,
        string $targetTime,
        int $duration,
        Carbon $dayStart,
        ?string $anchor = null,
    ): array {
        $anchorKey = $anchor ?? $subkind;
        $target = Carbon::parse($dateKey.' '.$targetTime);
        $start = $target->copy();

        if ($start->lt($dayStart)) {
            $start = $dayStart->copy();
        }

        return [
            'type' => 'MEAL',
            'title' => $title,
            'duration' => $duration,
            'meta' => [
                'block' => $subkind,
                'subkind' => $subkind,
                'anchor' => $anchorKey,
            ],
            'starts_at' => $start->copy(),
            'ends_at' => $start->copy()->addMinutes($duration),
            'target_time' => $target,
        ];
    }

    /**
     * @param  Collection<int, TrainingScheduleItem>  $items
     */
    private function resolveInsertIndex(Collection $items, string $blockKey, ?Carbon $targetTime): int
    {
        if ($items->isEmpty()) {
            return 0;
        }

        if ($blockKey === 'welcome') {
            return 0;
        }

        if ($blockKey === 'devotional') {
            $welcomeIndex = $items->search(fn (TrainingScheduleItem $item): bool => $item->type === 'WELCOME');

            if ($welcomeIndex !== false) {
                return $welcomeIndex + 1;
            }

            return 0;
        }

        if (! $targetTime) {
            return $items->count();
        }

        $targetIndex = $items->search(function (TrainingScheduleItem $item) use ($targetTime): bool {
            if (! $item->starts_at) {
                return false;
            }

            return $item->starts_at->gte($targetTime);
        });

        if ($targetIndex === false) {
            return $items->count();
        }

        return (int) $targetIndex;
    }

    private function matchesBlockItem(TrainingScheduleItem $item, string $blockKey): bool
    {
        if ($blockKey === 'welcome') {
            return $item->type === 'WELCOME';
        }

        if ($blockKey === 'devotional') {
            return $item->type === 'DEVOTIONAL';
        }

        if ($item->type !== 'MEAL') {
            return false;
        }

        $meta = is_array($item->meta) ? $item->meta : [];
        $anchor = $meta['anchor'] ?? null;
        $subkind = $meta['subkind'] ?? null;

        return match ($blockKey) {
            'breakfast' => $anchor === 'breakfast' || $subkind === 'breakfast',
            'lunch' => $anchor === 'lunch' || $subkind === 'lunch',
            'snack' => $anchor === 'afternoon_snack' || $subkind === 'snack',
            'dinner' => in_array($anchor, ['dinner', 'night_snack'], true) || $subkind === 'dinner',
            default => false,
        };
    }

    /**
     * @return Collection<int, TrainingScheduleItem>
     */
    private function itemsForDay(int $trainingId, string $dateKey): Collection
    {
        return TrainingScheduleItem::query()
            ->where('training_id', $trainingId)
            ->whereDate('date', $dateKey)
            ->orderBy('position')
            ->orderBy('id')
            ->get();
    }

    /**
     * @param  Collection<int, TrainingScheduleItem>  $items
     */
    private function syncPositions(Collection $items): void
    {
        $position = 1;

        foreach ($items as $item) {
            if ($item->position === $position) {
                $position++;

                continue;
            }

            $item->position = $position;
            $item->save();
            $position++;
        }
    }

    private function resolveDayStart(int $trainingId, string $dateKey): Carbon
    {
        $startTime = EventDate::query()
            ->where('training_id', $trainingId)
            ->where('date', $dateKey)
            ->value('start_time');

        $startTime = is_string($startTime) && $startTime !== '' ? $startTime : '00:00:00';

        return Carbon::parse($dateKey.' '.$startTime);
    }
}
