<?php

namespace App\Livewire\Pages\App\Director\Training;

use App\Models\Training;
use App\TrainingStatus;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Component;

class Index extends Component
{
    public function render(): View
    {
        $statuses = TrainingStatus::cases();
        $statusLabels = TrainingStatus::labels();

        $trainings = Training::query()
            ->with(['teacher', 'church', 'eventDates', 'course'])
            ->whereHas('course', fn ($query) => $query->where('execution', 0))
            ->get();

        return view('livewire.pages.app.director.training.index', [
            'statusLabels' => $statusLabels,
            'trainingsByStatus' => $this->groupTrainingsByStatus($trainings, $statuses),
        ]);
    }

    /**
     * @param  Collection<int, Training>  $trainings
     * @param  array<int, TrainingStatus>  $statuses
     * @return array<int, Collection<int, array{training: Training, dates: Collection<int, \App\Models\EventDate>}>>
     */
    private function groupTrainingsByStatus(Collection $trainings, array $statuses): array
    {
        $grouped = [];

        foreach ($statuses as $status) {
            $grouped[$status->value] = $trainings
                ->filter(fn (Training $training) => $training->status === $status)
                ->map(function (Training $training) {
                    $dates = $training->eventDates
                        ->sortBy(fn ($eventDate) => sprintf(
                            '%s %s',
                            $eventDate->date,
                            $eventDate->start_time ?? '00:00:00'
                        ))
                        ->values();

                    return [
                        'training' => $training,
                        'dates' => $dates,
                    ];
                })
                ->sortBy(function (array $item) {
                    $firstDate = $item['dates']->first();

                    if (! $firstDate) {
                        return '9999-12-31 23:59:59';
                    }

                    return sprintf(
                        '%s %s',
                        $firstDate->date,
                        $firstDate->start_time ?? '00:00:00'
                    );
                })
                ->values();
        }

        return $grouped;
    }
}
