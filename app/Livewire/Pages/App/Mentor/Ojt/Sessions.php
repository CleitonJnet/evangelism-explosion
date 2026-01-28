<?php

namespace App\Livewire\Pages\App\Mentor\Ojt;

use App\Models\OjtSession;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class Sessions extends Component
{
    /**
     * @var Collection<int, OjtSession>
     */
    public Collection $sessions;

    public function mount(): void
    {
        $userId = Auth::id();

        if (! $userId) {
            abort(401);
        }

        $this->sessions = OjtSession::query()
            ->whereHas('teams', fn ($query) => $query->where('mentor_id', $userId))
            ->with(['training.course', 'teams' => fn ($query) => $query->where('mentor_id', $userId)])
            ->orderBy('date')
            ->orderBy('starts_at')
            ->get();
    }

    public function render(): View
    {
        return view('livewire.pages.app.mentor.ojt.sessions');
    }
}
