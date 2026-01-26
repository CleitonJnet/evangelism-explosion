<?php

namespace App\Livewire\Pages\App\Teacher\Training;

use App\Models\Training;
use Illuminate\Support\Collection;
use Illuminate\View\View as ViewResponse;
use Livewire\Component;

class View extends Component
{
    public Training $training;

    /**
     * @var Collection<int, \App\Models\EventDate>
     */
    public Collection $eventDates;

    /**
     * @var Collection<int, \App\Models\TrainingScheduleItem>
     */
    public Collection $scheduleItems;

    public function mount(Training $training): void
    {
        $this->training = $training->load([
            'course',
            'eventDates' => fn ($query) => $query->orderBy('date')->orderBy('start_time'),
            'scheduleItems' => fn ($query) => $query->with('section')->orderBy('date')->orderBy('starts_at'),
        ]);

        $this->eventDates = $this->training->eventDates;
        $this->scheduleItems = $this->training->scheduleItems;
    }

    public function render(): ViewResponse
    {
        return view('livewire.pages.app.teacher.training.view', [
            'scheduleByDate' => $this->scheduleItems->groupBy(
                fn ($item) => $item->date?->format('Y-m-d')
            ),
        ]);
    }
}
