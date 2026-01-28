<?php

namespace App\Livewire\Pages\App\Teacher\Training\Ojt;

use App\Models\OjtReport;
use App\Models\Training;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Component;

class Reports extends Component
{
    public Training $training;

    /**
     * @var Collection<int, OjtReport>
     */
    public Collection $reports;

    public function mount(Training $training): void
    {
        $this->training = $training->load('course');

        $this->reports = OjtReport::query()
            ->whereHas('team.session', fn ($query) => $query->where('training_id', $training->id))
            ->with(['team.session', 'team.mentor'])
            ->orderByDesc('submitted_at')
            ->get();
    }

    public function render(): View
    {
        return view('livewire.pages.app.teacher.training.ojt.reports');
    }
}
