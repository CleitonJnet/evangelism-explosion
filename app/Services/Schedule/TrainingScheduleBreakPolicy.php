<?php

namespace App\Services\Schedule;

use App\Models\Training;
use App\Models\TrainingScheduleItem;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TrainingScheduleBreakPolicy
{
    private const MIN_WINDOW_MINUTES = 120;

    private const MAX_WINDOW_MINUTES = 140;

    private const DEFAULT_BREAK_MINUTES = 10;

    private const MIN_BREAK_DISTANCE_MINUTES = 60;

    public function __construct(
        public TrainingDayBlocksService $dayBlocksService,
        public TrainingScheduleTimelineService $timelineService,
    ) {}

    public function suggestBreakIfLongRun(
        int $trainingId,
        string $dateKey,
        ?string $aroundTime,
        string $reasonKey,
    ): void {
        $training = Training::query()->findOrFail($trainingId);

        if ($this->isSuppressed($training, $dateKey, $reasonKey)) {
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

        if ($this->hasExistingAutoBreak($items, $reasonKey)) {
            return;
        }

        $runs = $this->buildSectionRuns($items);

        if ($runs->isEmpty()) {
            return;
        }

        $targetTime = $aroundTime ? Carbon::parse($dateKey.' '.$aroundTime) : null;
        $run = $this->selectRun($runs, $targetTime);

        if (! $run || $run['duration_minutes'] < self::MIN_WINDOW_MINUTES) {
            return;
        }

        $insertTime = $targetTime && $targetTime->between($run['starts_at'], $run['ends_at'])
            ? $targetTime
            : $run['starts_at']->copy()->addSeconds(
                (int) floor($run['starts_at']->diffInSeconds($run['ends_at']) / 2)
            );

        if ($this->hasNearbyPause($items, $insertTime)) {
            return;
        }

        DB::transaction(function () use ($trainingId, $dateKey, $items, $insertTime, $reasonKey): void {
            $insertIndex = $this->resolveInsertIndex($items, $insertTime);

            $breakItem = TrainingScheduleItem::query()->create([
                'training_id' => $trainingId,
                'section_id' => null,
                'date' => $dateKey,
                'starts_at' => $insertTime->copy(),
                'ends_at' => $insertTime->copy()->addMinutes(self::DEFAULT_BREAK_MINUTES),
                'type' => 'BREAK',
                'title' => 'Intervalo',
                'planned_duration_minutes' => self::DEFAULT_BREAK_MINUTES,
                'suggested_duration_minutes' => null,
                'min_duration_minutes' => null,
                'origin' => 'AUTO',
                'status' => 'OK',
                'conflict_reason' => null,
                'meta' => [
                    'auto_reason' => $reasonKey,
                ],
            ]);

            $items->splice($insertIndex, 0, [$breakItem]);
            $this->syncPositions($items);

            $this->timelineService->rebalanceDayToEventWindow($trainingId, $dateKey);
        });
    }

    public function suppressSnackBreak(int $trainingId, string $dateKey): void
    {
        $training = Training::query()->with('eventDates')->findOrFail($trainingId);
        $overrides = $this->resolveOverrides($training);
        $overrides[$dateKey]['snack_break_suppressed'] = true;

        $dayBlocks = $this->dayBlocksService->get($trainingId);

        $training->schedule_settings = [
            'day_blocks' => $dayBlocks,
            'overrides' => $overrides,
        ];

        $training->save();
    }

    /**
     * @return Collection<int, array{
     *     starts_at: Carbon,
     *     ends_at: Carbon,
     *     duration_minutes: int
     * }>
     */
    private function buildSectionRuns(Collection $items): Collection
    {
        $runs = [];
        $current = null;

        foreach ($items as $item) {
            if ($item->type === 'SECTION') {
                if ($current === null) {
                    $current = [
                        'starts_at' => $item->starts_at?->copy() ?? Carbon::parse($item->date?->format('Y-m-d').' 00:00:00'),
                        'ends_at' => $item->ends_at?->copy() ?? Carbon::parse($item->date?->format('Y-m-d').' 00:00:00'),
                        'duration_minutes' => (int) $item->planned_duration_minutes,
                    ];
                } else {
                    $current['ends_at'] = $item->ends_at?->copy() ?? $current['ends_at'];
                    $current['duration_minutes'] += (int) $item->planned_duration_minutes;
                }

                continue;
            }

            if ($current !== null) {
                $runs[] = $current;
                $current = null;
            }
        }

        if ($current !== null) {
            $runs[] = $current;
        }

        return collect($runs);
    }

    /**
     * @param  Collection<int, array{starts_at: Carbon, ends_at: Carbon, duration_minutes: int}>  $runs
     * @return array{starts_at: Carbon, ends_at: Carbon, duration_minutes: int}|null
     */
    private function selectRun(Collection $runs, ?Carbon $targetTime): ?array
    {
        if (! $targetTime) {
            return $runs->sortByDesc('duration_minutes')->first();
        }

        $matching = $runs->first(fn (array $run): bool => $targetTime->between($run['starts_at'], $run['ends_at']));

        if ($matching) {
            return $matching;
        }

        return $runs
            ->map(function (array $run) use ($targetTime): array {
                $midpoint = $run['starts_at']->copy()->addSeconds(
                    (int) floor($run['starts_at']->diffInSeconds($run['ends_at']) / 2)
                );

                return [
                    'run' => $run,
                    'distance' => abs($midpoint->diffInSeconds($targetTime, false)),
                ];
            })
            ->sortBy('distance')
            ->first()['run'] ?? null;
    }

    private function resolveInsertIndex(Collection $items, CarbonInterface $targetTime): int
    {
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

    private function hasExistingAutoBreak(Collection $items, string $reasonKey): bool
    {
        return $items->contains(function (TrainingScheduleItem $item) use ($reasonKey): bool {
            if ($item->type !== 'BREAK') {
                return false;
            }

            $meta = is_array($item->meta) ? $item->meta : [];

            return ($meta['auto_reason'] ?? null) === $reasonKey;
        });
    }

    private function hasNearbyPause(Collection $items, Carbon $insertTime): bool
    {
        return $items->contains(function (TrainingScheduleItem $item) use ($insertTime): bool {
            if (! in_array($item->type, ['BREAK', 'MEAL'], true)) {
                return false;
            }

            if (! $item->starts_at) {
                return false;
            }

            return abs($item->starts_at->diffInMinutes($insertTime, false)) < self::MIN_BREAK_DISTANCE_MINUTES;
        });
    }

    private function isSuppressed(Training $training, string $dateKey, string $reasonKey): bool
    {
        if ($reasonKey !== 'snack_off') {
            return false;
        }

        $overrides = $this->resolveOverrides($training);

        return (bool) ($overrides[$dateKey]['snack_break_suppressed'] ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    private function resolveOverrides(Training $training): array
    {
        $settings = is_array($training->schedule_settings) ? $training->schedule_settings : [];
        $overrides = $settings['overrides'] ?? [];

        return is_array($overrides) ? $overrides : [];
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
}
