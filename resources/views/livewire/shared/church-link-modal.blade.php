<?php

use App\Models\Church;
use App\Models\ChurchTemp;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component
{
    protected $listeners = [
        'open-church-modal' => 'openFromEvent',
    ];

    public bool $showChurchModal = false;

    public bool $showChurchTempForm = false;

    public string $churchSearch = '';

    public ?int $selectedChurchId = null;

    public string $churchTempName = '';

    public string $churchTempPastor = '';

    public string $churchTempEmail = '';

    public string $churchTempPhone = '';

    public string $churchTempStreet = '';

    public string $churchTempNumber = '';

    public string $churchTempComplement = '';

    public string $churchTempDistrict = '';

    public string $churchTempCity = '';

    public string $churchTempState = '';

    public string $churchTempPostalCode = '';

    public string $churchTempContact = '';

    public string $churchTempContactPhone = '';

    public string $churchTempContactEmail = '';

    public string $churchTempNotes = '';

    public function mount(): void
    {
        $user = Auth::user();

        if (! $user) {
            return;
        }

        if ($this->shouldPromptForChurch($user) && session()->get('church_modal_open')) {
            $this->prepareChurchSelection($user);

            session()->forget('church_modal_open');
            session()->put('church_modal_seen', true);
        }
    }

    public function openFromEvent(): void
    {
        $user = Auth::user();

        if (! $user) {
            return;
        }

        $this->prepareChurchSelection($user);
    }

    /**
     * @return Collection<int, Church>
     */
    public function getChurchesProperty(): Collection
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
        $this->validate($this->churchSelectionRules());

        $user = Auth::user();

        if (! $user) {
            $this->addError('selectedChurchId', 'Nao foi possivel vincular a igreja.');

            return;
        }

        $user->forceFill([
            'church_id' => $this->selectedChurchId,
            'church_temp_id' => null,
        ])->save();

        $this->resetChurchSelection();
        session()->forget(['church_modal_open', 'church_modal_seen']);
        $this->dispatch('church-linked');
    }

    public function startChurchTempRegistration(): void
    {
        $this->showChurchTempForm = true;
    }

    public function backToChurchSearch(): void
    {
        $this->showChurchTempForm = false;
    }

    public function saveChurchTemp(): void
    {
        $validated = $this->validate($this->churchTempRules());

        $user = Auth::user();

        if (! $user) {
            $this->addError('churchTempName', 'Nao foi possivel salvar a igreja.');

            return;
        }

        $churchTemp = ChurchTemp::create([
            'name' => $validated['churchTempName'],
            'pastor' => $validated['churchTempPastor'],
            'email' => $validated['churchTempEmail'],
            'phone' => $validated['churchTempPhone'],
            'street' => $validated['churchTempStreet'],
            'number' => $validated['churchTempNumber'],
            'complement' => $validated['churchTempComplement'],
            'district' => $validated['churchTempDistrict'],
            'city' => $validated['churchTempCity'],
            'state' => $validated['churchTempState'],
            'postal_code' => $validated['churchTempPostalCode'],
            'contact' => $validated['churchTempContact'],
            'contact_phone' => $validated['churchTempContactPhone'],
            'contact_email' => $validated['churchTempContactEmail'],
            'notes' => $validated['churchTempNotes'],
        ]);

        $user->forceFill([
            'church_id' => null,
            'church_temp_id' => $churchTemp->id,
        ])->save();

        $this->resetChurchSelection();
        session()->forget(['church_modal_open', 'church_modal_seen']);
        $this->dispatch('church-linked');
    }

    public function closeChurchModal(): void
    {
        $this->resetChurchSelection();
    }

    /**
     * @return array<string, array<int, string>>
     */
    protected function churchSelectionRules(): array
    {
        return [
            'selectedChurchId' => ['required', 'exists:churches,id'],
        ];
    }

    /**
     * @return array<string, array<int, string>>
     */
    protected function churchTempRules(): array
    {
        return [
            'churchTempName' => ['required', 'string', 'min:3', 'max:255'],
            'churchTempPastor' => ['nullable', 'string', 'max:255'],
            'churchTempEmail' => ['nullable', 'email', 'max:255'],
            'churchTempPhone' => ['nullable', 'string', 'max:20'],
            'churchTempStreet' => ['nullable', 'string', 'max:255'],
            'churchTempNumber' => ['nullable', 'string', 'max:50'],
            'churchTempComplement' => ['nullable', 'string', 'max:255'],
            'churchTempDistrict' => ['nullable', 'string', 'max:255'],
            'churchTempCity' => ['required', 'string', 'max:255'],
            'churchTempState' => ['required', 'string', 'max:2'],
            'churchTempPostalCode' => ['nullable', 'string', 'max:20'],
            'churchTempContact' => ['nullable', 'string', 'max:255'],
            'churchTempContactPhone' => ['nullable', 'string', 'max:20'],
            'churchTempContactEmail' => ['nullable', 'email', 'max:255'],
            'churchTempNotes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    private function shouldPromptForChurch(User $user): bool
    {
        return $user->church_id === null && $user->church_temp_id === null;
    }

    private function prepareChurchSelection(?User $user = null): void
    {
        $this->showChurchModal = true;
        $this->showChurchTempForm = false;
        $this->selectedChurchId = $user?->church_id ?? $this->churches->first()?->id;
    }

    private function resetChurchSelection(): void
    {
        $this->showChurchModal = false;
        $this->showChurchTempForm = false;
        $this->churchSearch = '';
        $this->selectedChurchId = null;
    }
};
?>

<div>
    @include('livewire.web.event.partials.church-modal')
</div>
