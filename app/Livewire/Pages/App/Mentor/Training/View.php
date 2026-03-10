<?php

namespace App\Livewire\Pages\App\Mentor\Training;

use App\Models\Training;
use App\Services\Training\MentorTrainingOverviewService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View as ViewResponse;
use Livewire\Component;

class View extends Component
{
    use AuthorizesRequests;

    public Training $training;

    public array $summary = [];

    public function mount(Training $training): void
    {
        $this->authorize('view', $training);

        $mentor = Auth::user();

        abort_unless($mentor !== null, 401);

        $this->training = $training->load([
            'course.ministry',
            'teacher',
            'church',
            'eventDates' => fn ($query) => $query->orderBy('date')->orderBy('start_time'),
        ]);
        $this->summary = app(MentorTrainingOverviewService::class)->trainingSummary($mentor, $training);
    }

    public function render(): ViewResponse
    {
        return view('livewire.pages.app.mentor.training.view');
    }
}
