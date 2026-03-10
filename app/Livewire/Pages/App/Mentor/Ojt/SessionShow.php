<?php

namespace App\Livewire\Pages\App\Mentor\Ojt;

use App\Models\StpSession;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;
use Livewire\Component;

class SessionShow extends Component
{
    use AuthorizesRequests;

    public StpSession $session;

    public function mount(StpSession $session): void
    {
        $this->authorize('view', $session);

        $this->session = $session->load([
            'training.course.ministry',
            'training.teacher',
            'training.church',
            'teams' => fn ($query) => $query
                ->where('mentor_user_id', auth()->id())
                ->with(['mentor', 'students', 'approaches']),
        ]);
    }

    public function render(): View
    {
        return view('livewire.pages.app.mentor.ojt.session-show');
    }
}
