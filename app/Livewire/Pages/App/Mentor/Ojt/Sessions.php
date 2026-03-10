<?php

namespace App\Livewire\Pages\App\Mentor\Ojt;

use App\Services\Training\MentorTrainingOverviewService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class Sessions extends Component
{
    public function render(): View
    {
        $mentor = Auth::user();

        abort_unless($mentor?->can('access-mentor'), 403);

        return view('livewire.pages.app.mentor.ojt.sessions', [
            'sessions' => app(MentorTrainingOverviewService::class)->mentorSessions($mentor),
        ]);
    }
}
