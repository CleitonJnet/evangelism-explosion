<?php

namespace App\Livewire\Pages\App\Teacher\Training\Ojt;

use App\Models\OjtReport;
use App\Models\Training;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Component;

class Statistics extends Component
{
    public Training $training;

    public int $gospelPresentations = 0;

    public int $listenersCount = 0;

    public int $resultsDecisions = 0;

    public int $resultsInterested = 0;

    public int $resultsRejection = 0;

    public int $resultsAssurance = 0;

    /**
     * @var Collection<int, array<string, mixed>>
     */
    public Collection $publicReports;

    public string $mode = 'summary';

    public function mount(Training $training, string $mode = 'summary'): void
    {
        $this->training = $training->load('course');
        $this->mode = $mode;

        $reports = OjtReport::query()
            ->whereHas('team.session', fn ($query) => $query->where('training_id', $training->id))
            ->get();

        $this->gospelPresentations = (int) $reports->sum('gospel_presentations');
        $this->listenersCount = (int) $reports->sum('listeners_count');
        $this->resultsDecisions = (int) $reports->sum('results_decisions');
        $this->resultsInterested = (int) $reports->sum('results_interested');
        $this->resultsRejection = (int) $reports->sum('results_rejection');
        $this->resultsAssurance = (int) $reports->sum('results_assurance');
        $this->publicReports = $reports
            ->pluck('public_report')
            ->filter()
            ->values();
    }

    public function render(): View
    {
        return view('livewire.pages.app.teacher.training.ojt.statistics');
    }
}
