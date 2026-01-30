<?php

namespace App\Livewire\Pages\App\Teacher\Training;

use App\Models\Training;
use App\Models\TrainingScheduleItem;
use App\Services\Schedule\TrainingScheduleGenerator;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Livewire\Component;

class Schedule extends Component
{
    private const BREAKFAST_START = '07:30:00';

    private const BREAKFAST_END = '08:30:00';

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

    /**
     * @var array<string, array{
     *     day_minutes: int,
     *     day_label: string,
     *     scheduled_minutes: int,
     *     remaining_minutes: int,
     *     remaining_label: string,
     *     overflow_minutes: int,
     *     overflow_label: string,
     *     long_day_warning: bool
     * }>
     */
    public array $daySummaries = [];

    public int $totalWorkloadMinutes = 0;

    public string $totalWorkloadLabel = '00h';

    public bool $modalOpen = false;

    /**
     * @var array{title: string, type: string, date: string, time: string, duration: int}
     */
    public array $form = [
        'title' => '',
        'type' => 'SECTION',
        'date' => '',
        'time' => '',
        'duration' => 60,
    ];

    public function mount(Training $training, TrainingScheduleGenerator $generator): void
    {
        $this->authorizeTraining($training);
        $this->training = $training;
        $this->refreshSchedule($generator);
    }

    public function regenerate(TrainingScheduleGenerator $generator): void
    {
        $this->authorizeTraining($this->training);
        $this->generateSchedule($generator, 'FULL');
        $this->refreshSchedule($generator);
    }

    public function saveSettings(TrainingScheduleGenerator $generator): void
    {
        $this->authorizeTraining($this->training);

        $payload = $this->validatedSettingsPayload();

        if (! $payload) {
            return;
        }

        $this->training->welcome_duration_minutes = $payload['welcome_duration_minutes'];
        $this->training->schedule_settings = $payload['schedule_settings'];
        $this->training->save();

        $this->generateSchedule($generator, 'AUTO_ONLY');
        $this->refreshSchedule($generator);
    }

    public function saveDaySettings(TrainingScheduleGenerator $generator, string $dateKey): void
    {
        $this->authorizeTraining($this->training);

        $this->persistDaySettings($dateKey, $this->scheduleSettings['days'][$dateKey] ?? []);

        $this->generateSchedule($generator, 'AUTO_ONLY');
        $this->refreshSchedule($generator);
    }

