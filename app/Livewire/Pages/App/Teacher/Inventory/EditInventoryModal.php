<?php

namespace App\Livewire\Pages\App\Teacher\Inventory;

use App\Models\Inventory;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class EditInventoryModal extends Component
{
    use AuthorizesRequests;

    public int $inventoryId;

    public bool $showModal = false;

    public bool $busy = false;

    public string $name = '';

    public string $status = 'active';

    public ?string $phone = null;

    public ?string $email = null;

    /**
     * @var array{
     *     postal_code: string,
     *     street: string,
     *     number: string,
     *     complement: string,
     *     district: string,
     *     city: string,
     *     state: string
     * }
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

    public ?string $notes = null;

    public function mount(int $inventoryId): void
    {
        $this->inventoryId = $inventoryId;
        $this->fillForm();
    }

    #[On('open-teacher-inventory-edit-modal')]
    public function openModal(?int $inventoryId = null): void
    {
        if ($inventoryId !== null && $inventoryId !== $this->inventoryId) {
            return;
        }

        $this->fillForm();
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetValidation();
        $this->fillForm();
    }

    public function save(): void
    {
        if ($this->busy) {
            return;
        }

        $this->busy = true;

        try {
            $validated = $this->validate($this->rules(), $this->messages(), $this->validationAttributes());
            $inventory = Inventory::query()->findOrFail($this->inventoryId);
            $this->authorize('update', $inventory);

            $inventory->forceFill([
                'name' => $validated['name'],
                'phone' => $validated['phone'] ?? null,
                'email' => $validated['email'] ?? null,
                'street' => $this->nullableAddressValue('street'),
                'number' => $this->nullableAddressValue('number'),
                'complement' => $this->nullableAddressValue('complement'),
                'district' => $this->nullableAddressValue('district'),
                'city' => $this->nullableAddressValue('city'),
                'state' => $this->nullableAddressValue('state'),
                'postal_code' => $this->nullableAddressValue('postal_code'),
                'notes' => $validated['notes'] ?? null,
            ])->save();

            $this->dispatch('teacher-inventory-updated', inventoryId: $inventory->id);
            $this->dispatch('toast', type: 'success', message: __('Estoque atualizado com sucesso.'));
            $this->closeModal();
        } finally {
            $this->busy = false;
        }
    }

    public function render(): View
    {
        $inventory = Inventory::query()->findOrFail($this->inventoryId);
        $this->authorize('view', $inventory);

        return view('livewire.pages.app.teacher.inventory.edit-inventory-modal');
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'address.street' => ['nullable', 'string', 'max:255'],
            'address.number' => ['nullable', 'string', 'max:30'],
            'address.complement' => ['nullable', 'string', 'max:255'],
            'address.district' => ['nullable', 'string', 'max:255'],
            'address.city' => ['nullable', 'string', 'max:255'],
            'address.state' => ['nullable', 'string', 'max:10'],
            'address.postal_code' => ['nullable', 'string', 'max:20'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    protected function messages(): array
    {
        return [
            'required' => 'O campo :attribute é obrigatório.',
            'email' => 'O campo :attribute deve conter um email válido.',
            'max' => 'O campo :attribute não pode ter mais de :max caracteres.',
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'name' => 'nome',
            'phone' => 'telefone',
            'email' => 'email',
            'address.street' => 'logradouro',
            'address.number' => 'número',
            'address.complement' => 'complemento',
            'address.district' => 'bairro',
            'address.city' => 'cidade',
            'address.state' => 'estado',
            'address.postal_code' => 'CEP',
            'notes' => 'observações',
        ];
    }

    private function fillForm(): void
    {
        $inventory = Inventory::query()->findOrFail($this->inventoryId);
        $this->authorize('view', $inventory);

        $this->name = (string) $inventory->name;
        $this->status = $inventory->is_active ? 'active' : 'inactive';
        $this->phone = $inventory->phone;
        $this->email = $inventory->email;
        $this->address = [
            'postal_code' => (string) ($inventory->postal_code ?? ''),
            'street' => (string) ($inventory->street ?? ''),
            'number' => (string) ($inventory->number ?? ''),
            'complement' => (string) ($inventory->complement ?? ''),
            'district' => (string) ($inventory->district ?? ''),
            'city' => (string) ($inventory->city ?? ''),
            'state' => (string) ($inventory->state ?? ''),
        ];
        $this->notes = $inventory->notes;
    }

    private function nullableAddressValue(string $key): ?string
    {
        $value = trim((string) ($this->address[$key] ?? ''));

        return $value !== '' ? $value : null;
    }
}
