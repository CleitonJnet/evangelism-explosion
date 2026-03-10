<?php

namespace App\Livewire\Pages\App\Mentor;

use App\Services\Training\MentorTrainingOverviewService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class Dashboard extends Component
{
    public function render(): View
    {
        $mentor = Auth::user();

        abort_unless($mentor?->can('access-mentor'), 403);

        return view('livewire.pages.app.mentor.dashboard', [
            'trainings' => app(MentorTrainingOverviewService::class)->mentorTrainings($mentor)->take(6),
            'dashboard' => app(MentorTrainingOverviewService::class)->dashboardData($mentor),
        ]);
    }
}