    public function updateDuration(
        TrainingScheduleGenerator $generator,
        int $id,
        string $date,
        string $startsAt,
        int $duration,
    ): void {
        $this->authorizeTraining($this->training);

        $validator = Validator::make([
            'date' => $date,
            'starts_at' => $startsAt,
            'planned_duration_minutes' => $duration,
        ], $this->updateItemRules(), $this->updateItemMessages(), $this->updateItemAttributes());

        if ($validator->fails()) {
            $this->dispatchScheduleAlert($validator->errors()->first() ?? 'Confira os valores informados.');

            return;
        }

        $item = TrainingScheduleItem::query()
            ->where('training_id', $this->training->id)
            ->findOrFail($id);

        if ($item->is_locked) {
            $this->dispatchScheduleAlert('Esta sessão está travada.');

            return;
        }

        $duration = $this->normalizeIntervalDuration((string) $item->type, $duration);

        if ($this->updateDurationForDayAnchors($item, $duration)) {
            $this->generateSchedule($generator, 'AUTO_ONLY');
            $this->refreshSchedule($generator);

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

        $dateValue = Carbon::createFromFormat('Y-m-d', $date);
        $startValue = Carbon::createFromFormat('Y-m-d H:i:s', $startsAt)
            ->setDate($dateValue->year, $dateValue->month, $dateValue->day);

        $item->planned_duration_minutes = $duration;
        $item->origin = 'TEACHER';
        $item->date = $dateValue->format('Y-m-d');
        $item->starts_at = $startValue->copy();
        $item->ends_at = $startValue->copy()->addMinutes($duration);
        $item->save();

        $this->generateSchedule($generator, 'AUTO_ONLY');
        $this->refreshSchedule($generator);
    }

    public function applyDuration(TrainingScheduleGenerator $generator, int $id): void
    {
        $this->authorizeTraining($this->training);

        $item = $this->scheduleItems->firstWhere('id', $id);

        if (! $item || ! $item->date || ! $item->starts_at) {
            return;
        }

        $duration = (int) ($this->durationInputs[$id] ?? 0);

        $this->updateDuration(
            $generator,
            $id,
            $item->date->format('Y-m-d'),
            $item->starts_at->format('Y-m-d H:i:s'),
            $duration,
        );
    }

    public function moveItem(TrainingScheduleGenerator $generator, int $id, string $date, string $startsAt): void
    {
        $this->authorizeTraining($this->training);

        $validator = Validator::make([
            'date' => $date,
            'starts_at' => $startsAt,
        ], $this->moveItemRules(), $this->updateItemMessages(), $this->updateItemAttributes());

        if ($validator->fails()) {
            $this->dispatchScheduleAlert($validator->errors()->first() ?? 'Confira os valores informados.');

            return;
        }

        $item = TrainingScheduleItem::query()
            ->where('training_id', $this->training->id)
            ->findOrFail($id);

        if ($item->is_locked) {
            $this->dispatchScheduleAlert('Esta sessão está travada.');

            return;
        }

        $dateValue = Carbon::createFromFormat('Y-m-d', $date);
        $startValue = Carbon::createFromFormat('Y-m-d H:i:s', $startsAt)
            ->setDate($dateValue->year, $dateValue->month, $dateValue->day)
            ->subSecond();

        $duration = (int) $item->planned_duration_minutes;

        $item->origin = 'TEACHER';
        $item->date = $dateValue->format('Y-m-d');
        $item->starts_at = $startValue->copy();
        $item->ends_at = $startValue->copy()->addMinutes($duration);
        $item->save();

        $this->generateSchedule($generator, 'AUTO_ONLY');
        $this->refreshSchedule($generator);
    }

    public function toggleLock(TrainingScheduleGenerator $generator, int $id, bool $shouldLock): void
    {
        $this->authorizeTraining($this->training);

        $item = TrainingScheduleItem::query()
            ->where('training_id', $this->training->id)
            ->findOrFail($id);

        $item->is_locked = $shouldLock;
        $item->save();

        $this->refreshSchedule($generator);
    }

    public function deleteItem(TrainingScheduleGenerator $generator, int $id): void
    {
        $this->authorizeTraining($this->training);

        $item = TrainingScheduleItem::query()
            ->where('training_id', $this->training->id)
            ->findOrFail($id);

        $item->delete();

        $this->generateSchedule($generator, 'AUTO_ONLY');
        $this->refreshSchedule($generator);
    }

    public function openCreate(string $date, string $time): void
    {
        $this->authorizeTraining($this->training);

        $this->form = [
            'title' => '',
            'type' => 'SECTION',
            'date' => $date,
            'time' => $time,
            'duration' => 60,
        ];

        $this->modalOpen = true;
    }

    public function closeModal(): void
    {
        $this->modalOpen = false;
    }

    public function createItem(TrainingScheduleGenerator $generator): void
    {
        $this->authorizeTraining($this->training);

        $startsAt = $this->buildStartsAt($this->form['date'] ?? '', $this->form['time'] ?? '');

        if (! $startsAt) {
            $this->dispatchScheduleAlert('Informe a data e o horário inicial.');

            return;
        }

        $duration = (int) ($this->form['duration'] ?? 0);
        $duration = $this->normalizeIntervalDuration((string) ($this->form['type'] ?? ''), $duration);

        $validator = Validator::make([
            'date' => $this->form['date'] ?? '',
            'starts_at' => $startsAt,
            'planned_duration_minutes' => $duration,
            'title' => $this->form['title'] ?? '',
            'type' => $this->form['type'] ?? '',
        ], $this->storeItemRules(), $this->storeItemMessages(), $this->storeItemAttributes());

        if ($validator->fails()) {
            $this->dispatchScheduleAlert($validator->errors()->first() ?? 'Confira os valores informados.');

            return;
        }

        $dateValue = Carbon::createFromFormat('Y-m-d', (string) $this->form['date']);
        $startValue = Carbon::createFromFormat('Y-m-d H:i:s', $startsAt)
            ->setDate($dateValue->year, $dateValue->month, $dateValue->day);

        $this->training->scheduleItems()->create([
            'section_id' => null,
            'date' => $dateValue->format('Y-m-d'),
            'starts_at' => $startValue->copy(),
            'ends_at' => $startValue->copy()->addMinutes($duration),
            'type' => $this->form['type'],
            'title' => $this->form['title'],
            'planned_duration_minutes' => $duration,
            'suggested_duration_minutes' => null,
            'min_duration_minutes' => null,
            'origin' => 'TEACHER',
            'is_locked' => false,
            'status' => 'OK',
            'conflict_reason' => null,
            'meta' => null,
        ]);

        $this->generateSchedule($generator, 'AUTO_ONLY');
        $this->refreshSchedule($generator);

        $this->modalOpen = false;
        $this->form = [
            'title' => '',
            'type' => 'SECTION',
            'date' => '',
            'time' => '',
            'duration' => 60,
        ];
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
                'scheduleItems' => fn ($query) => $query->with('section')->orderBy('date')->orderBy('starts_at'),
            ])
            ->findOrFail($this->training->id);

        $this->eventDates = $this->training->eventDates;
        $this->scheduleItems = $this->training->scheduleItems;
        $this->scheduleSettings = $this->resolveScheduleSettings($generator);
        $this->syncDurationInputs();

        $this->applyScheduleAdjustments($generator);

        $preview = $generator->preview($this->training);
        $unallocatedByDate = collect($preview['unallocated'] ?? [])
            ->groupBy(fn (array $item) => $item['assigned_date'] ?? null);

        $this->totalWorkloadMinutes = $this->eventDates->reduce(function (int $total, $eventDate): int {
            if (! $eventDate->start_time || ! $eventDate->end_time) {
                return $total;
            }

            $start = Carbon::parse($eventDate->date.' '.$eventDate->start_time);
            $end = Carbon::parse($eventDate->date.' '.$eventDate->end_time);

            return $total + max(0, $start->diffInMinutes($end, false));
        }, 0);

