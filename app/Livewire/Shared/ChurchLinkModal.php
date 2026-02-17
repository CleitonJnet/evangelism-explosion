<?php

namespace App\Livewire\Shared;

use App\Models\Church;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class ChurchLinkModal extends Component
{
    public bool $showChurchModal = false;

    public string $churchSearch = '';

    public ?int $selectedChurchId = null;

    public function mount(): void
    {
        $user = Auth::user();

        if (! $user) {
            return;
        }

        if ($this->shouldPromptForChurch($user) && session()->get('church_modal_open')) {
            $this->prepareChurchSelection($user);

            session()->forget('church_modal_open');
            session()->put('church_modal_prompted', true);
        }
    }

    #[On('open-church-modal')]
    public function openFromEvent(): void
    {
        $user = Auth::user();

        if (! $user) {
            return;
        }

        $this->prepareChurchSelection($user);
    }

    #[Computed]
    public function churches(): Collection
    {
        return Church::query()
            ->when(
                $this->churchSearch !== '',
                fn ($query) => $query
                    ->where('name', 'like', "%{$this->churchSearch}%")
                    ->orWhere('city', 'like', "%{$this->churchSearch}%")
                    ->orWhere('state', 'like', "%{$this->churchSearch}%"),
            )
            ->orderBy('name')
            ->limit(20)
            ->get();
    }

    public function updatedChurchSearch(): void
    {
        $this->selectedChurchId = $this->churches->first()?->id;
    }

    public function saveChurchSelection(): void
    {
        $this->validate([
            'selectedChurchId' => ['required', 'exists:churches,id'],
        ]);

        $user = Auth::user();

        if (! $user) {
            $this->addError('selectedChurchId', 'Nao foi possivel vincular a igreja.');

            return;
        }

        $user->forceFill([
            'church_id' => $this->selectedChurchId,
            'church_temp_id' => null,
        ])->save();

        session()->forget(['church_modal_open', 'church_modal_prompted']);

        $this->resetChurchSelection();
        $this->dispatch('church-linked');
    }

    public function openCreateChurchTempModal(): void
    {
        $this->dispatch('open-create-church-temp-modal');
    }

    #[On('church-temp-linked')]
    public function handleChurchTempLinked(): void
    {
        session()->forget(['church_modal_open', 'church_modal_prompted']);

        $this->resetChurchSelection();
        $this->dispatch('church-linked');
    }

    public function closeChurchModal(): void
    {
        $this->resetChurchSelection();
    }

    private function shouldPromptForChurch(User $user): bool
    {
        return $user->church_id === null && $user->church_temp_id === null;
    }

    private function prepareChurchSelection(?User $user = null): void
    {
        $this->showChurchModal = true;
        $this->selectedChurchId = $user?->church_id ?? $this->churches->first()?->id;
    }

    private function resetChurchSelection(): void
    {
        $this->showChurchModal = false;
        $this->churchSearch = '';
        $this->selectedChurchId = null;
    }

    public function render(): View
    {
        return view('livewire.shared.church-link-modal');
    }
}
