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
     * @var Collection<int, \App\Models\User>
     */
    public Collection $students;

    public function mount(Training $training): void
    {
        $this->training = $training->load([
            'course.ministry',
            'teacher',
            'church',
            'eventDates' => fn ($query) => $query->orderBy('date')->orderBy('start_time'),
            'students' => fn ($query) => $query->orderBy('name'),
        ])->loadCount('scheduleItems');

        $this->eventDates = $this->training->eventDates;
        $this->students = $this->training->students;
    }

    public function render(): ViewResponse
    {
        return view('livewire.pages.app.teacher.training.view');
    }
}
