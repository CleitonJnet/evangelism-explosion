<?php

namespace App\Livewire\Pages\App\Teacher\Training\Ojt;

use App\Models\OjtSession;
use App\Models\Training;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Component;

class Teams extends Component
{
    public Training $training;

    /**
     * @var Collection<int, OjtSession>
     */
    public Collection $sessions;

    public function mount(Training $training): void
    {
        $this->training = $training->load('course');

        $this->sessions = $this->training->ojtSessions()
            ->with(['teams.mentor', 'teams.trainees.trainee'])
            ->orderBy('date')
            ->orderBy('starts_at')
            ->get();
    }

    public function render(): View
    {
        return view('livewire.pages.app.teacher.training.ojt.teams');
    }
}
