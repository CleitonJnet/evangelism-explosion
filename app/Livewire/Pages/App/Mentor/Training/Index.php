<?php

namespace App\Livewire\Pages\App\Mentor\Training;

use App\Services\Training\MentorTrainingOverviewService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class Index extends Component
{
    public function render(): View
    {
        $mentor = Auth::user();

        abort_unless($mentor?->can('access-mentor'), 403);

        return view('livewire.pages.app.mentor.training.index', [
            'trainings' => app(MentorTrainingOverviewService::class)->mentorTrainings($mentor),
        ]);
    }
}
