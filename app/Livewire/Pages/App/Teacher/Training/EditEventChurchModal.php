<?php

namespace App\Livewire\Pages\App\Teacher\Training;

use App\Models\Church;
use App\Models\Training;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class EditEventChurchModal extends Component
{
    public Training $training;

    public int $trainingId;

    public bool $showModal = false;

    public bool $busy = false;

    public ?int $church_id = null;

    public string $churchSearch = '';

    public bool $preserveNewChurchSelection = false;

    /**
     * @var array{id: int|null, name: string}
     */
    public array $newChurchSelection = [
        'id' => null,
        'name' => '',
    ];

    public ?string $leader = null;

    public ?string $coordinator = null;

    /**
     * @var array{postal_code: string, street: string, number: string, complement: string, district: string, city: string, state: string}
     */
    public array $address = [
        'postal_code' => '',
        'street' => '',
        'number' => '',
        'complement' => '',
        'district' => '',
        'city' => '',
        'state' => '',
    ];

    public function mount(int $trainingId): void
    {
        $this->trainingId = $trainingId;
        $this->loadTraining();
        $this->fillFromTraining();
    }

    #[On('open-edit-event-church-modal')]
    public function openModal(int $trainingId): void
    {
        if ($trainingId !== $this->trainingId) {
            abort(404);
        }

        $this->loadTraining();
        $this->fillFromTraining();
        $this->resetValidation();
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetValidation();
    }

    public function updatedChurchId(?string $value): void
    {
        if (! $value) {
            return;
        }

        $this->applySelectedChurchData((int) $value);
    }

    public function selectChurch(int $churchId): void
    {
        $this->preserveNewChurchSelection = false;
        $this->church_id = $churchId;
        $this->applySelectedChurchData($churchId);
    }

    public function updatedChurchSearch(string $value): void
    {
        $selectedNewChurchId = isset($this->newChurchSelection['id']) ? (int) $this->newChurchSelection['id'] : null;
        $selectedNewChurchName = trim((string) ($this->newChurchSelection['name'] ?? ''));

        if (
            $this->preserveNewChurchSelection
            && $selectedNewChurchId
            && $this->church_id === $selectedNewChurchId
            && trim($value) === $selectedNewChurchName
        ) {
            return;
        }

        $this->preserveNewChurchSelection = false;
    }

    /**
     * @param  array{id?: int|string|null, name?: string|null}  $value
     */
    public function updatedNewChurchSelection(array $value): void
    {
        $churchId = isset($value['id']) ? (int) $value['id'] : null;
        $churchName = trim((string) ($value['name'] ?? ''));

        if (! $churchId || $churchName === '') {
            return;
        }

        if (! Church::query()->whereKey($churchId)->exists()) {
            return;
        }

        $this->preserveNewChurchSelection = true;
        $this->churchSearch = $churchName;
        $this->church_id = $churchId;
        $this->applySelectedChurchData($churchId);
    }

    #[On('church-created')]
    public function handleChurchCreated(int $churchId, string $churchName): void
    {
        $this->updatedNewChurchSelection([
            'id' => $churchId,
            'name' => $churchName,
        ]);
    }

    public function save(): void
    {
        if ($this->busy) {
            return;
        }

        $this->authorizeTraining($this->training);
        $validated = $this->validate();
        $this->busy = true;

        try {
            $this->training->update([
                'church_id' => $validated['church_id'],
                'leader' => trim((string) $validated['leader']),
                'coordinator' => trim((string) $validated['coordinator']),
                'street' => $validated['address']['street'] ?: null,
                'number' => $validated['address']['number'] ?: null,
                'complement' => $validated['address']['complement'] ?: null,
                'district' => $validated['address']['district'] ?: null,
                'city' => $validated['address']['city'] ?: null,
                'state' => strtoupper((string) ($validated['address']['state'] ?? '')),
                'postal_code' => $validated['address']['postal_code'] ?: null,
            ]);

            $this->loadTraining();
            $this->fillFromTraining();
            $this->closeModal();
            $this->dispatch('training-church-updated', trainingId: $this->training->id);
        } finally {
            $this->busy = false;
        }
    }

    public function render(): View
    {
        return view('livewire.pages.app.teacher.training.edit-event-church-modal', [
            'churches' => $this->loadChurches(),
        ]);
    }

    /**
     * @return array<string, array<int, string>>
     */
    protected function rules(): array
    {
        return [
            'church_id' => ['required', 'integer', 'exists:churches,id'],
            'leader' => ['required', 'string', 'max:255'],
            'coordinator' => ['required', 'string', 'max:255'],
            'address.postal_code' => ['nullable', 'string', 'max:20'],
            'address.street' => ['nullable', 'string', 'max:255'],
            'address.number' => ['nullable', 'string', 'max:50'],
            'address.complement' => ['nullable', 'string', 'max:255'],
            'address.district' => ['nullable', 'string', 'max:255'],
            'address.city' => ['nullable', 'string', 'max:255'],
            'address.state' => ['nullable', 'string', 'max:2'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            'church_id' => 'igreja sede',
            'leader' => 'líder do evento',
            'coordinator' => 'coordenador do evento',
            'address.postal_code' => 'CEP',
            'address.street' => 'logradouro',
            'address.number' => 'número',
            'address.complement' => 'complemento',
            'address.district' => 'bairro',
            'address.city' => 'cidade',
            'address.state' => 'UF',
        ];
    }

    private function fillFromTraining(): void
    {
        $this->training->loadMissing('church');

        $church = $this->training->church;
        $churchName = (string) ($church?->name ?? '');

        $this->church_id = $this->training->church_id;
        $this->churchSearch = $churchName;
        $this->newChurchSelection = [
            'id' => $church?->id,
            'name' => $churchName,
        ];
        $this->preserveNewChurchSelection = false;

        $this->address = [
            'postal_code' => (string) ($this->training->postal_code ?? ''),
            'street' => (string) ($this->training->street ?? ''),
            'number' => (string) ($this->training->number ?? ''),
            'complement' => (string) ($this->training->complement ?? ''),
            'district' => (string) ($this->training->district ?? ''),
            'city' => (string) ($this->training->city ?? ''),
            'state' => (string) ($this->training->state ?? ''),
        ];

        $defaultLeader = $this->resolveLeaderDefault($church);
        $defaultCoordinator = $this->resolveCoordinatorDefault($church);

        $this->leader = filled($this->training->leader) ? $this->training->leader : $defaultLeader;
        $this->coordinator = filled($this->training->coordinator) ? $this->training->coordinator : $defaultCoordinator;
    }

    private function applySelectedChurchData(int $churchId): void
    {
        $church = Church::query()->find($churchId);

        if (! $church) {
            return;
        }

        $this->address = [
            'postal_code' => $church->postal_code ?? '',
            'street' => $church->street ?? '',
            'number' => $church->number ?? '',
            'complement' => $church->complement ?? '',
            'district' => $church->district ?? '',
            'city' => $church->city ?? '',
            'state' => $church->state ?? '',
        ];
        $this->leader = $this->resolveLeaderDefault($church);
        $this->coordinator = $this->resolveCoordinatorDefault($church);
    }

    private function resolveLeaderDefault(?Church $church): string
    {
        return trim((string) ($church?->pastor ?? ''));
    }

    private function resolveCoordinatorDefault(?Church $church): string
    {
        $contact = trim((string) ($church?->contact ?? ''));

        if ($contact !== '') {
            return $contact;
        }

        return $this->resolveLeaderDefault($church);
    }

    private function loadTraining(): void
    {
        $this->training = Training::query()->with('church')->findOrFail($this->trainingId);
        $this->authorizeTraining($this->training);
    }

    /**
     * @return EloquentCollection<int, Church>
     */
    private function loadChurches(): EloquentCollection
    {
        return Church::query()
            ->when($this->churchSearch !== '', function ($query): void {
                $search = '%'.$this->churchSearch.'%';
                $query->where('name', 'like', $search)
                    ->orWhere('pastor', 'like', $search)
                    ->orWhere('district', 'like', $search)
                    ->orWhere('city', 'like', $search)
                    ->orWhere('state', 'like', $search);
            })
            ->orderBy('name')
            ->limit(5)
            ->get();
    }

    private function authorizeTraining(Training $training): void
    {
        Gate::authorize('access-teacher');

        if (Auth::id() !== $training->teacher_id) {
            abort(403);
        }
    }
}
