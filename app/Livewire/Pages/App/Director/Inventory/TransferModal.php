<?php

namespace App\Livewire\Pages\App\Director\Inventory;

use App\Exceptions\Inventory\InsufficientStockException;
use App\Models\Inventory;
use App\Models\Material;
use App\Services\Inventory\StockMovementService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use InvalidArgumentException;
use Livewire\Attributes\On;
use Livewire\Component;

class TransferModal extends Component
{
    public int $inventoryId;

    public bool $showModal = false;

    public bool $busy = false;

    public ?int $destination_inventory_id = null;

    public ?int $material_id = null;

    public ?int $quantity = null;

    public ?string $notes = null;

    public function mount(int $inventoryId): void
    {
        $this->inventoryId = $inventoryId;
    }

    #[On('open-director-inventory-transfer-modal')]
    public function openModal(?int $inventoryId = null): void
    {
        if ($inventoryId !== null && $inventoryId !== $this->inventoryId) {
            return;
        }

        $this->resetValidation();
        $this->destination_inventory_id = null;
        $this->material_id = null;
        $this->quantity = null;
        $this->notes = null;
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
    }

    public function save(): void
    {
        if ($this->busy) {
            return;
        }

        $this->busy = true;

        try {
            $validated = $this->validate($this->rules(), $this->messages(), $this->validationAttributes());

            $sourceInventory = Inventory::query()->findOrFail($this->inventoryId);
            $destinationInventory = Inventory::query()->findOrFail((int) $validated['destination_inventory_id']);
            $material = Material::query()->findOrFail((int) $validated['material_id']);

            app(StockMovementService::class)->transferStock(
                $sourceInventory,
                $destinationInventory,
                $material,
                (int) $validated['quantity'],
                Auth::user(),
                notes: $validated['notes'] ?? null,
            );

            $this->dispatch('director-inventory-stock-updated', inventoryId: $sourceInventory->id);
            $this->dispatch('director-inventory-stock-updated', inventoryId: $destinationInventory->id);
            $this->dispatch('toast', type: 'success', message: __('Transferência registrada com sucesso.'));
            $this->closeModal();
        } catch (InsufficientStockException|InvalidArgumentException $exception) {
            $field = str_contains($exception->getMessage(), 'estoques distintos') ? 'destination_inventory_id' : 'quantity';
            $this->addError($field, $exception->getMessage());
            $this->dispatch('toast', type: 'error', message: $exception->getMessage());
        } finally {
            $this->busy = false;
        }
    }

    public function render(): View
    {
        return view('livewire.pages.app.director.inventory.transfer-modal', [
            'inventory' => Inventory::query()->findOrFail($this->inventoryId),
            'destinationOptions' => $this->destinationOptions(),
            'materialOptions' => $this->materialOptions(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'destination_inventory_id' => [
                'required',
                'integer',
                Rule::exists('inventories', 'id'),
                Rule::notIn([$this->inventoryId]),
            ],
            'material_id' => ['required', 'integer', Rule::exists('materials', 'id')],
            'quantity' => ['required', 'integer', 'min:1'],
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
            'integer' => 'O campo :attribute deve ser um número inteiro.',
            'min' => 'O campo :attribute deve ser no mínimo :min.',
            'max' => 'O campo :attribute não pode ter mais de :max caracteres.',
            'not_in' => 'Escolha um estoque de destino diferente do estoque de origem.',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            'destination_inventory_id' => 'estoque de destino',
            'material_id' => 'material',
            'quantity' => 'quantidade',
            'notes' => 'observação',
        ];
    }

    /**
     * @return array<int, array{value: int, label: string}>
     */
    private function destinationOptions(): array
    {
        return Inventory::query()
            ->with('responsibleUser:id,name')
            ->whereKeyNot($this->inventoryId)
            ->orderBy('name')
            ->get()
            ->map(function (Inventory $inventory): array {
                $suffix = $inventory->kind === 'teacher'
                    ? ' - '.($inventory->responsibleUser?->name ?: __('Sem professor'))
                    : ' - '.__('Central');

                return [
                    'value' => $inventory->id,
                    'label' => $inventory->name.$suffix,
                ];
            })
            ->all();
    }

    /**
     * @return array<int, array{value: int, label: string}>
     */
    private function materialOptions(): array
    {
        return Material::query()
            ->orderBy('name')
            ->get(['id', 'name', 'type'])
            ->map(fn (Material $material): array => [
                'value' => $material->id,
                'label' => sprintf('%s%s', $material->name, $material->isComposite() ? ' ('.__('Composto').')' : ''),
            ])
            ->all();
    }
}
