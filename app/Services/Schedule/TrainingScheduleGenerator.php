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

    private const BREAK_TITLE = 'Intervalo';

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

    public function generate(Training $training): GenerationResult
    {
        return DB::transaction(function () use ($training): GenerationResult {
            $training->load([
                'eventDates' => fn ($query) => $query->orderBy('date')->orderBy('start_time'),
                'course.sections' => fn ($query) => $query->orderBy('order'),
                'scheduleItems' => fn ($query) => $query->orderBy('date')->orderBy('starts_at'),
            ]);

            $settings = $this->settingsFor($training);

            $plan = $this->buildPlanFromSections($training, $settings);

            $training->scheduleItems()->delete();

            $createdItems = $this->persistItems($training, $plan['items']);

            $this->markConflicts(
                $training->scheduleItems()->orderBy('date')->orderBy('starts_at')->get(),
            );

            return new GenerationResult($createdItems, collect($plan['unallocated']));
        });
    }

    public function normalizeGeneratedDurationsToFive(Training $training): void
    {
        $training->loadMissing('scheduleItems');

        $training->scheduleItems->each(function (TrainingScheduleItem $item): void {
            $minutes = (int) $item->planned_duration_minutes;
            $rounded = (int) (round($minutes / 5) * 5);

            if ($rounded <= 0) {
                $rounded = 5;
            }

            if ($item->type === 'SECTION') {
                if ($item->min_duration_minutes !== null) {
                    $rounded = max($rounded, (int) $item->min_duration_minutes);
                }

                if ($item->suggested_duration_minutes !== null) {
                    $base = (int) $item->suggested_duration_minutes;
                    $min = (int) floor($base * 0.75);
                    $max = (int) ceil($base * 1.25);
                    $rounded = max($min, min($rounded, $max));
                }
            }

            if ($rounded !== (int) $item->planned_duration_minutes) {
                $item->planned_duration_minutes = $rounded;
                $item->saveQuietly();
            }
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
        $firstDateKey = $this->resolveDateKey($training->eventDates->first()?->date);
        $lastScheduled = null;

        foreach ($training->eventDates as $eventDate) {
            if (! $eventDate->start_time || ! $eventDate->end_time) {
                continue;
            }

            $dateKey = $this->resolveDateKey($eventDate->date);

            if (! $dateKey) {
                continue;
            }

            $dayStart = Carbon::parse($dateKey.' '.$eventDate->start_time);
            $dayEnd = Carbon::parse($dateKey.' '.$eventDate->end_time);

            if ($dayEnd->lessThanOrEqualTo($dayStart)) {
                continue;
            }

            $daySettings = $this->resolveDaySettings($settings, $dateKey, $dateKey === $firstDateKey);
            $anchors = $this->buildAnchorsForDay(
                $dayStart,
                $dayEnd,
                $dateKey,
                $daySettings,
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
                true,
            );

            $generatedItems = array_merge($generatedItems, $result['items']);
            $queueIndex = $result['index'];

            $lastScheduled = [
                'dateKey' => $dateKey,
                'dayStart' => $dayStart,
                'dayEnd' => $dayEnd,
                'anchors' => $anchors,
                'cursor' => $this->resolveLastCursor($result['items'], $dateKey, $dayStart),
            ];
        }

        if ($queueIndex < count($queue) && $lastScheduled !== null) {
            $remainingMinutes = $this->sumRemainingQueueMinutes($queue, $queueIndex);
            $overflowEnd = $lastScheduled['cursor']
                ->copy()
                ->addMinutes($remainingMinutes + $this->overflowBufferMinutes($remainingMinutes, $settings));

            $overflowResult = $this->scheduleFromQueue(
                $training,
                $queue,
                $queueIndex,
                $lastScheduled['cursor'],
                $overflowEnd,
                $lastScheduled['dateKey'],
                $lastScheduled['anchors'],
                $settings,
                false,
            );

            $generatedItems = array_merge($generatedItems, $overflowResult['items']);
            $queueIndex = $overflowResult['index'];
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
        $firstDateKey = $this->resolveDateKey($training->eventDates->first()?->date);

        foreach ($training->eventDates as $eventDate) {
            if (! $eventDate->start_time || ! $eventDate->end_time) {
                continue;
            }

            $dateKey = $this->resolveDateKey($eventDate->date);

            if (! $dateKey) {
                continue;
            }

            $dayStart = Carbon::parse($dateKey.' '.$eventDate->start_time);
            $dayEnd = Carbon::parse($dateKey.' '.$eventDate->end_time);

            if ($dayEnd->lessThanOrEqualTo($dayStart)) {
                continue;
            }

            $dayItems = $prepared['itemsByDate'][$dateKey] ?? [];
            $daySettings = $this->resolveDaySettings($settings, $dateKey, $dateKey === $firstDateKey);
            $anchors = $this->buildAnchorsForDay(
                $dayStart,
                $dayEnd,
                $dateKey,
                $daySettings,
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
     * @return array{itemsByDate: array<string, array<int, array<string, mixed>>>}
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
        $orderIndex = 0;

        foreach ($orderedItems as $item) {
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

        $lastDate = $this->resolveDateKey($training->eventDates->last()?->date);

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

        return [
            'itemsByDate' => $itemsByDate,
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
            $item['max_minutes'] = null;

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
        $item['max_minutes'] = $max;

        return $item;
    }

    /**
     * @param  Collection<int, Section>  $sections
     * @return Collection<int, array{section: Section, section_id: int, title: string, suggested: int, min: int, max: int, planned: int}>
     */
    private function buildSectionQueue(Collection $sections): Collection
    {
        return $sections->map(function ($section): array {
            $suggested = (int) ($section->duration ?? 0);
            $min = (int) ceil($suggested * 0.8);
            $max = (int) ceil($suggested * 1.2);

            return [
                'section' => $section,
                'section_id' => $section->id,
                'title' => $section->name ?? __('Unidade'),
                'suggested' => $suggested,
                'min' => $min,
                'max' => $max,
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

        $max = (int) ceil($suggested * 1.2);

        return [
            'order' => $order,
            'assigned_date' => $dateKey,
            'section_id' => $section->id,
            'type' => 'SECTION',
            'title' => $section->name ?? __('Unidade'),
            'planned_minutes' => $suggested,
            'suggested_minutes' => $suggested,
            'min_minutes' => $min,
            'max_minutes' => $max,
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
        bool $includeAnchors,
    ): array {
        $generatedItems = [];

        if ($includeAnchors) {
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
                );
            }
        }

        $slots = $this->buildSlots($dayStart, $dayEnd, $anchors);
        $postLunchStart = $this->resolvePostLunchStart($anchors);
        $afterLunchPause = (int) ($settings['after_lunch_pause_minutes'] ?? 10);
        $postLunchFirstSessionAvailable = $postLunchStart !== null;

        foreach ($slots as $slot) {
            if ($queueIndex >= count($queue)) {
                break;
            }

            $current = $slot['start']->copy();
            $slotEnd = $slot['end']->copy();
            $minutesSinceBreak = 0;

            while ($queueIndex < count($queue)) {
                if ($current->gte($slotEnd)) {
                    break;
                }

                $next = $queue[$queueIndex];
                $nextMin = (int) ($next['min'] ?? 0);
                $postLunch = $postLunchStart !== null && $current->gte($postLunchStart);

                if ($this->shouldInsertBreak($minutesSinceBreak, $current, $slotEnd, $nextMin)) {
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
                        self::BREAK_TITLE,
                        $this->breakMinutes,
                        null,
                        null,
                        null,
                        ['anchor' => 'break'],
                        'AUTO',
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
                    );

                    $queueIndex++;

                    continue;
                }

                $segment = $planned;
                $postLunchBreakRequired = false;

                if ($postLunch) {
                    $segmentMax = $postLunchFirstSessionAvailable ? 80 : 60;
                    $segment = min($segment, $segmentMax);

                    if ($postLunchFirstSessionAvailable) {
                        $postLunchBreakRequired = true;
                        $postLunchFirstSessionAvailable = false;
                    }
                }

                $requiredTime = $segment + ($postLunchBreakRequired ? $afterLunchPause : 0);
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
                );

                $current = $segmentEnd->copy();
                $minutesSinceBreak += $segment;

                $remaining = $planned - $segment;
                $hasRemainingContent = $remaining > 0 || ($queueIndex + 1) < count($queue);

                if ($postLunchBreakRequired && $hasRemainingContent) {
                    $pauseEnd = $current->copy()->addMinutes($afterLunchPause);

                    $generatedItems[] = $this->buildItemAttributes(
                        $training,
                        $dateKey,
                        $current->copy(),
                        $pauseEnd->copy(),
                        'BREAK',
                        self::BREAK_TITLE,
                        $afterLunchPause,
                        null,
                        null,
                        null,
                        ['anchor' => 'break'],
                        'AUTO',
                    );

                    $current = $pauseEnd->copy();
                    $minutesSinceBreak = 0;
                }

                if ($remaining > 0) {
                    $queue[$queueIndex]['planned'] = $remaining;
                } else {
                    $queueIndex++;
                }
            }
        }

        $generatedItems = $this->redistributeSlotMinutes($generatedItems, $slots, $postLunchStart);

        return ['items' => $generatedItems, 'index' => $queueIndex];
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    private function resolveLastCursor(array $items, string $dateKey, Carbon $fallback): Carbon
    {
        $last = null;

        foreach ($items as $item) {
            if (($item['date'] ?? null) !== $dateKey) {
                continue;
            }

            $end = $item['ends_at'] ?? null;

            if (! $end instanceof CarbonInterface) {
                continue;
            }

            if ($last === null || $end->gt($last)) {
                $last = $end->copy();
            }
        }

        return $last ?? $fallback->copy();
    }

    /**
     * @param  array<int, array<string, mixed>>  $queue
     */
    private function sumRemainingQueueMinutes(array $queue, int $startIndex): int
    {
        $total = 0;

        for ($index = $startIndex; $index < count($queue); $index++) {
            $total += (int) ($queue[$index]['planned'] ?? 0);
        }

        return $total;
    }

    /**
     * @param  array<string, mixed>  $settings
     */
    private function overflowBufferMinutes(int $remainingMinutes, array $settings): int
    {
        if ($remainingMinutes <= 0) {
            return 60;
        }

        $breaks = (int) ceil($remainingMinutes / max(1, $this->minBreakAt));
        $afterLunchPause = (int) ($settings['after_lunch_pause_minutes'] ?? 10);

        return ($breaks * $this->breakMinutes) + ($afterLunchPause * 2) + 60;
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
            );
        }

        $slots = $this->buildSlots($dayStart, $dayEnd, $anchors);
        $postLunchStart = $this->resolvePostLunchStart($anchors);
        $afterLunchPause = (int) ($settings['after_lunch_pause_minutes'] ?? 10);
        $postLunchFirstSessionAvailable = $postLunchStart !== null;

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

            while ($index < count($items)) {
                if ($current->gte($slotEnd)) {
                    break;
                }

                $next = $items[$index];
                $nextMin = (int) ($next['min_minutes'] ?? 0);
                $postLunch = $postLunchStart !== null && $current->gte($postLunchStart);

                if ($this->shouldInsertBreak($minutesSinceBreak, $current, $slotEnd, $nextMin)) {
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
                        self::BREAK_TITLE,
                        $this->breakMinutes,
                        null,
                        null,
                        null,
                        ['anchor' => 'break'],
                        'AUTO',
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
                    );

                    $index++;

                    continue;
                }

                $segment = $remaining;
                $postLunchBreakRequired = false;

                if ($postLunch && $this->countsAsTraining($next['type'])) {
                    $segmentMax = $postLunchFirstSessionAvailable ? 80 : 60;
                    $segment = min($segment, $segmentMax);

                    if ($postLunchFirstSessionAvailable) {
                        $postLunchBreakRequired = true;
                        $postLunchFirstSessionAvailable = false;
                    }
                }

                $requiredTime = $segment + ($postLunchBreakRequired ? $afterLunchPause : 0);
                $slotRemaining = $current->diffInMinutes($slotEnd, false);

                if ($slotRemaining < $requiredTime) {
                    if ($slotRemaining <= 0) {
                        break 2;
                    }

                    if ($this->isSectionFromCatalog($next) && $slotRemaining < $nextMin) {
                        break;
                    }

                    if ($postLunchBreakRequired && $slotRemaining > $afterLunchPause) {
                        $segment = $slotRemaining - $afterLunchPause;
                    } else {
                        $segment = $slotRemaining;
                    }
                }

                $segment = min($segment, $remaining);

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
                );

                $current = $segmentEnd->copy();

                if ($this->countsAsTraining($next['type'])) {
                    $minutesSinceBreak += $segment;
                } else {
                    $minutesSinceBreak = 0;
                }

                $nextRemaining = $remaining - $segment;
                $hasRemainingContent = $nextRemaining > 0 || ($index + 1) < count($items);

                if ($postLunchBreakRequired && $hasRemainingContent) {
                    $pauseEnd = $current->copy()->addMinutes($afterLunchPause);

                    $generatedItems[] = $this->buildItemAttributes(
                        $training,
                        $dateKey,
                        $current->copy(),
                        $pauseEnd->copy(),
                        'BREAK',
                        self::BREAK_TITLE,
                        $afterLunchPause,
                        null,
                        null,
                        null,
                        ['anchor' => 'break'],
                        'AUTO',
                    );

                    $current = $pauseEnd->copy();
                    $minutesSinceBreak = 0;
                }

                $next['remaining_minutes'] = $nextRemaining;

                if ($nextRemaining <= 0) {
                    $index++;
                } else {
                    $items[$index] = $next;
                }
            }
        }

        if ($index < count($items)) {
            $unallocated = array_slice($items, $index);
        }

        $generatedItems = $this->redistributeSlotMinutes($generatedItems, $slots, $postLunchStart);

        return ['items' => $generatedItems, 'unallocated' => $unallocated];
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @param  array<int, array{start: Carbon, end: Carbon}>  $slots
     * @return array<int, array<string, mixed>>
     */
    private function redistributeSlotMinutes(array $items, array $slots, ?Carbon $postLunchStart): array
    {
        foreach ($slots as $slot) {
            if ($postLunchStart && $slot['start']->gte($postLunchStart)) {
                continue;
            }

            $slotItems = [];

            foreach ($items as $index => $item) {
                $startsAt = $item['starts_at'] ?? null;
                $endsAt = $item['ends_at'] ?? null;

                if (! $startsAt || ! $endsAt) {
                    continue;
                }

                if ($startsAt->gte($slot['start']) && $endsAt->lte($slot['end'])) {
                    $slotItems[] = $index;
                }
            }

            if ($slotItems === []) {
                continue;
            }

            $sectionIndexes = [];
            $sectionTotal = 0;
            $fixedSectionTotal = 0;
            $otherTotal = 0;
            $durations = [];
            $mins = [];
            $maxs = [];

            foreach ($slotItems as $index) {
                $duration = (int) ($items[$index]['planned_duration_minutes'] ?? 0);
                $durations[$index] = $duration;

                if ($this->isSectionItem($items[$index])) {
                    if ($this->hasFixedDuration($items[$index])) {
                        $fixedSectionTotal += $duration;
                    } else {
                        $sectionIndexes[] = $index;
                        $sectionTotal += $duration;
                    }
                    $suggested = (int) ($items[$index]['suggested_duration_minutes'] ?? $duration);
                    $mins[$index] = (int) ($items[$index]['min_duration_minutes'] ?? ceil($suggested * 0.8));
                    $maxs[$index] = (int) ($items[$index]['max_duration_minutes'] ?? ceil($suggested * 1.2));
                } else {
                    $otherTotal += $duration;
                }
            }

            if ($sectionTotal <= 0 && $fixedSectionTotal <= 0) {
                continue;
            }

            $slotMinutes = $slot['start']->diffInMinutes($slot['end'], false);
            $availableForSections = $slotMinutes - $otherTotal - $fixedSectionTotal;

            if ($availableForSections <= 0) {
                continue;
            }

            $scaled = $sectionIndexes === []
                ? []
                : $this->scaleDurationsBounded($sectionIndexes, $durations, $mins, $maxs, $availableForSections);
            $cursor = $slot['start']->copy();

            foreach ($slotItems as $index) {
                $duration = $durations[$index];

                if (array_key_exists($index, $scaled)) {
                    $duration = $scaled[$index];
                    $items[$index]['planned_duration_minutes'] = $duration;
                }

                $items[$index]['starts_at'] = $cursor->copy();
                $items[$index]['ends_at'] = $cursor->copy()->addMinutes($duration);
                $cursor = $items[$index]['ends_at']->copy();
            }
        }

        return $items;
    }

    /**
     * @param  array<int, int>  $sectionIndexes
     * @param  array<int, int>  $durations
     * @return array<int, int>
     */
    private function scaleDurationsBounded(
        array $sectionIndexes,
        array $durations,
        array $mins,
        array $maxs,
        int $available,
    ): array {
        $total = 0;

        foreach ($sectionIndexes as $index) {
            $total += $durations[$index] ?? 0;
        }

        if ($total <= 0) {
            return [];
        }

        $scaled = [];
        $fractions = [];
        $sum = 0;

        foreach ($sectionIndexes as $index) {
            $raw = ($durations[$index] / $total) * $available;
            $floor = (int) floor($raw);
            $min = (int) ($mins[$index] ?? 0);
            $max = (int) ($maxs[$index] ?? $available);
            $value = max($min, min($max, $floor));
            $scaled[$index] = $value;
            $sum += $value;
            $fractions[$index] = $raw - $floor;
        }

        if ($sum === $available) {
            return $scaled;
        }

        if ($sum < $available) {
            $remainder = $available - $sum;
            arsort($fractions);

            while ($remainder > 0) {
                $progress = false;

                foreach (array_keys($fractions) as $index) {
                    if ($remainder <= 0) {
                        break;
                    }

                    $max = (int) ($maxs[$index] ?? $available);

                    if ($scaled[$index] >= $max) {
                        continue;
                    }

                    $scaled[$index]++;
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

                foreach (array_keys($fractions) as $index) {
                    if ($excess <= 0) {
                        break;
                    }

                    $min = (int) ($mins[$index] ?? 0);

                    if ($scaled[$index] <= $min) {
                        continue;
                    }

                    $scaled[$index]--;
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

    /**
     * @param  array<string, mixed>  $item
     */
    private function isSectionItem(array $item): bool
    {
        return ($item['type'] ?? null) === 'SECTION' && ! empty($item['section_id']);
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function hasFixedDuration(array $item): bool
    {
        $meta = $item['meta'] ?? null;

        if (! is_array($meta)) {
            return false;
        }

        return ($meta['fixed_duration'] ?? false) === true;
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function isSectionFromCatalog(array $item): bool
    {
        return ($item['type'] ?? null) === 'SECTION' && ! empty($item['section_id']);
    }

    /**
     * @param  array<int, array{starts_at: Carbon, ends_at: Carbon, type: string, title: string, duration: int, suggested_minutes: int|null, min_minutes: int|null, section_id: int|null, origin: string, meta: array<string, mixed>|null}>  $anchors
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
     * @return array<int, array{starts_at: Carbon, ends_at: Carbon, type: string, title: string, duration: int, suggested_minutes: int|null, min_minutes: int|null, section_id: int|null, origin: string, meta: array<string, mixed>|null}>
     */
    private function buildAnchorsForDay(
        Carbon $dayStart,
        Carbon $dayEnd,
        string $dateKey,
        array $daySettings,
    ): array {
        $anchors = [];
        $minimumStart = $dayStart->copy();

        if (($daySettings['welcome_enabled'] ?? false) === true) {
            $duration = $this->resolveWelcomeDurationMinutes($daySettings);

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
                        'meta' => ['anchor' => 'welcome'],
                    ];

                    $minimumStart = $welcomeEnd->copy();
                }
            }
        }

        $meals = $daySettings['meals'] ?? [];

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

            if ($end->lt($dayEnd)) {
                $anchors[] = $this->buildMealAnchor($start, $end, 'Café da manhã', 'breakfast');
            }
        }

        if (($meals['lunch']['enabled'] ?? true) && $this->slotWithinDay($dateKey, self::LUNCH_START, $dayStart, $dayEnd)) {
            $start = Carbon::parse($dateKey.' '.self::LUNCH_START);
            $start = $start->lt($dayStart) ? $dayStart->copy() : $start;
            $start = $start->lt($minimumStart) ? $minimumStart->copy() : $start;
            $duration = (int) ($meals['lunch']['duration_minutes'] ?? 60);
            $end = $start->copy()->addMinutes($duration);

            if ($end->lt($dayEnd)) {
                $anchors[] = $this->buildMealAnchor($start, $end, 'Almoço', 'lunch');
            }
        }

        if (($meals['afternoon_snack']['enabled'] ?? true) && $this->slotWithinDay($dateKey, self::AFTERNOON_SNACK_START, $dayStart, $dayEnd)) {
            $start = Carbon::parse($dateKey.' '.self::AFTERNOON_SNACK_START);
            $start = $start->lt($dayStart) ? $dayStart->copy() : $start;
            $start = $start->lt($minimumStart) ? $minimumStart->copy() : $start;
            $duration = (int) ($meals['afternoon_snack']['duration_minutes'] ?? 30);
            $end = $start->copy()->addMinutes($duration);

            if ($end->lt($dayEnd)) {
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

            if ($end->lt($dayEnd)) {
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
            'meta' => ['anchor' => $anchor],
        ];
    }

    private function slotWithinDay(string $dateKey, string $time, Carbon $dayStart, Carbon $dayEnd): bool
    {
        $slot = Carbon::parse($dateKey.' '.$time);

        return $slot->gte($dayStart) && $slot->lt($dayEnd);
    }

    private function resolveDateKey(CarbonInterface|string|null $date): ?string
    {
        if ($date instanceof CarbonInterface) {
            return $date->format('Y-m-d');
        }

        if (is_string($date) && $date !== '') {
            return $date;
        }

        return null;
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

        $merged['meals'] = $this->normalizeMeals($merged['meals'] ?? []);

        return $merged;
    }

    /**
     * @param  array<string, mixed>  $settings
     * @return array<string, mixed>
     */
    private function resolveDaySettings(array $settings, string $dateKey, bool $isFirstDay): array
    {
        $days = $settings['days'] ?? [];
        $daySettings = $days[$dateKey] ?? [];
        $daySettings = is_array($daySettings) ? $daySettings : [];

        $defaults = [
            'welcome_enabled' => $isFirstDay,
            'welcome_duration_minutes' => (int) ($settings['welcome_duration_minutes'] ?? 30),
            'meals' => $settings['meals'] ?? [],
        ];

        $merged = array_replace_recursive($defaults, $daySettings);
        $merged['welcome_enabled'] = (bool) ($merged['welcome_enabled'] ?? false);
        $merged['welcome_duration_minutes'] = $this->resolveWelcomeDurationMinutes($merged);
        $merged['meals'] = $this->normalizeMeals($merged['meals'] ?? []);

        return $merged;
    }

    /**
     * @param  array<string, mixed>  $meals
     * @return array<string, mixed>
     */
    private function normalizeMeals(array $meals): array
    {
        $defaults = [
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
        ];

        $merged = array_replace_recursive($defaults, $meals);

        foreach (['breakfast', 'lunch', 'afternoon_snack', 'dinner'] as $mealKey) {
            $meal = $merged[$mealKey] ?? [];
            $merged[$mealKey]['enabled'] = (bool) ($meal['enabled'] ?? true);
            $merged[$mealKey]['duration_minutes'] = (int) ($meal['duration_minutes'] ?? 30);
        }

        $merged['dinner']['substitute_snack'] = (bool) ($merged['dinner']['substitute_snack'] ?? false);

        return $merged;
    }

    private function isAutoAnchor(TrainingScheduleItem $item): bool
    {
        $anchor = $item->meta['anchor'] ?? null;

        if ($anchor) {
            return true;
        }

        if (in_array($item->type, ['WELCOME'], true)) {
            return true;
        }

        return $item->origin === 'AUTO' && in_array($item->type, ['BREAK', 'MEAL'], true);
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

        $items->groupBy(fn (TrainingScheduleItem $item) => $item->date?->format('Y-m-d'))
            ->each(function (Collection $group): void {
                $sorted = $group->sortBy('starts_at')->values();
                $position = 1;

                foreach ($sorted as $item) {
                    $item->position = $position;
                    $item->save();
                    $position++;
                }
            });

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
            'status' => 'OK',
            'conflict_reason' => null,
            'meta' => $meta,
        ];
    }
}
