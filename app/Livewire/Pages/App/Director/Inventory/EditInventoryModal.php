<?php

namespace App\Livewire\Pages\App\Director\Inventory;

use App\Models\Inventory;
use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class EditInventoryModal extends Component
{
    public int $inventoryId;

    public bool $showModal = false;

    public bool $busy = false;

    public string $name = '';

    public string $kind = 'teacher';

    public ?int $user_id = null;

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

    #[On('open-director-inventory-edit-modal')]
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

            $inventory->forceFill([
                'name' => $validated['name'],
                'kind' => $validated['kind'],
                'user_id' => $validated['kind'] === 'teacher' ? $validated['user_id'] : null,
                'is_active' => $validated['status'] === 'active',
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

            $this->dispatch('director-inventory-updated', inventoryId: $inventory->id);
            $this->dispatch('toast', type: 'success', message: __('Estoque atualizado com sucesso.'));
            $this->closeModal();
        } finally {
            $this->busy = false;
        }
    }

    public function render(): View
    {
        return view('livewire.pages.app.director.inventory.edit-inventory-modal', [
            'teacherOptions' => $this->teacherOptions(),
            'kindOptions' => [
                ['value' => 'central', 'label' => __('Central')],
                ['value' => 'teacher', 'label' => __('Professor')],
            ],
            'statusOptions' => [
                ['value' => 'active', 'label' => __('Ativo')],
                ['value' => 'inactive', 'label' => __('Inativo')],
            ],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'kind' => ['required', 'in:central,teacher'],
            'user_id' => [
                Rule::requiredIf(fn (): bool => $this->kind === 'teacher'),
                'nullable',
                'integer',
                Rule::in($this->teacherIds()),
            ],
            'status' => ['required', 'in:active,inactive'],
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

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'required' => 'O campo :attribute é obrigatório.',
            'email' => 'O campo :attribute deve conter um email válido.',
            'max' => 'O campo :attribute não pode ter mais de :max caracteres.',
            'in' => 'O valor informado para :attribute é inválido.',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            'name' => 'nome',
            'kind' => 'tipo',
            'user_id' => 'professor responsável',
            'status' => 'status',
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

    /**
     * @return array<int, array{value: int, label: string}>
     */
    private function teacherOptions(): array
    {
        return $this->teachers()
            ->map(fn (User $teacher): array => [
                'value' => $teacher->id,
                'label' => $teacher->name,
            ])
            ->all();
    }

    /**
     * @return array<int, int>
     */
    private function teacherIds(): array
    {
        return $this->teachers()->pluck('id')->map(fn ($id): int => (int) $id)->all();
    }

    /**
     * @return \Illuminate\Support\Collection<int, User>
     */
    private function teachers()
    {
        return User::query()
            ->whereHas('roles', fn ($query) => $query->whereIn('name', ['Teacher', 'teacher']))
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    private function fillForm(): void
    {
        $inventory = Inventory::query()->findOrFail($this->inventoryId);

        $this->name = (string) $inventory->name;
        $this->kind = (string) ($inventory->kind ?: 'teacher');
        $this->user_id = $inventory->user_id ? (int) $inventory->user_id : null;
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
