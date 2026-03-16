<?php

namespace App\Livewire\Pages\App\Director\Inventory;

use App\Models\Church;
use App\Models\Inventory;
use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class CreateInventoryModal extends Component
{
    public bool $showModal = false;

    public bool $busy = false;

    public string $name = '';

    public string $kind = 'teacher';

    public ?int $user_id = null;

    public ?int $church_id = null;

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

    #[On('open-director-inventory-create-modal')]
    public function openModal(): void
    {
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function save(): void
    {
        if ($this->busy) {
            return;
        }

        $this->busy = true;

        try {
            $validated = $this->validate($this->rules(), $this->messages(), $this->validationAttributes());

            $inventory = Inventory::query()->create([
                'name' => $validated['name'],
                'kind' => $validated['kind'],
                'user_id' => $validated['kind'] === 'teacher' ? $validated['user_id'] : null,
                'church_id' => $validated['kind'] === 'base' ? $validated['church_id'] : null,
                'is_active' => true,
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
            ]);

            $this->dispatch('director-inventory-created', inventoryId: $inventory->id);
            $this->dispatch('toast', type: 'success', message: __('Estoque cadastrado com sucesso.'));

            $this->closeModal();
            $this->redirectRoute('app.director.inventory.show', ['inventory' => $inventory->id], navigate: true);
        } finally {
            $this->busy = false;
        }
    }

    public function render(): View
    {
        return view('livewire.pages.app.director.inventory.create-inventory-modal', [
            'teacherOptions' => $this->teacherOptions(),
            'churchOptions' => $this->churchOptions(),
            'kindOptions' => [
                ['value' => 'central', 'label' => __('Central')],
                ['value' => 'base', 'label' => __('Base')],
                ['value' => 'teacher', 'label' => __('Professor')],
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
            'kind' => ['required', 'in:central,base,teacher'],
            'user_id' => [
                Rule::requiredIf(fn (): bool => $this->kind === 'teacher'),
                'nullable',
                'integer',
                Rule::in($this->teacherIds()),
            ],
            'church_id' => [
                Rule::requiredIf(fn (): bool => $this->kind === 'base'),
                'nullable',
                'integer',
                Rule::in($this->churchIds()),
            ],
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
            'church_id' => 'base vinculada',
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
     * @return array<int, array{value: int, label: string}>
     */
    private function churchOptions(): array
    {
        return $this->churches()
            ->map(fn (Church $church): array => [
                'value' => $church->id,
                'label' => trim($church->name.' - '.implode(' / ', array_filter([$church->city, $church->state]))),
            ])
            ->all();
    }

    /**
     * @return array<int, int>
     */
    private function churchIds(): array
    {
        return $this->churches()->pluck('id')->map(fn ($id): int => (int) $id)->all();
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

    /**
     * @return \Illuminate\Support\Collection<int, Church>
     */
    private function churches()
    {
        return Church::query()
            ->orderBy('name')
            ->get(['id', 'name', 'city', 'state']);
    }

    private function resetForm(): void
    {
        $this->resetValidation();
        $this->name = '';
        $this->kind = 'teacher';
        $this->user_id = null;
        $this->church_id = null;
        $this->phone = null;
        $this->email = null;
        $this->address = [
            'postal_code' => '',
            'street' => '',
            'number' => '',
            'complement' => '',
            'district' => '',
            'city' => '',
            'state' => '',
        ];
        $this->notes = null;
    }

    private function nullableAddressValue(string $key): ?string
    {
        $value = trim((string) ($this->address[$key] ?? ''));

        return $value !== '' ? $value : null;
    }
}
