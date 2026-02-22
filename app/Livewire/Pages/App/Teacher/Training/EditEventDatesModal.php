<?php

namespace App\Livewire\Pages\App\Teacher\Training;

use App\Models\Training;
use App\Services\Schedule\TrainingScheduleResetService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class EditEventDatesModal extends Component
{
    public Training $training;

    public int $trainingId;

    public bool $showModal = false;

    public bool $busy = false;

    /**
     * @var array<int, array{date: string, start_time: string, end_time: string}>
     */
    public array $eventDates = [['date' => '', 'start_time' => '', 'end_time' => '']];

    public function mount(int $trainingId): void
    {
        $this->trainingId = $trainingId;
        $this->loadTraining();
        $this->fillEventDatesFromTraining();
    }

    #[On('open-edit-event-dates-modal')]
    public function openModal(int $trainingId): void
    {
        if ($trainingId !== $this->trainingId) {
            abort(404);
        }

        $this->loadTraining();
        $this->fillEventDatesFromTraining();
        $this->resetValidation();
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetValidation();
    }

    public function addEventDate(): void
    {
        $this->eventDates[] = ['date' => '', 'start_time' => '', 'end_time' => ''];
    }

    public function removeEventDate(int $index): void
    {
        if (count($this->eventDates) === 1) {
            return;
        }

        unset($this->eventDates[$index]);
        $this->eventDates = array_values($this->eventDates);
    }

    public function save(TrainingScheduleResetService $resetService): void
    {
        if ($this->busy) {
            return;
        }

        $this->authorizeTraining($this->training);
        $validated = $this->validate();

        foreach ($validated['eventDates'] as $index => $eventDate) {
            if (($eventDate['end_time'] ?? '') <= ($eventDate['start_time'] ?? '')) {
                $this->addError("eventDates.{$index}.end_time", 'Fim deve ser após o início');

                return;
            }
        }

        $this->busy = true;

        try {
            $rows = collect($validated['eventDates'])
                ->sortBy(fn (array $eventDate): string => $eventDate['date'].' '.$eventDate['start_time'])
                ->values()
                ->map(function (array $eventDate): array {
                    return [
                        'date' => $eventDate['date'],
                        'start_time' => $eventDate['start_time'].':00',
                        'end_time' => $eventDate['end_time'].':00',
                    ];
                })
                ->all();

            if (! $this->hasEventDateChanges($rows)) {
                $this->closeModal();
                $this->dispatch('training-dates-updated', trainingId: $this->training->id);

                return;
            }

            DB::transaction(function () use ($rows): void {
                $this->training->eventDates()->delete();
                $this->training->eventDates()->createMany($rows);
            });

            $resetService->resetFull($this->training->id);

            $this->loadTraining();
            $this->fillEventDatesFromTraining();
            $this->closeModal();
            $this->dispatch('training-dates-updated', trainingId: $this->training->id);
        } finally {
            $this->busy = false;
        }
    }

    public function render(): View
    {
        return view('livewire.pages.app.teacher.training.edit-event-dates-modal');
    }

    /**
     * @return array<string, array<int, string>>
     */
    protected function rules(): array
    {
        return [
            'eventDates' => ['required', 'array', 'min:1'],
            'eventDates.*.date' => ['required', 'date_format:Y-m-d', 'distinct'],
            'eventDates.*.start_time' => ['required', 'date_format:H:i'],
            'eventDates.*.end_time' => ['required', 'date_format:H:i', 'after:eventDates.*.start_time'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'eventDates.required' => 'Adicione ao menos um dia.',
            'eventDates.min' => 'Adicione ao menos um dia.',
            'eventDates.*.date.required' => 'Informe a data.',
            'eventDates.*.date.date_format' => 'A data deve estar no formato YYYY-MM-DD.',
            'eventDates.*.date.distinct' => 'As datas não podem se repetir.',
            'eventDates.*.start_time.required' => 'Informe o horário inicial.',
            'eventDates.*.start_time.date_format' => 'O horário inicial deve estar no formato HH:MM.',
            'eventDates.*.end_time.required' => 'Informe o horário final.',
            'eventDates.*.end_time.date_format' => 'O horário final deve estar no formato HH:MM.',
            'eventDates.*.end_time.after' => 'O horário final deve ser maior que o horário inicial.',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            'eventDates' => 'datas do treinamento',
            'eventDates.*.date' => 'data',
            'eventDates.*.start_time' => 'horário inicial',
            'eventDates.*.end_time' => 'horário final',
        ];
    }

    private function loadTraining(): void
    {
        $this->training = Training::query()
            ->with(['eventDates' => fn ($query) => $query->orderBy('date')->orderBy('start_time')])
            ->findOrFail($this->trainingId);

        $this->authorizeTraining($this->training);
    }

    private function fillEventDatesFromTraining(): void
    {
        $rows = $this->training->eventDates
            ->map(function ($eventDate): array {
                return [
                    'date' => is_string($eventDate->date) ? $eventDate->date : $eventDate->date?->format('Y-m-d') ?? '',
                    'start_time' => substr((string) ($eventDate->start_time ?? ''), 0, 5),
                    'end_time' => substr((string) ($eventDate->end_time ?? ''), 0, 5),
                ];
            })
            ->values()
            ->all();

        $this->eventDates = $rows !== []
            ? $rows
            : [['date' => '', 'start_time' => '', 'end_time' => '']];
    }

    private function authorizeTraining(Training $training): void
    {
        Gate::authorize('access-teacher');

        if (Auth::id() !== $training->teacher_id) {
            abort(403);
        }
    }

    /**
     * @param  array<int, array{date: string, start_time: string, end_time: string}>  $newRows
     */
    private function hasEventDateChanges(array $newRows): bool
    {
        $currentRows = $this->training->eventDates()
            ->orderBy('date')
            ->orderBy('start_time')
            ->get(['date', 'start_time', 'end_time'])
            ->map(function ($eventDate): array {
                return [
                    'date' => is_string($eventDate->date) ? $eventDate->date : $eventDate->date?->format('Y-m-d') ?? '',
                    'start_time' => (string) $eventDate->start_time,
                    'end_time' => (string) $eventDate->end_time,
                ];
            })
            ->values()
            ->all();

        return $currentRows !== $newRows;
    }
}
