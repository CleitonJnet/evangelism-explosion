<?php

namespace App\Livewire\Pages\App\Teacher\Training;

use App\Models\Training;
use App\Models\TrainingScheduleItem;
use App\Services\Schedule\TrainingDayBlocksService;
use App\Services\Schedule\TrainingScheduleBreakPolicy;
use App\Services\Schedule\TrainingScheduleDayBlocksApplier;
use App\Services\Schedule\TrainingScheduleResetService;
use App\Services\Schedule\TrainingScheduleTimelineService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Livewire\Component;

class Schedule extends Component
{
    public Training $training;

    /**
     * @var Collection<int, \App\Models\EventDate>
     */
    public Collection $eventDates;

    /**
     * @var Collection<int, TrainingScheduleItem>
     */
    public Collection $scheduleItems;

    /**
     * @var array<int, int>
     */
    public array $durationInputs = [];

    /**
     * @var array<string, array<string, bool>>
     */
    public array $dayBlocks = [];

    /**
     * @var array<string, array{showBreakfast: bool, showLunch: bool, showSnack: bool, showDinner: bool}>
     */
    public array $dayUi = [];

    public bool $busy = false;

    public function mount(Training $training): void
    {
        $this->authorizeTraining($training);
        $this->training = $training;
        $this->refreshSchedule();
    }

    public function regenerate(TrainingScheduleResetService $resetService): void
    {
        if ($this->busy) {
            return;
        }

        $this->authorizeTraining($this->training);
        $this->busy = true;

        try {
            $resetService->resetFull($this->training->id);
            $this->refreshSchedule();
        } finally {
            $this->busy = false;
        }
    }

    public function toggleDayBlock(
        TrainingScheduleDayBlocksApplier $applier,
        TrainingScheduleBreakPolicy $breakPolicy,
        string $dateKey,
        string $blockKey,
        bool $enabled,
    ): void {
        if ($this->busy) {
            return;
        }

        $this->authorizeTraining($this->training);
        $this->busy = true;

        try {
            app(TrainingDayBlocksService::class)->set($this->training->id, $dateKey, $blockKey, $enabled);
            $applier->apply($this->training->id, $dateKey, $blockKey, $enabled);

            if ($blockKey === 'snack' && ! $enabled) {
                $breakPolicy->suggestBreakIfLongRun($this->training->id, $dateKey, '15:30:00', 'snack_off');
            }

            $this->refreshSchedule();
        } finally {
            $this->busy = false;
        }
    }

    public function addBreak(TrainingScheduleTimelineService $timelineService, string $dateKey): void
    {
        if ($this->busy) {
            return;
        }

        $this->authorizeTraining($this->training);
        $this->busy = true;

        try {
            $items = TrainingScheduleItem::query()
                ->where('training_id', $this->training->id)
                ->whereDate('date', $dateKey)
                ->orderBy('position')
                ->orderBy('id')
                ->get();

            $targetPosition = (int) floor(($items->count() + 1) / 2);
            $targetPosition = max(1, $targetPosition);
            $insertIndex = $targetPosition - 1;
            $dayStart = $this->resolveDayStart($dateKey);

            $breakItem = TrainingScheduleItem::query()->create([
                'training_id' => $this->training->id,
                'section_id' => null,
                'date' => $dateKey,
                'starts_at' => $dayStart->copy(),
                'ends_at' => $dayStart->copy()->addMinutes(10),
                'type' => 'BREAK',
                'title' => 'Intervalo',
                'planned_duration_minutes' => 10,
                'suggested_duration_minutes' => null,
                'min_duration_minutes' => null,
                'origin' => 'TEACHER',
                'status' => 'OK',
                'conflict_reason' => null,
                'meta' => null,
            ]);

            $items->splice($insertIndex, 0, [$breakItem]);
            $this->syncPositions($items);

            $timelineService->reflowDay($this->training->id, $dateKey);
            $this->refreshSchedule();
        } finally {
            $this->busy = false;
        }
    }

