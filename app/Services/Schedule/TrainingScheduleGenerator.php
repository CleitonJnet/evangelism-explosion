<?php

namespace App\Services\Schedule;

use App\Models\Training;
use App\Models\TrainingScheduleItem;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TrainingScheduleGenerator
{
    private int $minBreakAt = 90;

    private int $maxBreakAt = 150;

    private int $breakMinutes = 15;

    public function generate(Training $training, string $mode = 'AUTO_ONLY'): GenerationResult
    {
        return DB::transaction(function () use ($training, $mode): GenerationResult {
            $training->load([
                'eventDates' => fn ($query) => $query->orderBy('date')->orderBy('start_time'),
                'course.sections' => fn ($query) => $query->orderBy('order'),
            ]);

            $this->deletePreviousItems($training, $mode);

            $sections = $training->course?->sections ?? collect();
            $queue = $this->buildQueue($sections);

            if ($queue->isEmpty() || $training->eventDates->isEmpty()) {
                return new GenerationResult(collect(), $queue->pluck('section'));
            }

            $preservedByDate = $training->scheduleItems()
                ->where(function ($query): void {
                    $query->where('origin', 'TEACHER')
                        ->orWhere('is_locked', true);
                })
                ->get()
                ->groupBy(fn (TrainingScheduleItem $item) => $item->date?->format('Y-m-d'));

            $generatedItems = [];
            $queueIndex = 0;

            foreach ($training->eventDates as $eventDate) {
                if (! $eventDate->start_time || ! $eventDate->end_time) {
                    continue;
                }

                $dayStart = Carbon::parse($eventDate->date.' '.$eventDate->start_time);
                $dayEnd = Carbon::parse($eventDate->date.' '.$eventDate->end_time);

                if ($dayEnd->lessThanOrEqualTo($dayStart)) {
                    continue;
                }

                $dateKey = $eventDate->date;
                $anchors = $this->buildAnchors($dayStart, $dayEnd, $dateKey, $preservedByDate->get($dateKey));
                $slots = $this->buildSlots($dayStart, $dayEnd, $anchors);

                foreach ($anchors as $anchor) {
                    if (! $anchor['create']) {
                        continue;
                    }

                    $generatedItems[] = $this->buildItemAttributes(
                        $training,
                        $anchor['date'],
                        $anchor['starts_at'],
                        $anchor['ends_at'],
                        $anchor['type'],
                        $anchor['title'],
                        $anchor['duration'],
                        null,
                        null,
                        null,
                        $anchor['meta'] ?? null,
                    );
                }

                $skipDay = false;

                foreach ($slots as $slot) {
                    if ($skipDay) {
                        break;
                    }

                    if ($queueIndex >= $queue->count()) {
                        break;
                    }

                    $current = $slot['start']->copy();
                    $slotEnd = $slot['end']->copy();
                    $minutesSinceBreak = 0;
                    $blockStart = $current->copy();
                    $blockIndexes = [];

                    while ($queueIndex < $queue->count()) {
                        if ($current->gte($slotEnd)) {
                            break;
                        }

                        if ($this->shouldInsertBreak($minutesSinceBreak, $current, $slotEnd)) {
                            $generatedItems[] = $this->buildItemAttributes(
                                $training,
                                $eventDate->date,
                                $current->copy(),
                                $current->copy()->addMinutes($this->breakMinutes),
                                'BREAK',
                                'Intervalo',
                                $this->breakMinutes,
                                null,
                                null,
                                null,
                                null,
                            );

                            $current->addMinutes($this->breakMinutes);
                            $minutesSinceBreak = 0;
                            $blockStart = $current->copy();
                            $blockIndexes = [];

                            continue;
                        }

                        $next = $queue[$queueIndex];
                        $planned = $next['planned'];

                        if ($planned === 0) {
                            $generatedItems[] = $this->buildItemAttributes(
                                $training,
                                $eventDate->date,
                                $current->copy(),
                                $current->copy(),
                                'SECTION',
                                $next['title'],
                                0,
                                $next['suggested'],
                                $next['min'],
                                $next['section_id'],
                                null,
                            );

                            $queueIndex++;

                            continue;
                        }

                        $sectionEnd = $current->copy()->addMinutes($planned);

                        if ($sectionEnd->lte($slotEnd)) {
                            $generatedItems[] = $this->buildItemAttributes(
                                $training,
                                $eventDate->date,
                                $current->copy(),
                                $sectionEnd->copy(),
                                'SECTION',
                                $next['title'],
                                $planned,
                                $next['suggested'],
                                $next['min'],
                                $next['section_id'],
                                null,
                            );

                            $blockIndexes[] = array_key_last($generatedItems);
                            $current = $sectionEnd->copy();
                            $minutesSinceBreak += $planned;
                            $queueIndex++;

                            continue;
                        }

                        $over = abs($slotEnd->diffInMinutes($sectionEnd, false));
                        $compressionApplied = $this->applyCompression(
                            $generatedItems,
                            $blockIndexes,
                            $blockStart,
                            $training,
                            $next,
                            $over,
                        );

                        if (! $compressionApplied) {
                            $skipDay = true;
                            break;
                        }

                        $blockIndexes[] = array_key_last($generatedItems);
                        $current = $generatedItems[array_key_last($generatedItems)]['ends_at']->copy();
                        $minutesSinceBreak = $blockStart->diffInMinutes($current);
                        $queueIndex++;
                    }
                }
            }

            $createdItems = $this->persistItems($training, $generatedItems);

            $this->markConflicts(
                $training->scheduleItems()->orderBy('date')->orderBy('starts_at')->get(),
            );

            $unallocated = $queue->slice($queueIndex)->pluck('section');

            return new GenerationResult($createdItems, $unallocated);
        });
    }

    /**
     * @param  Collection<int, TrainingScheduleItem>  $items
     */
    public function markConflicts(Collection $items): void
    {
        $items->groupBy(fn (TrainingScheduleItem $item) => $item->date?->format('Y-m-d'))
            ->each(function (Collection $group): void {
                $sorted = $group->sortBy('starts_at')->values();

                $sorted->each(function (TrainingScheduleItem $item): void {
                    $item->status = 'OK';
                    $item->conflict_reason = null;
                });

                for ($index = 0; $index < $sorted->count() - 1; $index++) {
                    $current = $sorted[$index];
                    $next = $sorted[$index + 1];

                    if (! $current->ends_at || ! $next->starts_at) {
                        continue;
                    }

                    if ($current->ends_at->gt($next->starts_at)) {
                        $current->status = 'CONFLICT';
                        $current->conflict_reason = ['type' => 'OVERLAP', 'with' => $next->id];

                        $next->status = 'CONFLICT';
                        $next->conflict_reason = ['type' => 'OVERLAP', 'with' => $current->id];
                    }
                }

                $sorted->each(function (TrainingScheduleItem $item): void {
                    if ($item->isDirty(['status', 'conflict_reason'])) {
                        $item->save();
                    }
                });
            });
    }

    private function deletePreviousItems(Training $training, string $mode): void
    {
        if ($mode === 'FULL') {
            $training->scheduleItems()->delete();

            return;
        }

        $training->scheduleItems()
            ->where('origin', 'AUTO')
            ->where('is_locked', false)
            ->delete();
    }

    /**
     * @param  Collection<int, \App\Models\Section>  $sections
     * @return Collection<int, array{section: \App\Models\Section, section_id: int, title: string, suggested: int, min: int, planned: int}>
     */
    private function buildQueue(Collection $sections): Collection
    {
        return $sections->map(function ($section): array {
            $suggested = (int) ($section->duration ?? 0);
            $min = (int) ceil($suggested * 0.75);

            return [
                'section' => $section,
                'section_id' => $section->id,
                'title' => $section->name ?? __('Unidade'),
                'suggested' => $suggested,
                'min' => $min,
                'planned' => $suggested,
            ];
        })->values();
    }

    /**
     * @param  EloquentCollection<int, TrainingScheduleItem>|null  $preserved
     * @return array<int, array{create: bool, date: string, starts_at: Carbon, ends_at: Carbon, type: string, title: string, duration: int, meta: array<string, mixed>|null}>
     */
    private function buildAnchors(Carbon $dayStart, Carbon $dayEnd, string $dateKey, ?EloquentCollection $preserved): array
    {
        $anchors = [];

        $lunchStart = Carbon::parse($dateKey.' 12:00:00');
        $lunchEnd = Carbon::parse($dateKey.' 13:00:00');

        if ($lunchStart->gte($dayStart) && $lunchEnd->lte($dayEnd)) {
            $anchors[] = [
                'create' => true,
                'date' => $dateKey,
                'starts_at' => $lunchStart,
                'ends_at' => $lunchEnd,
                'type' => 'MEAL',
                'title' => 'Almoço',
                'duration' => 60,
                'meta' => ['anchor' => 'lunch'],
            ];
        }

        if ($preserved) {
            foreach ($preserved as $item) {
                if (! $item->starts_at || ! $item->ends_at) {
                    continue;
                }

                $anchors[] = [
                    'create' => false,
                    'date' => $dateKey,
                    'starts_at' => $item->starts_at->copy(),
                    'ends_at' => $item->ends_at->copy(),
                    'type' => $item->type,
                    'title' => $item->title,
                    'duration' => (int) $item->planned_duration_minutes,
                    'meta' => null,
                ];
            }
        }

        usort($anchors, fn ($a, $b) => $a['starts_at'] <=> $b['starts_at']);

        return $anchors;
    }

    /**
     * @param  array<int, array{create: bool, starts_at: Carbon, ends_at: Carbon}>  $anchors
     * @return array<int, array{start: Carbon, end: Carbon}>
     */
    private function buildSlots(Carbon $dayStart, Carbon $dayEnd, array $anchors): array
    {
        $slots = [];
        $cursor = $dayStart->copy();

        foreach ($anchors as $anchor) {
            $anchorStart = $anchor['starts_at']->copy();
            $anchorEnd = $anchor['ends_at']->copy();

            if ($anchorEnd->lte($dayStart) || $anchorStart->gte($dayEnd)) {
                continue;
            }

            $start = $anchorStart->lt($dayStart) ? $dayStart->copy() : $anchorStart;
            $end = $anchorEnd->gt($dayEnd) ? $dayEnd->copy() : $anchorEnd;

            if ($start->gt($cursor)) {
                $slots[] = ['start' => $cursor->copy(), 'end' => $start->copy()];
            }

            if ($end->gt($cursor)) {
                $cursor = $end->copy();
            }
        }

        if ($cursor->lt($dayEnd)) {
            $slots[] = ['start' => $cursor->copy(), 'end' => $dayEnd->copy()];
        }

        return $slots;
    }

    private function shouldInsertBreak(int $minutesSinceBreak, CarbonInterface $current, CarbonInterface $slotEnd): bool
    {
        if ($minutesSinceBreak >= $this->minBreakAt && $this->canFitBreak($current, $slotEnd)) {
            return true;
        }

        if ($minutesSinceBreak > $this->maxBreakAt && $this->canFitBreak($current, $slotEnd)) {
            return true;
        }

        return false;
    }

    private function canFitBreak(CarbonInterface $current, CarbonInterface $slotEnd): bool
    {
        return $current->copy()->addMinutes($this->breakMinutes)->lte($slotEnd);
    }

    /**
     * @param  array<int, array<string, mixed>>  $generatedItems
     * @param  array<int, int>  $blockIndexes
     * @param  array{section: \App\Models\Section, section_id: int, title: string, suggested: int, min: int, planned: int}  $next
     */
    private function applyCompression(
        array &$generatedItems,
        array $blockIndexes,
        Carbon $blockStart,
        Training $training,
        array $next,
        int $over,
    ): bool {
        $candidateItems = [];

        foreach ($blockIndexes as $index) {
            $candidateItems[] = [
                'source' => 'generated',
                'index' => $index,
                'planned' => (int) $generatedItems[$index]['planned_duration_minutes'],
                'min' => (int) ($generatedItems[$index]['min_duration_minutes'] ?? 0),
            ];
        }

        $candidateItems[] = [
            'source' => 'new',
            'index' => null,
            'planned' => (int) $next['planned'],
            'min' => (int) $next['min'],
        ];

        $budgets = [];
        foreach ($candidateItems as $key => $item) {
            $budgets[$key] = max(0, $item['planned'] - $item['min']);
        }

        $totalBudget = array_sum($budgets);

        if ($totalBudget < $over) {
            return false;
        }

        $reductions = $this->allocateReductions($budgets, $over);
        $cursor = $blockStart->copy();

        foreach ($candidateItems as $key => $candidate) {
            $newPlanned = $candidate['planned'] - ($reductions[$key] ?? 0);
            $start = $cursor->copy();
            $end = $cursor->copy()->addMinutes($newPlanned);

            if ($candidate['source'] === 'generated') {
                $generatedItems[$candidate['index']]['planned_duration_minutes'] = $newPlanned;
                $generatedItems[$candidate['index']]['starts_at'] = $start;
                $generatedItems[$candidate['index']]['ends_at'] = $end;
            } else {
                $generatedItems[] = $this->buildItemAttributes(
                    $training,
                    $start->format('Y-m-d'),
                    $start->copy(),
                    $end->copy(),
                    'SECTION',
                    $next['title'],
                    $newPlanned,
                    $next['suggested'],
                    $next['min'],
                    $next['section_id'],
                    null,
                );
            }

            $cursor = $end->copy();
        }

        return true;
    }

    /**
     * @param  array<int, int>  $budgets
     * @return array<int, int>
     */
    private function allocateReductions(array $budgets, int $over): array
    {
        $totalBudget = array_sum($budgets);
        $reductions = [];

        if ($totalBudget === 0) {
            return $reductions;
        }

        foreach ($budgets as $key => $budget) {
            $reductions[$key] = $budget > 0
                ? (int) floor($over * ($budget / $totalBudget))
                : 0;
        }

        $remaining = $over - array_sum($reductions);

        if ($remaining <= 0) {
            return $reductions;
        }

        $keys = array_keys($budgets);
        usort($keys, function ($left, $right) use ($budgets, $reductions): int {
            $leftRemaining = $budgets[$left] - ($reductions[$left] ?? 0);
            $rightRemaining = $budgets[$right] - ($reductions[$right] ?? 0);

            return $rightRemaining <=> $leftRemaining;
        });

        $keys = array_values(array_filter($keys, function ($key) use ($budgets, $reductions): bool {
            return $budgets[$key] - ($reductions[$key] ?? 0) > 0;
        }));

        $index = 0;

        while ($remaining > 0 && count($keys) > 0) {
            $key = $keys[$index % count($keys)];

            if ($budgets[$key] - $reductions[$key] > 0) {
                $reductions[$key]++;
                $remaining--;
            }

            $index++;
        }

        return $reductions;
    }

    /**
     * @param  array<int, array<string, mixed>>  $generatedItems
     * @return Collection<int, TrainingScheduleItem>
     */
    private function persistItems(Training $training, array $generatedItems): Collection
    {
        $items = collect();

        foreach ($generatedItems as $item) {
            $attributes = $item;
            unset($attributes['training']);

            $items->push($training->scheduleItems()->create($attributes));
        }

        return $items;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildItemAttributes(
        Training $training,
        string $date,
        CarbonInterface $startsAt,
        CarbonInterface $endsAt,
        string $type,
        string $title,
        int $plannedMinutes,
        ?int $suggestedMinutes,
        ?int $minMinutes,
        ?int $sectionId,
        ?array $meta,
    ): array {
        return [
            'training' => $training,
            'training_id' => $training->id,
            'section_id' => $sectionId,
            'date' => $date,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'type' => $type,
            'title' => $title,
            'planned_duration_minutes' => $plannedMinutes,
            'suggested_duration_minutes' => $suggestedMinutes,
            'min_duration_minutes' => $minMinutes,
            'origin' => 'AUTO',
            'is_locked' => false,
            'status' => 'OK',
            'conflict_reason' => null,
            'meta' => $meta,
        ];
    }
}