        $this->totalWorkloadLabel = $this->formatDuration($this->totalWorkloadMinutes);

        $this->daySummaries = $this->eventDates->mapWithKeys(function ($eventDate) use ($unallocatedByDate): array {
            $dateKey = $eventDate->date;
            $items = $this->scheduleItems->filter(
                fn (TrainingScheduleItem $item) => $item->date?->format('Y-m-d') === $dateKey
            );

            $dayStart = $eventDate->start_time
                ? Carbon::parse($eventDate->date.' '.$eventDate->start_time)
                : null;
            $dayEnd = $eventDate->end_time
                ? Carbon::parse($eventDate->date.' '.$eventDate->end_time)
                : null;

            $dayMinutes = ($dayStart && $dayEnd) ? max(0, $dayStart->diffInMinutes($dayEnd, false)) : 0;
            $scheduledMinutes = (int) $items->sum('planned_duration_minutes');
            $remainingMinutes = max(0, $dayMinutes - $scheduledMinutes);

            $overflowMinutes = (int) ($unallocatedByDate->get($dateKey, collect())->sum('planned_minutes') ?? 0);

            $daySettings = $this->scheduleSettings['days'][$dateKey] ?? [];
            $meals = $daySettings['meals'] ?? [];
            $mealsEnabled = collect($meals)->contains(fn ($meal) => (bool) ($meal['enabled'] ?? false));
            $longDayWarning = $dayMinutes > 360 && ! $mealsEnabled;

            return [
                $dateKey => [
                    'day_minutes' => $dayMinutes,
                    'day_label' => $this->formatDuration($dayMinutes),
                    'scheduled_minutes' => $scheduledMinutes,
                    'remaining_minutes' => $remainingMinutes,
                    'remaining_label' => $this->formatDuration($remainingMinutes),
                    'overflow_minutes' => $overflowMinutes,
                    'overflow_label' => $this->formatDuration($overflowMinutes),
                    'long_day_warning' => $longDayWarning,
                ],
            ];
        })->toArray();
    }

    private function formatDuration(int $minutes): string
    {
        if ($minutes <= 0) {
            return '00h';
        }

        $hours = intdiv($minutes, 60);
        $remaining = $minutes % 60;

        return $remaining > 0
            ? sprintf('%02dh %02dmin', $hours, $remaining)
            : sprintf('%02dh', $hours);
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

    private function buildStartsAt(string $date, string $time): ?string
    {
        if (! $date || ! $time) {
            return null;
        }

        return sprintf('%s %s:00', $date, $time);
    }

    /**
     * @return array{welcome_duration_minutes: int, schedule_settings: array<string, mixed>}|null
     */
    private function validatedSettingsPayload(): ?array
    {
        $meals = $this->scheduleSettings['meals'] ?? [];
        $devotional = $this->scheduleSettings['devotional'] ?? [];

        $payload = [
            'welcome_duration_minutes' => (int) ($this->scheduleSettings['welcome_duration_minutes'] ?? 30),
            'schedule_settings' => [
                'after_lunch_pause_minutes' => (int) ($this->scheduleSettings['after_lunch_pause_minutes'] ?? 10),
                'devotional' => [
                    'after_welcome_enabled' => (bool) ($devotional['after_welcome_enabled'] ?? true),
                    'after_breakfast_enabled' => (bool) ($devotional['after_breakfast_enabled'] ?? false),
                    'duration_minutes' => (int) ($devotional['duration_minutes'] ?? 30),
                ],
                'meals' => [
                    'breakfast' => [
                        'enabled' => (bool) ($meals['breakfast']['enabled'] ?? true),
                        'duration_minutes' => $this->normalizeIntervalMinutes(
                            (int) ($meals['breakfast']['duration_minutes'] ?? 30)
                        ),
                    ],
                    'lunch' => [
                        'enabled' => (bool) ($meals['lunch']['enabled'] ?? true),
                        'duration_minutes' => $this->normalizeIntervalMinutes(
                            (int) ($meals['lunch']['duration_minutes'] ?? 60)
                        ),
                    ],
                    'afternoon_snack' => [
                        'enabled' => (bool) ($meals['afternoon_snack']['enabled'] ?? true),
                        'duration_minutes' => $this->normalizeIntervalMinutes(
                            (int) ($meals['afternoon_snack']['duration_minutes'] ?? 30)
                        ),
                    ],
                    'dinner' => [
                        'enabled' => (bool) ($meals['dinner']['enabled'] ?? true),
                        'duration_minutes' => $this->normalizeIntervalMinutes(
                            (int) ($meals['dinner']['duration_minutes'] ?? 60)
                        ),
                        'substitute_snack' => (bool) ($meals['dinner']['substitute_snack'] ?? false),
                    ],
                ],
            ],
        ];

        $validator = Validator::make($payload, $this->settingsRules(), $this->settingsMessages(), $this->settingsAttributes());

        if ($validator->fails()) {
            $this->dispatchScheduleAlert($validator->errors()->first() ?? 'Confira os valores informados.');

            return null;
        }

        return $payload;
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function settingsRules(): array
    {
        return [
            'welcome_duration_minutes' => ['nullable', 'integer', 'min:30', 'max:60'],
            'schedule_settings' => ['required', 'array'],
            'schedule_settings.after_lunch_pause_minutes' => ['required', 'integer', 'min:5', 'max:10'],
            'schedule_settings.devotional' => ['required', 'array'],
            'schedule_settings.devotional.after_welcome_enabled' => ['required', 'boolean'],
            'schedule_settings.devotional.after_breakfast_enabled' => ['required', 'boolean'],
            'schedule_settings.devotional.duration_minutes' => ['required', 'integer', 'min:5', 'max:180'],
            'schedule_settings.meals' => ['required', 'array'],
            'schedule_settings.meals.breakfast.enabled' => ['required', 'boolean'],
            'schedule_settings.meals.breakfast.duration_minutes' => ['required', 'integer', 'min:5', 'max:180'],
            'schedule_settings.meals.lunch.enabled' => ['required', 'boolean'],
            'schedule_settings.meals.lunch.duration_minutes' => ['required', 'integer', 'min:5', 'max:180'],
            'schedule_settings.meals.afternoon_snack.enabled' => ['required', 'boolean'],
            'schedule_settings.meals.afternoon_snack.duration_minutes' => ['required', 'integer', 'min:5', 'max:180'],
            'schedule_settings.meals.dinner.enabled' => ['required', 'boolean'],
            'schedule_settings.meals.dinner.duration_minutes' => ['required', 'integer', 'min:5', 'max:180'],
            'schedule_settings.meals.dinner.substitute_snack' => ['required', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function settingsMessages(): array
    {
        return [
            'welcome_duration_minutes.min' => 'A duração das boas-vindas deve ser de no mínimo 30 minutos.',
            'welcome_duration_minutes.max' => 'A duração das boas-vindas deve ser de no máximo 60 minutos.',
            'schedule_settings.after_lunch_pause_minutes.min' => 'A pausa após o almoço deve ter no mínimo 5 minutos.',
            'schedule_settings.after_lunch_pause_minutes.max' => 'A pausa após o almoço deve ter no máximo 10 minutos.',
            'schedule_settings.devotional.duration_minutes.min' => 'O devocional deve ter duração mínima de 5 minutos.',
            'schedule_settings.devotional.duration_minutes.max' => 'O devocional deve ter duração máxima de 180 minutos.',
            'schedule_settings.meals.*.duration_minutes.min' => 'A duração informada deve ser de ao menos 5 minutos.',
            'schedule_settings.meals.*.duration_minutes.max' => 'A duração informada deve ser de no máximo 180 minutos.',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function settingsAttributes(): array
    {
        return [
            'welcome_duration_minutes' => 'boas-vindas',
            'schedule_settings.after_lunch_pause_minutes' => 'pausa após o almoço',
        ];
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function updateItemRules(): array
    {
        return [
            'date' => ['required', 'date_format:Y-m-d'],
            'starts_at' => ['required', 'date_format:Y-m-d H:i:s'],
            'planned_duration_minutes' => ['required', 'integer', 'min:1', 'max:720'],
        ];
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function moveItemRules(): array
    {
        return [
            'date' => ['required', 'date_format:Y-m-d'],
            'starts_at' => ['required', 'date_format:Y-m-d H:i:s'],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function updateItemMessages(): array
    {
        return [
            'date.required' => 'A data é obrigatória.',
            'date.date_format' => 'A data deve estar no formato YYYY-MM-DD.',
            'starts_at.required' => 'O horário inicial é obrigatório.',
            'starts_at.date_format' => 'O horário inicial deve estar no formato YYYY-MM-DD HH:MM:SS.',
            'planned_duration_minutes.integer' => 'A duração deve ser um número inteiro.',
            'planned_duration_minutes.min' => 'A duração deve ser de ao menos 1 minuto.',
            'planned_duration_minutes.max' => 'A duração deve ser de no máximo 720 minutos.',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function updateItemAttributes(): array
    {
        return [
            'date' => 'data',
            'starts_at' => 'horário inicial',
            'planned_duration_minutes' => 'duração',
        ];
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function storeItemRules(): array
    {
        return [
            'date' => ['required', 'date_format:Y-m-d'],
            'starts_at' => ['required', 'date_format:Y-m-d H:i:s'],
            'planned_duration_minutes' => ['required', 'integer', 'min:1', 'max:720'],
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:50'],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function storeItemMessages(): array
    {
        return [
            'date.required' => 'A data é obrigatória.',
            'date.date_format' => 'A data deve estar no formato YYYY-MM-DD.',
            'starts_at.required' => 'O horário inicial é obrigatório.',
            'starts_at.date_format' => 'O horário inicial deve estar no formato YYYY-MM-DD HH:MM:SS.',
            'planned_duration_minutes.required' => 'A duração é obrigatória.',
            'planned_duration_minutes.integer' => 'A duração deve ser um número inteiro.',
            'planned_duration_minutes.min' => 'A duração deve ser de ao menos 1 minuto.',
            'planned_duration_minutes.max' => 'A duração deve ser de no máximo 720 minutos.',
            'title.required' => 'O título é obrigatório.',
            'title.max' => 'O título deve ter no máximo 255 caracteres.',
            'type.required' => 'O tipo é obrigatório.',
            'type.max' => 'O tipo deve ter no máximo 50 caracteres.',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function storeItemAttributes(): array
    {
        return [
            'date' => 'data',
            'starts_at' => 'horário inicial',
            'planned_duration_minutes' => 'duração',
            'title' => 'título',
            'type' => 'tipo',
        ];
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

    private function applyScheduleAdjustments(TrainingScheduleGenerator $generator): void
    {
        $shouldRegenerate = false;
        $shouldReload = false;

        if ($this->ensureSingleWelcome()) {
            $shouldRegenerate = true;
        }

        if ($this->syncIntervalDurations()) {
            $shouldRegenerate = true;
        }

        if ($this->syncDayMealAvailability()) {
            $shouldRegenerate = true;
        }

        if ($this->syncDevotionalAnchors()) {
            $shouldRegenerate = true;
        }

        if ($this->removeConsecutiveScheduleItems()) {
            $shouldReload = true;
        }

        if ($this->unlockFlexibleAnchors()) {
            $shouldReload = true;
        }

        if (! $shouldRegenerate) {
            if ($shouldReload) {
                $this->training->load([
                    'course',
                    'eventDates' => fn ($query) => $query->orderBy('date')->orderBy('start_time'),
                    'scheduleItems' => fn ($query) => $query->with('section')->orderBy('date')->orderBy('starts_at'),
                ]);

                $this->eventDates = $this->training->eventDates;
                $this->scheduleItems = $this->training->scheduleItems;
                $this->scheduleSettings = $this->resolveScheduleSettings($generator);
                $this->syncDurationInputs();
            }

            return;
        }

        $this->generateSchedule($generator, 'AUTO_ONLY');

        $this->training->load([
            'course',
            'eventDates' => fn ($query) => $query->orderBy('date')->orderBy('start_time'),
            'scheduleItems' => fn ($query) => $query->with('section')->orderBy('date')->orderBy('starts_at'),
        ]);

        $this->eventDates = $this->training->eventDates;
        $this->scheduleItems = $this->training->scheduleItems;
        $this->scheduleSettings = $this->resolveScheduleSettings($generator);
        $this->syncDurationInputs();

        if ($this->removeConsecutiveScheduleItems()) {
            $this->training->load([
                'course',
                'eventDates' => fn ($query) => $query->orderBy('date')->orderBy('start_time'),
                'scheduleItems' => fn ($query) => $query->with('section')->orderBy('date')->orderBy('starts_at'),
            ]);

            $this->eventDates = $this->training->eventDates;
            $this->scheduleItems = $this->training->scheduleItems;
            $this->syncDurationInputs();
        }
    }

    private function ensureSingleWelcome(): bool
    {
        $firstDate = $this->eventDates->first()?->date;

        if (! $firstDate) {
            return false;
        }

        $welcomeItems = $this->scheduleItems
            ->filter(fn (TrainingScheduleItem $item) => $item->type === 'WELCOME')
            ->filter(fn (TrainingScheduleItem $item) => $item->date?->format('Y-m-d') === $firstDate)
            ->sortBy('starts_at')
            ->values();

        if ($welcomeItems->isEmpty()) {
            return false;
        }

        $needsCleanup = $welcomeItems->count() > 1;

        if (! $needsCleanup) {
            return false;
        }

        $keep = $welcomeItems->first();
        $duration = (int) ($keep?->planned_duration_minutes ?? 30);
        $duration = max(30, min(60, $duration));

        if ($this->training->welcome_duration_minutes !== $duration) {
            $this->training->welcome_duration_minutes = $duration;
            $this->training->save();
        }

        $idsToDelete = $welcomeItems->pluck('id')->values()->all();

        if ($idsToDelete !== []) {
            TrainingScheduleItem::query()
                ->whereIn('id', $idsToDelete)
                ->delete();
        }

        return true;
    }

    private function syncDevotionalAnchors(): bool
    {
        $desired = [];

        foreach ($this->eventDates as $eventDate) {
            if (! $eventDate->start_time || ! $eventDate->end_time) {
                continue;
            }

            $dateKey = $eventDate->date;
            $daySettings = $this->scheduleSettings['days'][$dateKey] ?? [];
            $devotionalEnabled = (bool) ($daySettings['devotional_enabled'] ?? true);

            if (! $devotionalEnabled) {
                continue;
            }

            $devotionalMinutes = $this->normalizeDevotionalMinutes(
                (int) ($daySettings['devotional_duration_minutes'] ?? 30)
            );
            $dayStart = Carbon::parse($eventDate->date.' '.$eventDate->start_time);
            $dayEnd = Carbon::parse($eventDate->date.' '.$eventDate->end_time);

            if ($dayEnd->lte($dayStart)) {
                continue;
            }

            $welcomeEnabled = (bool) ($daySettings['welcome_enabled'] ?? false);
            $welcomeDuration = $this->normalizeWelcomeMinutes(
                (int) ($daySettings['welcome_duration_minutes'] ?? $this->resolveWelcomeDurationMinutes())
            );
            $welcomeEnd = $welcomeEnabled ? $dayStart->copy()->addMinutes($welcomeDuration) : null;
            $welcomeItemEnd = $this->resolveWelcomeEnd($dateKey, $dayStart, $dayEnd);
            $minimumStart = $welcomeEnd?->copy() ?? $dayStart->copy();
            $openingEnd = $this->resolveOpeningEnd($dateKey, $dayStart, $dayEnd);
            $breakfastEnd = $this->resolveBreakfastEnd(
                $dateKey,
                $dayStart,
                $dayEnd,
                $minimumStart,
                $daySettings['meals'] ?? [],
            );

            $start = $welcomeEnd?->copy() ?? $dayStart->copy();
            $start = $welcomeItemEnd && $welcomeItemEnd->gt($start) ? $welcomeItemEnd->copy() : $start;
            $start = $openingEnd && $openingEnd->gt($start) ? $openingEnd->copy() : $start;
            $start = $breakfastEnd && $breakfastEnd->gt($start) ? $breakfastEnd->copy() : $start;

            $this->addDesiredDevotional($desired, $dateKey, 'devotional_after_welcome', $start, $devotionalMinutes, $dayEnd);
        }

        $existing = $this->scheduleItems
            ->filter(function (TrainingScheduleItem $item): bool {
                if (strtoupper((string) $item->type) === 'DEVOTIONAL') {
                    return true;
                }

                $anchor = $item->meta['anchor'] ?? null;

                return $anchor && (
                    str_starts_with((string) $anchor, 'devotional_')
                    || (string) $anchor === 'devotional'
                );
            })
            ->groupBy(function (TrainingScheduleItem $item): string {
                $dateKey = $item->date?->format('Y-m-d') ?? '';
                $anchor = (string) ($item->meta['anchor'] ?? 'devotional');

                return $dateKey.'|'.$anchor;
            });

        $duplicates = $existing->flatMap(function (Collection $items): Collection {
            return $items->sortBy('starts_at')->slice(1)->pluck('id');
        })->values()->all();

        if ($duplicates !== []) {
            TrainingScheduleItem::query()
                ->whereIn('id', $duplicates)
                ->delete();
        }

        $existing = $existing->map(fn (Collection $items) => $items->sortBy('starts_at')->first());

        $needsUpdate = false;

        foreach ($desired as $key => $entry) {
            $existingItem = $existing->get($key);

            if (! $existingItem) {
                $this->training->scheduleItems()->create([
                    'section_id' => null,
                    'date' => $entry['date'],
                    'starts_at' => $entry['starts_at']->copy(),
                    'ends_at' => $entry['ends_at']->copy(),
                    'type' => 'DEVOTIONAL',
                    'title' => 'Devocional',
                    'planned_duration_minutes' => $entry['duration'],
                    'suggested_duration_minutes' => null,
                    'min_duration_minutes' => null,
                    'origin' => 'AUTO',
                    'is_locked' => false,
                    'status' => 'OK',
                    'conflict_reason' => null,
                    'meta' => ['anchor' => $entry['anchor']],
                ]);

                $needsUpdate = true;

                continue;
            }

            $startsAt = $existingItem->starts_at?->format('Y-m-d H:i:s');
            $desiredStarts = $entry['starts_at']->format('Y-m-d H:i:s');
            $desiredEnds = $entry['ends_at']->format('Y-m-d H:i:s');

            if (
                $startsAt !== $desiredStarts
                || $existingItem->ends_at?->format('Y-m-d H:i:s') !== $desiredEnds
                || (int) $existingItem->planned_duration_minutes !== $entry['duration']
                || ! $existingItem->is_locked
            ) {
                $existingItem->starts_at = $entry['starts_at']->copy();
                $existingItem->ends_at = $entry['ends_at']->copy();
                $existingItem->planned_duration_minutes = $entry['duration'];
                $existingItem->origin = 'AUTO';
                $existingItem->is_locked = false;
                $existingItem->type = 'DEVOTIONAL';
                $existingItem->title = 'Devocional';
                $existingItem->meta = ['anchor' => $entry['anchor']];
                $existingItem->save();

                $needsUpdate = true;
            }
        }

        $obsolete = $existing
            ->reject(fn ($item, string $key): bool => array_key_exists($key, $desired))
            ->pluck('id')
            ->values()
            ->all();

        if ($obsolete !== []) {
            TrainingScheduleItem::query()
                ->whereIn('id', $obsolete)
                ->delete();

            $needsUpdate = true;
        }

        if ($duplicates !== []) {
            $needsUpdate = true;
        }

        return $needsUpdate;
    }

    private function syncIntervalDurations(): bool
    {
        $settings = $this->training->schedule_settings;
        $settings = is_array($settings) ? $settings : [];
        $meals = $settings['meals'] ?? [];
        $normalizedMeals = $this->normalizeMealDurations($meals);

        if ($normalizedMeals === $meals) {
            return false;
        }

        $settings['meals'] = $normalizedMeals;
        $this->training->schedule_settings = $settings;
        $this->training->save();

        return true;
    }

    private function syncDayMealAvailability(): bool
    {
        $settings = $this->training->schedule_settings;
        $settings = is_array($settings) ? $settings : [];
        $days = $settings['days'] ?? [];
        $changed = false;

        foreach ($this->eventDates as $eventDate) {
            $dateKey = $eventDate->date;
            $storedDay = $days[$dateKey] ?? [];
            $storedDay = is_array($storedDay) ? $storedDay : [];

            $normalized = $this->resolveDaySettings($this->scheduleSettings, [
                'days' => [$dateKey => $storedDay],
            ]);

            if (! isset($normalized[$dateKey])) {
                continue;
            }

            $normalizedMeals = $normalized[$dateKey]['meals'] ?? [];

            if (($storedDay['meals'] ?? []) !== $normalizedMeals) {
                $days[$dateKey] = $storedDay;
                $days[$dateKey]['meals'] = $normalizedMeals;
                $changed = true;
            }
        }

        if (! $changed) {
            return false;
        }

        $settings['days'] = $days;
        $this->training->schedule_settings = $settings;
        $this->training->save();

        $this->scheduleSettings['days'] = $days;

        return true;
    }

    /**
     * @param  array<string, array{date: string, anchor: string, starts_at: Carbon, ends_at: Carbon, duration: int}>  $desired
     */
    private function addDesiredDevotional(
        array &$desired,
        string $dateKey,
        string $anchor,
        Carbon $start,
        int $duration,
        Carbon $dayEnd,
    ): void {
        $end = $start->copy()->addMinutes($duration);

        if ($end->gt($dayEnd)) {
            return;
        }

        $desired[$dateKey.'|'.$anchor] = [
            'date' => $dateKey,
            'anchor' => $anchor,
            'starts_at' => $start->copy(),
            'ends_at' => $end->copy(),
            'duration' => $duration,
        ];
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

    private function isIntervalType(string $type): bool
    {
        return in_array(strtoupper($type), ['BREAK', 'MEAL'], true);
    }

    private function normalizeIntervalDuration(string $type, int $duration): int
    {
        if (! $this->isIntervalType($type)) {
            return $duration;
        }

        return $this->normalizeIntervalMinutes($duration);
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

    private function updateDurationForDayAnchors(TrainingScheduleItem $item, int $duration): bool
    {
        $dateKey = $item->date?->format('Y-m-d');

        if (! $dateKey) {
            return false;
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
                return false;
            }

            $daySettings = $this->scheduleSettings['days'][$dateKey] ?? [];
            $daySettings['meals'][$mealKey]['enabled'] = true;
            $daySettings['meals'][$mealKey]['duration_minutes'] = $duration;

            if ($mealKey === 'dinner') {
                $daySettings['meals'][$mealKey]['substitute_snack'] = $anchor === 'night_snack';
            }

            $this->persistDaySettings($dateKey, $daySettings);

            return true;
        }

        if ($item->type === 'WELCOME') {
            $daySettings = $this->scheduleSettings['days'][$dateKey] ?? [];
            $daySettings['welcome_enabled'] = true;
            $daySettings['welcome_duration_minutes'] = $duration;
            $this->persistDaySettings($dateKey, $daySettings);

            return true;
        }

        if ($item->type === 'DEVOTIONAL') {
            $daySettings = $this->scheduleSettings['days'][$dateKey] ?? [];
            $daySettings['devotional_enabled'] = true;
            $daySettings['devotional_duration_minutes'] = $duration;
            $this->persistDaySettings($dateKey, $daySettings);

            return true;
        }

        return false;
    }

    private function removeConsecutiveScheduleItems(): bool
    {
        if ($this->scheduleItems->isEmpty()) {
            return false;
        }

        $toDelete = [];
        $toUpdate = [];

        $this->scheduleItems
            ->groupBy(fn (TrainingScheduleItem $item) => $item->date?->format('Y-m-d'))
            ->each(function (Collection $items) use (&$toDelete, &$toUpdate): void {
                $sorted = $items->sortBy('starts_at')->values();
                $previous = null;

                foreach ($sorted as $item) {
                    if (! $previous) {
                        $previous = $item;

                        continue;
                    }

                    if ($this->shouldMergeConsecutiveItems($previous, $item)) {
                        $previous->planned_duration_minutes += (int) $item->planned_duration_minutes;
                        $previous->ends_at = $item->ends_at?->copy() ?? $previous->ends_at?->copy();
                        $toUpdate[$previous->id] = $previous;
                        $toDelete[] = $item->id;

                        continue;
                    }

                    if ($this->shouldRemoveConsecutiveItem($previous, $item)) {
                        $toDelete[] = $item->id;

                        continue;
                    }

                    $previous = $item;
                }
            });

        if ($toDelete === []) {
            return false;
        }

        if ($toUpdate !== []) {
            foreach ($toUpdate as $item) {
                $item->save();
            }
        }

        TrainingScheduleItem::query()
            ->whereIn('id', $toDelete)
            ->delete();

        return true;
    }

    private function shouldRemoveConsecutiveItem(TrainingScheduleItem $previous, TrainingScheduleItem $current): bool
    {
        if ($this->isIntervalType((string) $previous->type) && $this->isIntervalType((string) $current->type)) {
            return true;
        }

        if (strtoupper((string) $previous->type) === 'DEVOTIONAL' && strtoupper((string) $current->type) === 'DEVOTIONAL') {
            return true;
        }

        if ($previous->section_id && $current->section_id && $previous->section_id === $current->section_id) {
            return true;
        }

        return false;
    }

    private function shouldMergeConsecutiveItems(TrainingScheduleItem $previous, TrainingScheduleItem $current): bool
    {
        if (! $previous->ends_at || ! $current->starts_at) {
            return false;
        }

        if (! $previous->ends_at->equalTo($current->starts_at)) {
            return false;
        }

        if (
            $previous->section_id
            && $current->section_id
            && $previous->section_id === $current->section_id
        ) {
            return true;
        }

        if (
            $this->isIntervalType((string) $previous->type)
            && $this->isIntervalType((string) $current->type)
            && strtoupper((string) $previous->type) === strtoupper((string) $current->type)
        ) {
            return true;
        }

        if (
            strtoupper((string) $previous->type) === 'DEVOTIONAL'
            && strtoupper((string) $current->type) === 'DEVOTIONAL'
        ) {
            return true;
        }

        return false;
    }

    private function resolveWelcomeDurationMinutes(): int
    {
        $duration = (int) ($this->scheduleSettings['welcome_duration_minutes'] ?? $this->training->welcome_duration_minutes ?? 30);

        if ($duration < 30) {
            return 30;
        }

        if ($duration > 60) {
            return 60;
        }

        return $duration;
    }

    private function resolveDevotionalStart(
        string $dateKey,
        Carbon $dayStart,
        Carbon $dayEnd,
        Carbon $baseline,
    ): Carbon {
        $daySettings = $this->scheduleSettings['days'][$dateKey] ?? [];
        $breakfastEnd = $this->resolveBreakfastEnd(
            $dateKey,
            $dayStart,
            $dayEnd,
            $baseline,
            $daySettings['meals'] ?? [],
        );

        if ($breakfastEnd && $breakfastEnd->gt($baseline)) {
            return $breakfastEnd->copy();
        }

        return $baseline->copy();
    }

    private function resolveBreakfastEnd(
        string $dateKey,
        Carbon $dayStart,
        Carbon $dayEnd,
        Carbon $minimumStart,
        array $meals,
    ): ?Carbon {
        if (! ($meals['breakfast']['enabled'] ?? true)) {
            return null;
        }

        $breakfastEndSlot = Carbon::parse($dateKey.' '.self::BREAKFAST_END);

        if ($breakfastEndSlot->lt($dayStart) || $breakfastEndSlot->gte($dayEnd)) {
            return null;
        }

        $start = Carbon::parse($dateKey.' '.self::BREAKFAST_START);
        $start = $start->lt($dayStart) ? $dayStart->copy() : $start;
        $start = $start->lt($minimumStart) ? $minimumStart->copy() : $start;

        $duration = (int) ($meals['breakfast']['duration_minutes'] ?? 30);
        $end = $start->copy()->addMinutes($duration);

        if ($end->gt($dayEnd)) {
            return null;
        }

        return $end;
    }

    private function resolveOpeningEnd(string $dateKey, Carbon $dayStart, Carbon $dayEnd): ?Carbon
    {
        $openingItems = $this->scheduleItems
            ->filter(fn (TrainingScheduleItem $item) => $item->type === 'OPENING')
            ->filter(fn (TrainingScheduleItem $item) => $item->date?->format('Y-m-d') === $dateKey)
            ->filter(fn (TrainingScheduleItem $item) => $item->starts_at && $item->ends_at)
            ->filter(fn (TrainingScheduleItem $item) => $item->starts_at->gte($dayStart) && $item->ends_at->lte($dayEnd))
            ->sortBy('ends_at')
            ->values();

        if ($openingItems->isEmpty()) {
            return null;
        }

        return $openingItems->last()?->ends_at?->copy();
    }

    private function resolveWelcomeEnd(string $dateKey, Carbon $dayStart, Carbon $dayEnd): ?Carbon
    {
        $welcomeItems = $this->scheduleItems
            ->filter(fn (TrainingScheduleItem $item) => $item->type === 'WELCOME')
            ->filter(fn (TrainingScheduleItem $item) => $item->date?->format('Y-m-d') === $dateKey)
            ->filter(fn (TrainingScheduleItem $item) => $item->starts_at && $item->ends_at)
            ->filter(fn (TrainingScheduleItem $item) => $item->starts_at->gte($dayStart) && $item->ends_at->lte($dayEnd))
            ->sortBy('ends_at')
            ->values();

        if ($welcomeItems->isEmpty()) {
            return null;
        }

        $endsAt = $welcomeItems->last()?->ends_at;

        return $endsAt ? Carbon::instance($endsAt) : null;
    }

    private function generateSchedule(TrainingScheduleGenerator $generator, string $mode): void
    {
        $this->lockFlexibleAnchors();
        $generator->generate($this->training, $mode);
        $this->unlockFlexibleAnchors();
    }

    private function lockFlexibleAnchors(): void
    {
        TrainingScheduleItem::query()
            ->where('training_id', $this->training->id)
            ->where('type', 'DEVOTIONAL')
            ->update(['is_locked' => true]);
    }

    private function unlockFlexibleAnchors(): bool
    {
        $affected = TrainingScheduleItem::query()
            ->where('training_id', $this->training->id)
            ->whereIn('type', ['WELCOME', 'DEVOTIONAL'])
            ->where('is_locked', true)
            ->update(['is_locked' => false]);

        return $affected > 0;
    }
}