    public function deleteBreak(
        TrainingScheduleTimelineService $timelineService,
        TrainingScheduleBreakPolicy $breakPolicy,
        int $itemId,
    ): void {
        if ($this->busy) {
            return;
        }

        $this->authorizeTraining($this->training);
        $this->busy = true;

        try {
            $item = TrainingScheduleItem::query()
                ->where('training_id', $this->training->id)
                ->findOrFail($itemId);

            if ($item->type !== 'BREAK') {
                return;
            }

            $dateKey = $item->date?->format('Y-m-d');
            $meta = is_array($item->meta) ? $item->meta : [];
            $reasonKey = $meta['auto_reason'] ?? null;

            $item->delete();

            if ($dateKey) {
                $remaining = TrainingScheduleItem::query()
                    ->where('training_id', $this->training->id)
                    ->whereDate('date', $dateKey)
                    ->orderBy('position')
                    ->orderBy('id')
                    ->get();

                $this->syncPositions($remaining);
                $timelineService->reflowDay($this->training->id, $dateKey);
            }

            if ($dateKey && $reasonKey === 'snack_off') {
                $breakPolicy->suppressSnackBreak($this->training->id, $dateKey);
            }

            $this->refreshSchedule();
        } finally {
            $this->busy = false;
        }
    }

    public function applyDuration(TrainingScheduleTimelineService $timelineService, int $id): void
    {
        if ($this->busy) {
            return;
        }

        $this->authorizeTraining($this->training);
        $this->busy = true;

        try {
            $item = TrainingScheduleItem::query()
                ->where('training_id', $this->training->id)
                ->findOrFail($id);

            $duration = (int) ($this->durationInputs[$id] ?? 0);

            $validator = Validator::make(
                ['planned_duration_minutes' => $duration],
                $this->durationRules(),
                $this->durationMessages(),
                $this->durationAttributes(),
            );

            if ($validator->fails()) {
                $this->dispatchScheduleAlert($validator->errors()->first() ?? 'Confira os valores informados.');

                return;
            }

            if ($item->section_id && $item->suggested_duration_minutes) {
                $min = (int) ceil($item->suggested_duration_minutes * 0.75);
                $max = (int) floor($item->suggested_duration_minutes * 1.25);

                if ($duration < $min || $duration > $max) {
                    $this->dispatchScheduleAlert('A duração deve estar dentro de 25% do valor sugerido.');

                    return;
                }
            }

            if ($item->section_id) {
                $meta = is_array($item->meta) ? $item->meta : [];
                $meta['fixed_duration'] = true;
                $item->meta = $meta;
            }

            $item->planned_duration_minutes = $duration;
            $item->origin = 'TEACHER';
            $item->save();

            $dateKey = $item->date?->format('Y-m-d');

            if ($dateKey) {
                $timelineService->reflowDay($this->training->id, $dateKey);
            }

            $this->refreshSchedule();
        } finally {
            $this->busy = false;
        }
    }

    public function moveAfter(
        TrainingScheduleTimelineService $timelineService,
        int $id,
        string $dateKey,
        ?int $afterItemId,
    ): void {
        if ($this->busy) {
            return;
        }

        $this->authorizeTraining($this->training);
        $this->busy = true;

        try {
            $validator = Validator::make(
                ['date' => $dateKey],
                ['date' => ['required', 'date_format:Y-m-d']],
                $this->moveItemMessages(),
                $this->moveItemAttributes(),
            );

            if ($validator->fails()) {
                $this->dispatchScheduleAlert($validator->errors()->first() ?? 'Confira os valores informados.');

                return;
            }

            $timelineService->moveAfter($this->training->id, $id, $dateKey, $afterItemId);
            $this->refreshSchedule();
        } finally {
            $this->busy = false;
        }
    }

    public function render(): View
    {
        $scheduleByDate = $this->scheduleItems
            ->groupBy(fn (TrainingScheduleItem $item) => $item->date?->format('Y-m-d'));

        return view('livewire.pages.app.teacher.training.schedule', [
            'scheduleByDate' => $scheduleByDate,
        ]);
    }

    private function refreshSchedule(): void
    {
        $this->training = Training::query()
            ->with([
                'course',
                'eventDates' => fn ($query) => $query->orderBy('date')->orderBy('start_time'),
                'scheduleItems' => fn ($query) => $query->with('section')->orderBy('date')->orderBy('position'),
            ])
            ->findOrFail($this->training->id);

        $this->eventDates = $this->training->eventDates;
        $this->scheduleItems = $this->training->scheduleItems;
        $dayBlocksService = app(TrainingDayBlocksService::class);
        $this->dayUi = $dayBlocksService->computeDayUi($this->training);
        $this->dayBlocks = $dayBlocksService->get($this->training->id);
        $normalizedBlocks = $dayBlocksService->normalizeDayBlocksForVisibility(
            $this->training,
            $this->dayBlocks,
            $this->dayUi,
        );

        if ($normalizedBlocks !== $this->dayBlocks) {
            $dayBlocksService->persistDayBlocks($this->training, $normalizedBlocks);
            $this->dayBlocks = $normalizedBlocks;
        }

        if ($this->cleanupHiddenMeals($this->dayUi)) {
            $this->training->load([
                'eventDates' => fn ($query) => $query->orderBy('date')->orderBy('start_time'),
                'scheduleItems' => fn ($query) => $query->with('section')->orderBy('date')->orderBy('position'),
            ]);

            $this->eventDates = $this->training->eventDates;
            $this->scheduleItems = $this->training->scheduleItems;
        }
        $this->syncDurationInputs();
    }

