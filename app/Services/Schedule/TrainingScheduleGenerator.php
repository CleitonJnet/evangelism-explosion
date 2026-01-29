<?php

namespace App\Services\Schedule;

use App\Models\Section;
use App\Models\Training;
use App\Models\TrainingScheduleItem;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TrainingScheduleGenerator
{
    private const BREAKFAST_START = '07:30:00';

    private const BREAKFAST_END = '08:30:00';

    private const LUNCH_START = '12:00:00';

    private const AFTERNOON_SNACK_START = '15:30:00';

    private const DINNER_START = '18:00:00';

    private int $minBreakAt = 60;

    private int $maxBreakAt = 120;

    private int $breakMinutes = 15;

    /**
     * @return array<string, mixed>
     */
    public function settingsFor(Training $training): array
    {
        $settings = $training->schedule_settings ?? [];

        if (! is_array($settings)) {
            $settings = [];
        }

        if ($training->welcome_duration_minutes !== null) {
            $settings['welcome_duration_minutes'] = (int) $training->welcome_duration_minutes;
        }

        return $this->normalizeSettings($settings);
    }

    public function generate(Training $training, string $mode = 'AUTO_ONLY'): GenerationResult
    {
        return DB::transaction(function () use ($training, $mode): GenerationResult {
            $training->load([
                'eventDates' => fn ($query) => $query->orderBy('date')->orderBy('start_time'),
                'course.sections' => fn ($query) => $query->orderBy('order'),
                'scheduleItems' => fn ($query) => $query->orderBy('date')->orderBy('starts_at'),
            ]);

            $settings = $this->settingsFor($training);

            $plan = $mode === 'FULL'
                ? $this->buildPlanFromSections($training, $settings)
                : $this->buildPlanFromExisting($training, $settings);

            $training->scheduleItems()->delete();

            $createdItems = $this->persistItems($training, $plan['items']);

            $this->markConflicts(
                $training->scheduleItems()->orderBy('date')->orderBy('starts_at')->get(),
            );

            return new GenerationResult($createdItems, collect($plan['unallocated']));
        });
    }

    /**
     * @return array{items: array<int, array<string, mixed>>, unallocated: array<int, array<string, mixed>>}
     */
    public function preview(Training $training): array
    {
        $training->load([
            'eventDates' => fn ($query) => $query->orderBy('date')->orderBy('start_time'),
            'course.sections' => fn ($query) => $query->orderBy('order'),
            'scheduleItems' => fn ($query) => $query->orderBy('date')->orderBy('starts_at'),
        ]);

        $settings = $this->settingsFor($training);

        return $this->buildPlanFromExisting($training, $settings);
    }

    public function repositionItem(
        Training $training,
        TrainingScheduleItem $item,
        Carbon $targetStart,
        string $targetDate,
    ): void {
        $item->date = $targetDate;
        $item->starts_at = $targetStart->copy();
        $item->ends_at = $targetStart->copy()->addMinutes((int) $item->planned_duration_minutes);
        $item->origin = 'TEACHER';
        $item->save();

        $this->generate($training, 'AUTO_ONLY');
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

    /**
     * @param  array<string, mixed>  $settings
     * @return array{items: array<int, array<string, mixed>>, unallocated: array<int, array<string, mixed>>}
     */
    private function buildPlanFromSections(Training $training, array $settings): array
    {
        $sections = $training->course?->sections ?? collect();

        if ($sections->isEmpty() || $training->eventDates->isEmpty()) {
            return ['items' => [], 'unallocated' => []];
        }

        $queue = $this->buildSectionQueue($sections)->values()->all();
        $queueIndex = 0;
        $generatedItems = [];
        $unallocated = [];
        $firstDateKey = $training->eventDates->first()?->date;

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
            $anchors = $this->buildAnchorsForDay(
                $dayStart,
                $dayEnd,
                $dateKey,
                $settings,
                $dateKey === $firstDateKey,
                [],
            );

            $result = $this->scheduleFromQueue(
                $training,
                $queue,
                $queueIndex,
                $dayStart,
                $dayEnd,
                $dateKey,
                $anchors,
                $settings,
            );

            $generatedItems = array_merge($generatedItems, $result['items']);
            $queueIndex = $result['index'];
        }

        if ($queueIndex < count($queue)) {
            $unallocated = array_slice($queue, $queueIndex);
        }

        return ['items' => $generatedItems, 'unallocated' => $unallocated];
    }

    /**
     * @param  array<string, mixed>  $settings
     * @return array{items: array<int, array<string, mixed>>, unallocated: array<int, array<string, mixed>>}
     */
    private function buildPlanFromExisting(Training $training, array $settings): array
    {
        $existingItems = $training->scheduleItems;
        $prepared = $this->prepareContentFromExisting($training, $existingItems);

        $generatedItems = [];
        $unallocated = [];
        $firstDateKey = $training->eventDates->first()?->date;

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
            $dayItems = $prepared['itemsByDate'][$dateKey] ?? [];
            $lockedAnchors = $prepared['lockedByDate'][$dateKey] ?? [];

            $anchors = $this->buildAnchorsForDay(
                $dayStart,
                $dayEnd,
                $dateKey,
                $settings,
                $dateKey === $firstDateKey,
                $lockedAnchors,
            );

            $result = $this->scheduleFromItems(
                $training,
                $dayItems,
                $dayStart,
                $dayEnd,
                $dateKey,
                $anchors,
                $settings,
            );

            $generatedItems = array_merge($generatedItems, $result['items']);
            $unallocated = array_merge($unallocated, $result['unallocated']);
        }

        return ['items' => $generatedItems, 'unallocated' => $unallocated];
    }

    /**
     * @param  EloquentCollection<int, TrainingScheduleItem>  $existingItems
     * @return array{itemsByDate: array<string, array<int, array<string, mixed>>>, lockedByDate: array<string, array<int, array<string, mixed>>>}
     */
    private function prepareContentFromExisting(Training $training, EloquentCollection $existingItems): array
    {
        $sections = $training->course?->sections ?? collect();
        $sectionsById = $sections->keyBy('id');
        $orderedItems = $existingItems->sort(function (TrainingScheduleItem $left, TrainingScheduleItem $right): int {
            $leftDate = $left->date?->format('Y-m-d') ?? '';
            $rightDate = $right->date?->format('Y-m-d') ?? '';

            if ($leftDate !== $rightDate) {
                return $leftDate <=> $rightDate;
            }

            $leftStart = $left->starts_at?->timestamp ?? 0;
            $rightStart = $right->starts_at?->timestamp ?? 0;

            if ($leftStart !== $rightStart) {
                return $leftStart <=> $rightStart;
            }

            $leftUpdated = $left->updated_at?->timestamp ?? 0;
            $rightUpdated = $right->updated_at?->timestamp ?? 0;

            return $rightUpdated <=> $leftUpdated;
        })->values();

        $contentItems = [];
        $segmentGroups = [];
        $lockedAnchors = [];
        $orderIndex = 0;

        foreach ($orderedItems as $item) {
            if ($item->is_locked) {
                $lockedAnchors[] = [
                    'date' => $item->date?->format('Y-m-d'),
                    'starts_at' => $item->starts_at?->copy(),
                    'ends_at' => $item->ends_at?->copy(),
                    'type' => $item->type,
                    'title' => $item->title,
                    'planned_minutes' => (int) $item->planned_duration_minutes,
                    'suggested_minutes' => $item->suggested_duration_minutes ? (int) $item->suggested_duration_minutes : null,
                    'min_minutes' => $item->min_duration_minutes ? (int) $item->min_duration_minutes : null,
                    'section_id' => $item->section_id,
                    'origin' => $item->origin ?? 'TEACHER',
                    'meta' => $item->meta,
                ];
                $orderIndex++;

                continue;
            }

            if ($this->isAutoAnchor($item)) {
                $orderIndex++;

                continue;
            }

            $dateKey = $item->date?->format('Y-m-d') ?? null;

            if ($item->section_id && $this->isSegmentItem($item)) {
                $groupKey = $item->section_id.'|'.$dateKey;

                if (! array_key_exists($groupKey, $segmentGroups)) {
                    $segmentGroups[$groupKey] = [
                        'section_id' => $item->section_id,
                        'date' => $dateKey,
                        'order' => $orderIndex,
                        'title' => $item->title,
                        'origin' => $item->origin ?? 'AUTO',
                        'planned_minutes' => 0,
                    ];
                }

                $segmentGroups[$groupKey]['planned_minutes'] += (int) $item->planned_duration_minutes;
                $orderIndex++;

                continue;
            }

            $contentItems[] = [
                'order' => $orderIndex,
                'assigned_date' => $dateKey,
                'section_id' => $item->section_id,
                'type' => $item->type,
                'title' => $item->title,
                'planned_minutes' => (int) $item->planned_duration_minutes,
                'origin' => $item->origin ?? 'AUTO',
                'meta' => $item->meta,
            ];
            $orderIndex++;
        }

        foreach ($segmentGroups as $group) {
            $contentItems[] = [
                'order' => $group['order'],
                'assigned_date' => $group['date'],
                'section_id' => $group['section_id'],
                'type' => 'SECTION',
                'title' => $group['title'],
                'planned_minutes' => (int) $group['planned_minutes'],
                'origin' => $group['origin'],
                'meta' => ['segment_group' => true],
            ];
        }

        $contentItems = collect($contentItems)
            ->sortBy('order')
            ->values()
            ->all();

        $existingSectionIds = collect($contentItems)
            ->pluck('section_id')
            ->filter()
            ->unique()
            ->values();

        $lastDate = $training->eventDates->last()?->date;

        foreach ($sections as $section) {
            if ($existingSectionIds->contains($section->id)) {
                continue;
            }

            $contentItems[] = $this->buildSectionItem(
                $section,
                $lastDate,
                count($contentItems),
            );
        }

        $itemsByDate = [];

        foreach ($contentItems as $item) {
            $dateKey = $item['assigned_date'] ?? $lastDate;

            if (! $dateKey) {
                continue;
            }

            if (! array_key_exists($dateKey, $itemsByDate)) {
                $itemsByDate[$dateKey] = [];
            }

            $itemsByDate[$dateKey][] = $this->hydrateItemWithDurations($item, $sectionsById);
        }

        foreach ($itemsByDate as $dateKey => $items) {
            $itemsByDate[$dateKey] = collect($items)
                ->sortBy('order')
                ->values()
                ->all();
        }

        $lockedByDate = [];

        foreach ($lockedAnchors as $anchor) {
            $dateKey = $anchor['date'];

            if (! $dateKey || ! $anchor['starts_at'] || ! $anchor['ends_at']) {
                continue;
            }

            if (! array_key_exists($dateKey, $lockedByDate)) {
                $lockedByDate[$dateKey] = [];
            }

            $lockedByDate[$dateKey][] = $anchor;
        }

        return [
            'itemsByDate' => $itemsByDate,
            'lockedByDate' => $lockedByDate,
        ];
    }

    /**
     * @param  array<string, mixed>  $item
     * @param  Collection<int, Section>  $sectionsById
     * @return array<string, mixed>
     */
    private function hydrateItemWithDurations(array $item, Collection $sectionsById): array
    {
        if (! $item['section_id']) {
            $item['suggested_minutes'] = null;
            $item['min_minutes'] = null;

            return $item;
        }

        $section = $sectionsById->get($item['section_id']);
        $suggested = (int) ($section?->duration ?? $item['planned_minutes'] ?? 0);
        $min = (int) ceil($suggested * 0.8);
        $max = (int) ceil($suggested * 1.2);
        $planned = (int) ($item['planned_minutes'] ?? $suggested);

        if ($planned < $min) {
            $planned = $min;
        }

        if ($planned > $max) {
            $planned = $max;
        }

        $item['planned_minutes'] = $planned;
        $item['suggested_minutes'] = $suggested;
        $item['min_minutes'] = $min;

        return $item;
    }

    /**
     * @param  Collection<int, Section>  $sections
     * @return Collection<int, array{section: Section, section_id: int, title: string, suggested: int, min: int, planned: int}>
     */
    private function buildSectionQueue(Collection $sections): Collection
    {
        return $sections->map(function ($section): array {
            $suggested = (int) ($section->duration ?? 0);
            $min = (int) ceil($suggested * 0.8);

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
     * @return array<string, mixed>
     */
    private function buildSectionItem(Section $section, ?string $dateKey, int $order): array
    {
        $suggested = (int) ($section->duration ?? 0);
        $min = (int) ceil($suggested * 0.8);

        return [
            'order' => $order,
            'assigned_date' => $dateKey,
            'section_id' => $section->id,
            'type' => 'SECTION',
            'title' => $section->name ?? __('Unidade'),
            'planned_minutes' => $suggested,
            'suggested_minutes' => $suggested,
            'min_minutes' => $min,
            'origin' => 'AUTO',
            'meta' => null,
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $queue
     * @param  array<int, array<string, mixed>>  $anchors
     * @param  array<string, mixed>  $settings
     * @return array{items: array<int, array<string, mixed>>, index: int}
     */
    private function scheduleFromQueue(
        Training $training,
        array $queue,
        int $queueIndex,
        Carbon $dayStart,
        Carbon $dayEnd,
        string $dateKey,
        array $anchors,
        array $settings,
    ): array {
        $generatedItems = [];

        foreach ($anchors as $anchor) {
            $generatedItems[] = $this->buildItemAttributes(
                $training,
                $dateKey,
                $anchor['starts_at'],
                $anchor['ends_at'],
                $anchor['type'],
                $anchor['title'],
                $anchor['duration'],
                $anchor['suggested_minutes'],
                $anchor['min_minutes'],
                $anchor['section_id'],
                $anchor['meta'],
                $anchor['origin'],
                $anchor['is_locked'],
            );
        }

        $slots = $this->buildSlots($dayStart, $dayEnd, $anchors);
        $postLunchStart = $this->resolvePostLunchStart($anchors);
        $afterLunchPause = (int) ($settings['after_lunch_pause_minutes'] ?? 10);

        foreach ($slots as $slot) {
            if ($queueIndex >= count($queue)) {
                break;
            }

            $current = $slot['start']->copy();
            $slotEnd = $slot['end']->copy();
            $minutesSinceBreak = 0;
            $postLunch = $postLunchStart && $current->gte($postLunchStart);

            while ($queueIndex < count($queue)) {
                if ($current->gte($slotEnd)) {
                    break;
                }

                $next = $queue[$queueIndex];
                $nextMin = (int) ($next['min'] ?? 0);

                if (! $postLunch && $this->shouldInsertBreak($minutesSinceBreak, $current, $slotEnd, $nextMin)) {
                    $breakEnd = $current->copy()->addMinutes($this->breakMinutes);
                    if ($breakEnd->gt($slotEnd)) {
                        break;
                    }

                    $generatedItems[] = $this->buildItemAttributes(
                        $training,
                        $dateKey,
                        $current->copy(),
                        $breakEnd->copy(),
                        'BREAK',
                        'Intervalo',
                        $this->breakMinutes,
                        null,
                        null,
                        null,
                        ['anchor' => 'break'],
                        'AUTO',
                        false,
                    );

                    $current = $breakEnd->copy();
                    $minutesSinceBreak = 0;

                    continue;
                }

                $planned = (int) ($next['planned'] ?? 0);

                if ($planned <= 0) {
                    $generatedItems[] = $this->buildItemAttributes(
                        $training,
                        $dateKey,
                        $current->copy(),
                        $current->copy(),
                        'SECTION',
                        $next['title'],
                        0,
                        $next['suggested'] ?? null,
                        $next['min'] ?? null,
                        $next['section_id'],
                        null,
                        'AUTO',
                        false,
                    );

                    $queueIndex++;

                    continue;
                }

                $segment = $planned;
                $postLunchPauseRequired = false;

                if ($postLunch) {
                    $segment = min($segment, 60);
                    $postLunchPauseRequired = true;
                }

                $requiredTime = $segment + ($postLunchPauseRequired ? $afterLunchPause : 0);
                $slotRemaining = $current->diffInMinutes($slotEnd, false);

                if ($slotRemaining < $requiredTime) {
                    break;
                }

                $segmentStart = $current->copy();
                $segmentEnd = $segmentStart->copy()->addMinutes($segment);

                $generatedItems[] = $this->buildItemAttributes(
                    $training,
                    $dateKey,
                    $segmentStart,
                    $segmentEnd,
                    'SECTION',
                    $next['title'],
                    $segment,
                    $next['suggested'] ?? null,
                    $next['min'] ?? null,
                    $next['section_id'],
                    $postLunch && $next['section_id'] ? ['segment_of' => $next['section_id']] : null,
                    'AUTO',
                    false,
                );

                $current = $segmentEnd->copy();
                $minutesSinceBreak += $segment;

                if ($postLunchPauseRequired) {
                    $pauseEnd = $current->copy()->addMinutes($afterLunchPause);

                    $generatedItems[] = $this->buildItemAttributes(
                        $training,
                        $dateKey,
                        $current->copy(),
                        $pauseEnd->copy(),
                        'BREAK',
                        'Pausa',
                        $afterLunchPause,
                        null,
                        null,
                        null,
                        ['anchor' => 'after_lunch_pause'],
                        'AUTO',
                        false,
                    );

                    $current = $pauseEnd->copy();
                    $minutesSinceBreak = 0;
                }

                $remaining = $planned - $segment;

                if ($remaining > 0) {
                    $queue[$queueIndex]['planned'] = $remaining;
                } else {
                    $queueIndex++;
                }
            }
        }

        return ['items' => $generatedItems, 'index' => $queueIndex];
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @param  array<int, array<string, mixed>>  $anchors
     * @param  array<string, mixed>  $settings
     * @return array{items: array<int, array<string, mixed>>, unallocated: array<int, array<string, mixed>>}
     */
    private function scheduleFromItems(
        Training $training,
        array $items,
        Carbon $dayStart,
        Carbon $dayEnd,
        string $dateKey,
        array $anchors,
        array $settings,
    ): array {
        $generatedItems = [];
        $unallocated = [];

        foreach ($anchors as $anchor) {
            $generatedItems[] = $this->buildItemAttributes(
                $training,
                $dateKey,
                $anchor['starts_at'],
                $anchor['ends_at'],
                $anchor['type'],
                $anchor['title'],
                $anchor['duration'],
                $anchor['suggested_minutes'],
                $anchor['min_minutes'],
                $anchor['section_id'],
                $anchor['meta'],
                $anchor['origin'],
                $anchor['is_locked'],
            );
        }

        $slots = $this->buildSlots($dayStart, $dayEnd, $anchors);
        $postLunchStart = $this->resolvePostLunchStart($anchors);
        $afterLunchPause = (int) ($settings['after_lunch_pause_minutes'] ?? 10);

        $items = array_map(function (array $item): array {
            $item['remaining_minutes'] = (int) ($item['planned_minutes'] ?? 0);

            return $item;
        }, $items);

        $index = 0;

        foreach ($slots as $slot) {
            if ($index >= count($items)) {
                break;
            }

            $current = $slot['start']->copy();
            $slotEnd = $slot['end']->copy();
            $minutesSinceBreak = 0;
            $postLunch = $postLunchStart && $current->gte($postLunchStart);

            while ($index < count($items)) {
                if ($current->gte($slotEnd)) {
                    break;
                }

                $next = $items[$index];
                $nextMin = (int) ($next['min_minutes'] ?? 0);

                if (! $postLunch && $this->shouldInsertBreak($minutesSinceBreak, $current, $slotEnd, $nextMin)) {
                    $breakEnd = $current->copy()->addMinutes($this->breakMinutes);
                    if ($breakEnd->gt($slotEnd)) {
                        break;
                    }

                    $generatedItems[] = $this->buildItemAttributes(
                        $training,
                        $dateKey,
                        $current->copy(),
                        $breakEnd->copy(),
                        'BREAK',
                        'Intervalo',
                        $this->breakMinutes,
                        null,
                        null,
                        null,
                        ['anchor' => 'break'],
                        'AUTO',
                        false,
                    );

                    $current = $breakEnd->copy();
                    $minutesSinceBreak = 0;

                    continue;
                }

                $remaining = (int) ($next['remaining_minutes'] ?? $next['planned_minutes'] ?? 0);

                if ($remaining <= 0) {
                    $generatedItems[] = $this->buildItemAttributes(
                        $training,
                        $dateKey,
                        $current->copy(),
                        $current->copy(),
                        $next['type'],
                        $next['title'],
                        0,
                        $next['suggested_minutes'] ?? null,
                        $next['min_minutes'] ?? null,
                        $next['section_id'] ?? null,
                        $next['meta'] ?? null,
                        $next['origin'] ?? 'AUTO',
                        false,
                    );

                    $index++;

                    continue;
                }

                $segment = $remaining;
                $postLunchPauseRequired = false;

                if ($postLunch && $this->countsAsTraining($next['type'])) {
                    $segment = min($segment, 60);
                    $postLunchPauseRequired = true;
                }

                $requiredTime = $segment + ($postLunchPauseRequired ? $afterLunchPause : 0);
                $slotRemaining = $current->diffInMinutes($slotEnd, false);

                if ($slotRemaining < $requiredTime) {
                    break 2;
                }

                $segmentStart = $current->copy();
                $segmentEnd = $segmentStart->copy()->addMinutes($segment);

                $generatedItems[] = $this->buildItemAttributes(
                    $training,
                    $dateKey,
                    $segmentStart,
                    $segmentEnd,
                    $next['type'],
                    $next['title'],
                    $segment,
                    $next['suggested_minutes'] ?? null,
                    $next['min_minutes'] ?? null,
                    $next['section_id'] ?? null,
                    $postLunch && $next['section_id'] ? ['segment_of' => $next['section_id']] : $next['meta'] ?? null,
                    $next['origin'] ?? 'AUTO',
                    false,
                );

                $current = $segmentEnd->copy();

                if ($this->countsAsTraining($next['type'])) {
                    $minutesSinceBreak += $segment;
                }

                if ($postLunchPauseRequired) {
                    $pauseEnd = $current->copy()->addMinutes($afterLunchPause);

                    $generatedItems[] = $this->buildItemAttributes(
                        $training,
                        $dateKey,
                        $current->copy(),
                        $pauseEnd->copy(),
                        'BREAK',
                        'Pausa',
                        $afterLunchPause,
                        null,
                        null,
                        null,
                        ['anchor' => 'after_lunch_pause'],
                        'AUTO',
                        false,
                    );

                    $current = $pauseEnd->copy();
                    $minutesSinceBreak = 0;
                }

                $next['remaining_minutes'] = $remaining - $segment;

                if ($next['remaining_minutes'] <= 0) {
                    $index++;
                } else {
                    $items[$index] = $next;
                }
            }
        }

        if ($index < count($items)) {
            $unallocated = array_slice($items, $index);
        }

        return ['items' => $generatedItems, 'unallocated' => $unallocated];
    }

    /**
     * @param  array<int, array{starts_at: Carbon, ends_at: Carbon, type: string, title: string, duration: int, suggested_minutes: int|null, min_minutes: int|null, section_id: int|null, origin: string, is_locked: bool, meta: array<string, mixed>|null}>  $anchors
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

    /**
     * @param  array<string, mixed>  $settings
     * @param  array<int, array<string, mixed>>  $lockedAnchors
     * @return array<int, array{starts_at: Carbon, ends_at: Carbon, type: string, title: string, duration: int, suggested_minutes: int|null, min_minutes: int|null, section_id: int|null, origin: string, is_locked: bool, meta: array<string, mixed>|null}>
     */
    private function buildAnchorsForDay(
        Carbon $dayStart,
        Carbon $dayEnd,
        string $dateKey,
        array $settings,
        bool $includeWelcome,
        array $lockedAnchors,
    ): array {
        $anchors = [];
        $minimumStart = $dayStart->copy();

        foreach ($lockedAnchors as $anchor) {
            if (! $anchor['starts_at'] || ! $anchor['ends_at']) {
                continue;
            }

            $anchors[] = [
                'starts_at' => $anchor['starts_at']->copy(),
                'ends_at' => $anchor['ends_at']->copy(),
                'type' => $anchor['type'],
                'title' => $anchor['title'],
                'duration' => (int) $anchor['planned_minutes'],
                'suggested_minutes' => $anchor['suggested_minutes'] ?? null,
                'min_minutes' => $anchor['min_minutes'] ?? null,
                'section_id' => $anchor['section_id'] ?? null,
                'origin' => $anchor['origin'] ?? 'TEACHER',
                'is_locked' => true,
                'meta' => $anchor['meta'] ?? null,
            ];
        }

        if ($includeWelcome) {
            $duration = $this->resolveWelcomeDurationMinutes($settings);

            if ($duration > 0 && $dayStart->lt($dayEnd)) {
                $welcomeEnd = $dayStart->copy()->addMinutes($duration);

                if ($welcomeEnd->lte($dayEnd)) {
                    $anchors[] = [
                        'starts_at' => $dayStart->copy(),
                        'ends_at' => $welcomeEnd,
                        'type' => 'WELCOME',
                        'title' => 'Boas-vindas',
                        'duration' => $duration,
                        'suggested_minutes' => null,
                        'min_minutes' => null,
                        'section_id' => null,
                        'origin' => 'AUTO',
                        'is_locked' => true,
                        'meta' => ['anchor' => 'welcome'],
                    ];

                    $minimumStart = $welcomeEnd->copy();
                }
            }
        }

        $meals = $settings['meals'] ?? [];

        $anchors = array_merge($anchors, $this->buildMealAnchors($dateKey, $dayStart, $dayEnd, $meals, $minimumStart));

        usort($anchors, fn ($a, $b) => $a['starts_at'] <=> $b['starts_at']);

        return $anchors;
    }

    /**
     * @param  array<string, mixed>  $meals
     * @return array<int, array<string, mixed>>
     */
    private function buildMealAnchors(string $dateKey, Carbon $dayStart, Carbon $dayEnd, array $meals, Carbon $minimumStart): array
    {
        $anchors = [];

        if (($meals['breakfast']['enabled'] ?? true) && $this->slotWithinDay($dateKey, self::BREAKFAST_END, $dayStart, $dayEnd)) {
            $start = Carbon::parse($dateKey.' '.self::BREAKFAST_START);
            $start = $start->lt($dayStart) ? $dayStart->copy() : $start;
            $start = $start->lt($minimumStart) ? $minimumStart->copy() : $start;
            $duration = (int) ($meals['breakfast']['duration_minutes'] ?? 30);
            $end = $start->copy()->addMinutes($duration);

            if ($end->lte($dayEnd)) {
                $anchors[] = $this->buildMealAnchor($start, $end, 'Café da manhã', 'breakfast');
            }
        }

        if (($meals['lunch']['enabled'] ?? true) && $this->slotWithinDay($dateKey, self::LUNCH_START, $dayStart, $dayEnd)) {
            $start = Carbon::parse($dateKey.' '.self::LUNCH_START);
            $start = $start->lt($dayStart) ? $dayStart->copy() : $start;
            $start = $start->lt($minimumStart) ? $minimumStart->copy() : $start;
            $duration = (int) ($meals['lunch']['duration_minutes'] ?? 60);
            $end = $start->copy()->addMinutes($duration);

            if ($end->lte($dayEnd)) {
                $anchors[] = $this->buildMealAnchor($start, $end, 'Almoço', 'lunch');
            }
        }

        if (($meals['afternoon_snack']['enabled'] ?? true) && $this->slotWithinDay($dateKey, self::AFTERNOON_SNACK_START, $dayStart, $dayEnd)) {
            $start = Carbon::parse($dateKey.' '.self::AFTERNOON_SNACK_START);
            $start = $start->lt($dayStart) ? $dayStart->copy() : $start;
            $start = $start->lt($minimumStart) ? $minimumStart->copy() : $start;
            $duration = (int) ($meals['afternoon_snack']['duration_minutes'] ?? 30);
            $end = $start->copy()->addMinutes($duration);

            if ($end->lte($dayEnd)) {
                $anchors[] = $this->buildMealAnchor($start, $end, 'Lanche', 'afternoon_snack');
            }
        }

        if (($meals['dinner']['enabled'] ?? true) && $this->slotWithinDay($dateKey, self::DINNER_START, $dayStart, $dayEnd)) {
            $start = Carbon::parse($dateKey.' '.self::DINNER_START);
            $start = $start->lt($dayStart) ? $dayStart->copy() : $start;
            $start = $start->lt($minimumStart) ? $minimumStart->copy() : $start;
            $duration = (int) ($meals['dinner']['duration_minutes'] ?? 60);
            $end = $start->copy()->addMinutes($duration);
            $substitute = (bool) ($meals['dinner']['substitute_snack'] ?? false);
            $title = $substitute ? 'Lanche' : 'Jantar';
            $anchor = $substitute ? 'night_snack' : 'dinner';

            if ($end->lte($dayEnd)) {
                $anchors[] = $this->buildMealAnchor($start, $end, $title, $anchor);
            }
        }

        return $anchors;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildMealAnchor(Carbon $start, Carbon $end, string $title, string $anchor): array
    {
        return [
            'starts_at' => $start,
            'ends_at' => $end,
            'type' => 'MEAL',
            'title' => $title,
            'duration' => $start->diffInMinutes($end),
            'suggested_minutes' => null,
            'min_minutes' => null,
            'section_id' => null,
            'origin' => 'AUTO',
            'is_locked' => false,
            'meta' => ['anchor' => $anchor],
        ];
    }

    private function slotWithinDay(string $dateKey, string $time, Carbon $dayStart, Carbon $dayEnd): bool
    {
        $slot = Carbon::parse($dateKey.' '.$time);

        return $slot->gte($dayStart) && $slot->lt($dayEnd);
    }

    /**
     * @param  array<int, array<string, mixed>>  $anchors
     */
    private function resolvePostLunchStart(array $anchors): ?Carbon
    {
        foreach ($anchors as $anchor) {
            if (($anchor['meta']['anchor'] ?? null) === 'lunch') {
                return $anchor['ends_at']->copy();
            }
        }

        return null;
    }

    private function countsAsTraining(string $type): bool
    {
        $type = strtoupper($type);

        return ! in_array($type, ['BREAK', 'MEAL', 'WELCOME'], true);
    }

    private function shouldInsertBreak(
        int $minutesSinceBreak,
        CarbonInterface $current,
        CarbonInterface $slotEnd,
        int $nextMinDuration,
    ): bool {
        if ($minutesSinceBreak >= $this->minBreakAt && $this->canFitBreak($current, $slotEnd, $nextMinDuration)) {
            return true;
        }

        if ($minutesSinceBreak > $this->maxBreakAt && $this->canFitBreak($current, $slotEnd, $nextMinDuration)) {
            return true;
        }

        return false;
    }

    private function canFitBreak(CarbonInterface $current, CarbonInterface $slotEnd, int $nextMinDuration): bool
    {
        $afterBreak = $current->copy()->addMinutes($this->breakMinutes);
        $remainingMinutes = $afterBreak->diffInMinutes($slotEnd, false);

        if ($remainingMinutes <= 0) {
            return false;
        }

        return $remainingMinutes >= $nextMinDuration;
    }

    private function resolveWelcomeDurationMinutes(array $settings): int
    {
        $duration = (int) ($settings['welcome_duration_minutes'] ?? 30);

        if ($duration < 30) {
            return 30;
        }

        if ($duration > 60) {
            return 60;
        }

        return $duration;
    }

    /**
     * @param  array<string, mixed>  $settings
     * @return array<string, mixed>
     */
    private function normalizeSettings(array $settings): array
    {
        $defaults = [
            'welcome_duration_minutes' => 30,
            'after_lunch_pause_minutes' => 10,
            'meals' => [
                'breakfast' => [
                    'enabled' => true,
                    'duration_minutes' => 30,
                ],
                'lunch' => [
                    'enabled' => true,
                    'duration_minutes' => 60,
                ],
                'afternoon_snack' => [
                    'enabled' => true,
                    'duration_minutes' => 30,
                ],
                'dinner' => [
                    'enabled' => true,
                    'duration_minutes' => 60,
                    'substitute_snack' => false,
                ],
            ],
        ];

        $merged = array_replace_recursive($defaults, $settings);

        $merged['welcome_duration_minutes'] = (int) ($merged['welcome_duration_minutes'] ?? 30);
        $merged['after_lunch_pause_minutes'] = (int) ($merged['after_lunch_pause_minutes'] ?? 10);
        $merged['after_lunch_pause_minutes'] = max(5, min(10, $merged['after_lunch_pause_minutes']));

        foreach (['breakfast', 'lunch', 'afternoon_snack', 'dinner'] as $mealKey) {
            $meal = $merged['meals'][$mealKey] ?? [];
            $merged['meals'][$mealKey]['enabled'] = (bool) ($meal['enabled'] ?? true);
            $merged['meals'][$mealKey]['duration_minutes'] = (int) ($meal['duration_minutes'] ?? 30);
        }

        $merged['meals']['dinner']['substitute_snack'] = (bool) ($merged['meals']['dinner']['substitute_snack'] ?? false);

        return $merged;
    }

    private function isAutoAnchor(TrainingScheduleItem $item): bool
    {
        $anchor = $item->meta['anchor'] ?? null;

        if ($anchor) {
            return true;
        }

        return $item->origin === 'AUTO' && in_array($item->type, ['BREAK', 'MEAL', 'WELCOME'], true);
    }

    private function isSegmentItem(TrainingScheduleItem $item): bool
    {
        return isset($item->meta['segment_of']);
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
        string $origin,
        bool $isLocked,
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
            'origin' => $origin,
            'is_locked' => $isLocked,
            'status' => 'OK',
            'conflict_reason' => null,
            'meta' => $meta,
        ];
    }
}
