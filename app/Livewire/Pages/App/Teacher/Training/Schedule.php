<?php

namespace App\Livewire\Pages\App\Teacher\Training;

use App\Models\Training;
use App\Models\TrainingScheduleItem;
use App\Services\Schedule\TrainingScheduleGenerator;
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
     * @var array{
     *     welcome_duration_minutes: int,
     *     after_lunch_pause_minutes: int,
     *     devotional: array{
     *         after_welcome_enabled: bool,
     *         after_breakfast_enabled: bool,
     *         duration_minutes: int,
     *     },
     *     days: array<string, array{
     *         welcome_enabled: bool,
     *         welcome_duration_minutes: int,
     *         devotional_enabled: bool,
     *         devotional_duration_minutes: int,
     *         meals: array{
     *             breakfast: array{enabled: bool, duration_minutes: int},
     *             lunch: array{enabled: bool, duration_minutes: int},
     *             afternoon_snack: array{enabled: bool, duration_minutes: int},
     *             dinner: array{enabled: bool, duration_minutes: int, substitute_snack: bool}
     *         }
     *     }>,
     *     meals: array{
     *         breakfast: array{enabled: bool, duration_minutes: int},
     *         lunch: array{enabled: bool, duration_minutes: int},
     *         afternoon_snack: array{enabled: bool, duration_minutes: int},
     *         dinner: array{enabled: bool, duration_minutes: int, substitute_snack: bool}
     *     }
     * }
     */
    public array $scheduleSettings = [];

    public bool $busy = false;

    public function mount(Training $training, TrainingScheduleGenerator $generator): void
    {
        $this->authorizeTraining($training);
        $this->training = $training;
        $this->refreshSchedule($generator);
    }

    public function regenerate(TrainingScheduleResetService $resetService, TrainingScheduleGenerator $generator): void
    {
        if ($this->busy) {
            return;
        }

        $this->authorizeTraining($this->training);
        $this->busy = true;

        try {
            $resetService->resetFull($this->training->id);
            $this->refreshSchedule($generator);
        } finally {
            $this->busy = false;
        }
    }

    public function saveDaySettings(
        TrainingScheduleGenerator $generator,
        TrainingScheduleResetService $resetService,
        string $dateKey,
    ): void {
        if ($this->busy) {
            return;
        }

        $this->authorizeTraining($this->training);
        $this->busy = true;

        try {
            $this->persistDaySettings($dateKey, $this->scheduleSettings['days'][$dateKey] ?? []);
            $resetService->resetFull($this->training->id);
            $this->refreshSchedule($generator);
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
                $min = (int) ceil($item->suggested_duration_minutes * 0.8);
                $max = (int) floor($item->suggested_duration_minutes * 1.2);

                if ($duration < $min || $duration > $max) {
                    $this->dispatchScheduleAlert('A duração deve estar dentro de 20% do valor sugerido.');

                    return;
                }
            }

            if ($item->section_id) {
                $meta = is_array($item->meta) ? $item->meta : [];
                $meta['fixed_duration'] = true;
                $item->meta = $meta;
            }

            $this->updateDurationForDayAnchors($item, $duration);

            $item->planned_duration_minutes = $duration;
            $item->origin = 'TEACHER';
            $item->save();

            $dateKey = $item->date?->format('Y-m-d');

            if ($dateKey) {
                $timelineService->reflowDay($this->training->id, $dateKey);
            }

            $this->refreshSchedule(app(TrainingScheduleGenerator::class));
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
            $this->refreshSchedule(app(TrainingScheduleGenerator::class));
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

    private function refreshSchedule(TrainingScheduleGenerator $generator): void
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
        $this->scheduleSettings = $this->resolveScheduleSettings($generator);
        $this->syncDurationInputs();

        if ($this->ensureSingleWelcome()) {
            $this->training->load([
                'eventDates' => fn ($query) => $query->orderBy('date')->orderBy('start_time'),
                'scheduleItems' => fn ($query) => $query->with('section')->orderBy('date')->orderBy('position'),
            ]);

            $this->eventDates = $this->training->eventDates;
            $this->scheduleItems = $this->training->scheduleItems;
            $this->scheduleSettings = $this->resolveScheduleSettings($generator);
            $this->syncDurationInputs();
        }
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

    /**
     * @return array<string, mixed>
     */
    private function resolveScheduleSettings(TrainingScheduleGenerator $generator): array
    {
        $settings = $generator->settingsFor($this->training);
        $storedSettings = $this->training->schedule_settings;
        $storedSettings = is_array($storedSettings) ? $storedSettings : [];
        $devotional = $storedSettings['devotional'] ?? [];

        $settings['devotional'] = $this->normalizeDevotionalSettings($devotional);
        $settings['meals'] = $this->normalizeMealDurations($settings['meals'] ?? []);
        $settings['days'] = $this->resolveDaySettings($settings, $storedSettings);

        return $settings;
    }

    private function normalizeDevotionalSettings(array $devotional): array
    {
        $defaults = [
            'after_welcome_enabled' => true,
            'after_breakfast_enabled' => false,
            'duration_minutes' => 30,
        ];

        if (array_key_exists('enabled', $devotional)) {
            $devotional['after_welcome_enabled'] = (bool) $devotional['enabled'];
            $devotional['after_breakfast_enabled'] = (bool) ($devotional['after_breakfast_enabled'] ?? false);
        }

        if (isset($devotional['start_day']) || isset($devotional['after_welcome'])) {
            $devotional['after_breakfast_enabled'] = (bool) ($devotional['start_day']['enabled'] ?? false);
            $devotional['after_welcome_enabled'] = (bool) ($devotional['after_welcome']['enabled'] ?? true);

            $legacyDuration = $devotional['after_welcome']['duration_minutes']
                ?? $devotional['start_day']['duration_minutes']
                ?? $devotional['duration_minutes']
                ?? 30;

            $devotional['duration_minutes'] = $legacyDuration;
        }

        $merged = array_replace_recursive($defaults, $devotional);
        $merged['after_welcome_enabled'] = (bool) ($merged['after_welcome_enabled'] ?? true);
        $merged['after_breakfast_enabled'] = (bool) ($merged['after_breakfast_enabled'] ?? true);
        $merged['duration_minutes'] = $this->normalizeDevotionalMinutes((int) ($merged['duration_minutes'] ?? 30));

        return $merged;
    }

    private function normalizeDevotionalMinutes(int $minutes): int
    {
        if ($minutes < 5) {
            return 5;
        }

        if ($minutes > 180) {
            return 180;
        }

        return $minutes;
    }

    /**
     * @param  array<string, array<string, mixed>>  $meals
     * @return array<string, array<string, mixed>>
     */
    private function normalizeMealDurations(array $meals): array
    {
        $updated = $meals;

        foreach (['breakfast', 'lunch', 'afternoon_snack', 'dinner'] as $mealKey) {
            if (! isset($updated[$mealKey]) || ! is_array($updated[$mealKey])) {
                continue;
            }

            $duration = (int) ($updated[$mealKey]['duration_minutes'] ?? 0);
            $normalized = $this->normalizeIntervalMinutes($duration);

            if ($normalized !== $duration) {
                $updated[$mealKey]['duration_minutes'] = $normalized;
            }
        }

        return $updated;
    }

    private function normalizeIntervalMinutes(int $minutes): int
    {
        return $minutes === 59 ? 60 : $minutes;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function resolveDaySettings(array $settings, array $storedSettings, bool $syncFromItems = true): array
    {
        $days = $storedSettings['days'] ?? [];
        $mealsDefaults = $settings['meals'] ?? [];
        $devotionalDefaults = $settings['devotional'] ?? [];
        $firstDate = $this->eventDates->first()?->date;
        $resolved = [];

        foreach ($this->eventDates as $eventDate) {
            $dateKey = $eventDate->date;
            $storedDay = $days[$dateKey] ?? [];
            $storedDay = is_array($storedDay) ? $storedDay : [];

            $defaults = [
                'welcome_enabled' => $dateKey === $firstDate,
                'welcome_duration_minutes' => (int) ($settings['welcome_duration_minutes'] ?? 30),
                'devotional_enabled' => (bool) ($devotionalDefaults['after_welcome_enabled'] ?? true),
                'devotional_duration_minutes' => (int) ($devotionalDefaults['duration_minutes'] ?? 30),
                'meals' => $mealsDefaults,
            ];

            $merged = array_replace_recursive($defaults, $storedDay);
            $merged['welcome_enabled'] = (bool) ($merged['welcome_enabled'] ?? false);
            $merged['welcome_duration_minutes'] = $this->normalizeWelcomeMinutes(
                (int) ($merged['welcome_duration_minutes'] ?? 30)
            );
            $merged['devotional_enabled'] = (bool) ($merged['devotional_enabled'] ?? true);
            $merged['devotional_duration_minutes'] = $this->normalizeDevotionalMinutes(
                (int) ($merged['devotional_duration_minutes'] ?? 30)
            );
            $merged['meals'] = $this->normalizeMealDurations($merged['meals'] ?? []);
            $merged['meals']['dinner']['substitute_snack'] = (bool) ($merged['meals']['dinner']['substitute_snack'] ?? false);
            $merged['meals'] = $this->applyMealAvailability($merged['meals'], $eventDate);

            if ($syncFromItems) {
                $merged['meals'] = $this->syncDayMealSelectionFromItems($dateKey, $merged['meals']);
            }

            $resolved[$dateKey] = $merged;
        }

        return $resolved;
    }

    private function normalizeWelcomeMinutes(int $minutes): int
    {
        if ($minutes < 30) {
            return 30;
        }

        if ($minutes > 60) {
            return 60;
        }

        return $minutes;
    }

    /**
     * @param  array<string, array<string, mixed>>  $meals
     * @return array<string, array<string, mixed>>
     */
    private function applyMealAvailability(array $meals, $eventDate): array
    {
        if (! $eventDate?->start_time || ! $eventDate?->end_time) {
            return $meals;
        }

        $dayStart = Carbon::parse($eventDate->date.' '.$eventDate->start_time);
        $dayEnd = Carbon::parse($eventDate->date.' '.$eventDate->end_time);

        $availability = [
            'breakfast' => $this->isWithinWindow($dayStart, $dayEnd, '07:00:00', '10:30:00'),
            'lunch' => $this->isWithinWindow($dayStart, $dayEnd, '10:00:00', '15:00:00'),
            'afternoon_snack' => $this->isWithinWindow($dayStart, $dayEnd, '14:00:00', '17:00:00'),
            'dinner' => $this->isWithinWindow($dayStart, $dayEnd, '17:00:00', '21:00:00'),
        ];

        foreach ($availability as $mealKey => $allowed) {
            if (! $allowed) {
                $meals[$mealKey]['enabled'] = false;
            }
        }

        return $meals;
    }

    /**
     * @param  array<string, array<string, mixed>>  $meals
     * @return array<string, array<string, mixed>>
     */
    private function syncDayMealSelectionFromItems(string $dateKey, array $meals): array
    {
        if ($this->scheduleItems->isEmpty()) {
            return $meals;
        }

        $mealItems = $this->scheduleItems
            ->filter(fn (TrainingScheduleItem $item) => $item->type === 'MEAL')
            ->filter(fn (TrainingScheduleItem $item) => $item->date?->format('Y-m-d') === $dateKey);

        $hasSnack = $mealItems->contains(fn (TrainingScheduleItem $item) => ($item->meta['anchor'] ?? null) === 'afternoon_snack');
        $hasDinner = $mealItems->contains(fn (TrainingScheduleItem $item) => ($item->meta['anchor'] ?? null) === 'dinner');
        $hasNightSnack = $mealItems->contains(fn (TrainingScheduleItem $item) => ($item->meta['anchor'] ?? null) === 'night_snack');

        $meals['afternoon_snack']['enabled'] = $hasSnack;
        $meals['dinner']['enabled'] = $hasDinner || $hasNightSnack;
        $meals['dinner']['substitute_snack'] = $hasNightSnack;

        return $meals;
    }

    private function isWithinWindow(Carbon $dayStart, Carbon $dayEnd, string $windowStart, string $windowEnd): bool
    {
        $start = Carbon::parse($dayStart->format('Y-m-d').' '.$windowStart);
        $end = Carbon::parse($dayStart->format('Y-m-d').' '.$windowEnd);

        return $dayEnd->gt($start) && $dayStart->lt($end);
    }

    private function persistDaySettings(string $dateKey, array $daySettings): void
    {
        $normalized = $this->resolveDaySettings($this->scheduleSettings, [
            'days' => [$dateKey => $daySettings],
        ], false);

        $storedSettings = $this->training->schedule_settings;
        $storedSettings = is_array($storedSettings) ? $storedSettings : [];
        $storedSettings['days'] = $storedSettings['days'] ?? [];
        $storedSettings['days'][$dateKey] = $normalized[$dateKey] ?? $daySettings;

        $this->training->schedule_settings = $storedSettings;
        $this->training->save();

        $this->scheduleSettings['days'][$dateKey] = $storedSettings['days'][$dateKey];
    }

    private function ensureSingleWelcome(): bool
    {
        $firstDate = $this->eventDates->first()?->date;

        if (! $firstDate) {
            return false;
        }

        $dateKey = $firstDate instanceof Carbon ? $firstDate->format('Y-m-d') : (string) $firstDate;

        $welcomeItems = $this->scheduleItems
            ->filter(fn (TrainingScheduleItem $item) => $item->type === 'WELCOME')
            ->filter(fn (TrainingScheduleItem $item) => $item->date?->format('Y-m-d') === $dateKey)
            ->sortBy('position')
            ->values();

        if ($welcomeItems->count() <= 1) {
            return false;
        }

        $keep = $welcomeItems->first();
        $duration = (int) ($keep?->planned_duration_minutes ?? 30);
        $duration = max(30, min(60, $duration));

        if ($this->training->welcome_duration_minutes !== $duration) {
            $this->training->welcome_duration_minutes = $duration;
            $this->training->save();
        }

        $idsToDelete = $welcomeItems->skip(1)->pluck('id')->values()->all();

        if ($idsToDelete === []) {
            return false;
        }

        TrainingScheduleItem::query()
            ->whereIn('id', $idsToDelete)
            ->delete();

        app(TrainingScheduleTimelineService::class)->reflowDay($this->training->id, $dateKey);

        return true;
    }

    private function updateDurationForDayAnchors(TrainingScheduleItem $item, int $duration): void
    {
        $dateKey = $item->date?->format('Y-m-d');

        if (! $dateKey) {
            return;
        }

        if ($item->type === 'MEAL') {
            $anchor = (string) ($item->meta['anchor'] ?? '');
            $mealKey = match ($anchor) {
                'breakfast' => 'breakfast',
                'lunch' => 'lunch',
                'afternoon_snack' => 'afternoon_snack',
                'dinner', 'night_snack' => 'dinner',
                default => null,
            };

            if (! $mealKey) {
                return;
            }

            $daySettings = $this->scheduleSettings['days'][$dateKey] ?? [];
            $daySettings['meals'][$mealKey]['enabled'] = true;
            $daySettings['meals'][$mealKey]['duration_minutes'] = $duration;

            if ($mealKey === 'dinner') {
                $daySettings['meals'][$mealKey]['substitute_snack'] = $anchor === 'night_snack';
            }

            $this->persistDaySettings($dateKey, $daySettings);

            return;
        }

        if ($item->type === 'WELCOME') {
            $daySettings = $this->scheduleSettings['days'][$dateKey] ?? [];
            $daySettings['welcome_enabled'] = true;
            $daySettings['welcome_duration_minutes'] = $duration;
            $this->persistDaySettings($dateKey, $daySettings);

            return;
        }

        if ($item->type === 'DEVOTIONAL') {
            $daySettings = $this->scheduleSettings['days'][$dateKey] ?? [];
            $daySettings['devotional_enabled'] = true;
            $daySettings['devotional_duration_minutes'] = $duration;
            $this->persistDaySettings($dateKey, $daySettings);
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