    private function syncDurationInputs(): void
    {
        $this->durationInputs = $this->scheduleItems
            ->mapWithKeys(function (TrainingScheduleItem $item): array {
                $duration = (int) $item->planned_duration_minutes;

                if ($duration <= 0) {
                    $duration = (int) ($item->suggested_duration_minutes ?? 60);
                }

                return [$item->id => $duration];
            })
            ->toArray();
    }

    private function authorizeTraining(Training $training): void
    {
        $teacherId = Auth::id();

        if (! $teacherId || $training->teacher_id !== $teacherId) {
            abort(403);
        }
    }

    private function dispatchScheduleAlert(string $message): void
    {
        $this->dispatch('schedule-alert', message: $message);
    }

    private function resolveDayStart(string $dateKey): Carbon
    {
        $eventDate = $this->eventDates->first(function ($eventDate) use ($dateKey): bool {
            if (! $eventDate->date) {
                return false;
            }

            $eventDateKey = is_string($eventDate->date)
                ? $eventDate->date
                : $eventDate->date->format('Y-m-d');

            return $eventDateKey === $dateKey;
        });

        $startTime = $eventDate?->start_time ?? '00:00:00';

        return Carbon::parse($dateKey.' '.$startTime);
    }

    /**
     * @param  array<string, array{showBreakfast: bool, showLunch: bool, showSnack: bool, showDinner: bool}>  $dayUi
     */
    private function cleanupHiddenMeals(array $dayUi): bool
    {
        $timelineService = app(TrainingScheduleTimelineService::class);
        $reflowDates = [];

        foreach ($dayUi as $dateKey => $flags) {
            $hiddenMeals = [];

            if (! ($flags['showBreakfast'] ?? false)) {
                $hiddenMeals[] = ['anchor' => ['breakfast'], 'subkind' => ['breakfast']];
            }

            if (! ($flags['showLunch'] ?? false)) {
                $hiddenMeals[] = ['anchor' => ['lunch'], 'subkind' => ['lunch']];
            }

            if (! ($flags['showSnack'] ?? false)) {
                $hiddenMeals[] = ['anchor' => ['afternoon_snack'], 'subkind' => ['snack']];
            }

            if (! ($flags['showDinner'] ?? false)) {
                $hiddenMeals[] = ['anchor' => ['dinner', 'night_snack'], 'subkind' => ['dinner']];
            }

            if ($hiddenMeals === []) {
                continue;
            }

            $query = TrainingScheduleItem::query()
                ->where('training_id', $this->training->id)
                ->whereDate('date', $dateKey)
                ->where('type', 'MEAL');

            $query->where(function ($subQuery) use ($hiddenMeals): void {
                foreach ($hiddenMeals as $meal) {
                    $anchors = $meal['anchor'];
                    $subkinds = $meal['subkind'];

                    $subQuery->orWhere(function ($or) use ($anchors, $subkinds): void {
                        $or->whereIn('meta->anchor', $anchors)
                            ->orWhereIn('meta->subkind', $subkinds);
                    });
                }
            });

            $deleted = $query->delete();

            if ($deleted > 0) {
                $reflowDates[] = $dateKey;
            }
        }

        foreach (array_unique($reflowDates) as $dateKey) {
            $timelineService->reflowDay($this->training->id, $dateKey);
        }

        return $reflowDates !== [];
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

    /**
     * @return array<string, array<int, string>>
     */
    private function durationRules(): array
    {
        return [
            'planned_duration_minutes' => ['required', 'integer', 'min:1', 'max:720'],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function durationMessages(): array
    {
        return [
            'planned_duration_minutes.integer' => 'A duração deve ser um número inteiro.',
            'planned_duration_minutes.min' => 'A duração deve ser de ao menos 1 minuto.',
            'planned_duration_minutes.max' => 'A duração deve ser de no máximo 720 minutos.',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function durationAttributes(): array
    {
        return [
            'planned_duration_minutes' => 'duração',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function moveItemMessages(): array
    {
        return [
            'date.required' => 'A data é obrigatória.',
            'date.date_format' => 'A data deve estar no formato YYYY-MM-DD.',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function moveItemAttributes(): array
    {
        return [
            'date' => 'data',
        ];
    }
}
