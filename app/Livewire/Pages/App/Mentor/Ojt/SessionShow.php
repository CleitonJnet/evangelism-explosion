<?php

namespace App\Livewire\Pages\App\Mentor\Ojt;

use App\Models\OjtSession;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;
use Livewire\Component;

class SessionShow extends Component
{
    use AuthorizesRequests;

    public OjtSession $session;

    public function mount(OjtSession $session): void
    {
        $this->authorize('view', $session);

        $this->session = $session->load(['training', 'teams.mentor', 'teams.trainees.trainee']);
    }

    public function render(): View
    {
        return view('livewire.pages.app.mentor.ojt.session-show');
    }
}
