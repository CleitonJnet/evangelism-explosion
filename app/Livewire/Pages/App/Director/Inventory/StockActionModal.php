<?php

namespace App\Livewire\Pages\App\Director\Inventory;

use App\Exceptions\Inventory\InsufficientStockException;
use App\Exceptions\Inventory\InvalidCompositeMaterialException;
use App\Models\Inventory;
use App\Models\Material;
use App\Services\Inventory\StockMovementService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use InvalidArgumentException;
use Livewire\Attributes\On;
use Livewire\Component;

class StockActionModal extends Component
{
    public int $inventoryId;

    public bool $showModal = false;

    public bool $busy = false;

    public string $mode = 'entry';

    public ?int $material_id = null;

    public ?int $quantity = null;

    public ?int $target_quantity = null;

    public ?string $notes = null;

    public function mount(int $inventoryId): void
    {
        $this->inventoryId = $inventoryId;
    }

    #[On('director-material-created')]
    public function handleMaterialCreated(?int $materialId = null): void
    {
        if ($materialId !== null) {
            $this->material_id = $materialId;
        }
    }

    #[On('open-director-inventory-stock-action-modal')]
    public function openModal(?int $inventoryId = null, ?string $mode = null): void
    {
        if ($inventoryId !== null && $inventoryId !== $this->inventoryId) {
            return;
        }

        $this->resetValidation();
        $this->material_id = null;
        $this->quantity = null;
        $this->target_quantity = null;
        $this->notes = null;
        $this->mode = in_array($mode, ['entry', 'exit', 'adjustment', 'loss'], true) ? $mode : 'entry';
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
            $inventory = Inventory::query()->findOrFail($this->inventoryId);
            $material = Material::query()->findOrFail((int) $validated['material_id']);
            $actor = Auth::user();
            $service = app(StockMovementService::class);

            match ($this->mode) {
                'entry' => $service->addStock($inventory, $material, (int) $validated['quantity'], $actor, notes: $validated['notes'] ?? null),
                'exit' => $material->isComposite()
                    ? $service->removeCompositeMaterial($inventory, $material, (int) $validated['quantity'], $actor, notes: $validated['notes'] ?? null)
                    : $service->removeStock($inventory, $material, (int) $validated['quantity'], $actor, notes: $validated['notes'] ?? null),
                'adjustment' => $service->adjustStock($inventory, $material, (int) $validated['target_quantity'], $actor, notes: $validated['notes'] ?? null),
                'loss' => $service->registerLoss($inventory, $material, (int) $validated['quantity'], $actor, notes: $validated['notes'] ?? null),
                default => throw new InvalidArgumentException('Modo de movimentação inválido.'),
            };

            $this->dispatch('director-inventory-stock-updated', inventoryId: $inventory->id);
            $this->dispatch('toast', type: 'success', message: $this->successMessage($material));
            $this->closeModal();
        } catch (InsufficientStockException|InvalidCompositeMaterialException|InvalidArgumentException $exception) {
            $field = $this->mode === 'adjustment' ? 'target_quantity' : 'quantity';
            $this->addError($field, $exception->getMessage());
            $this->dispatch('toast', type: 'error', message: $exception->getMessage());
        } finally {
            $this->busy = false;
        }
    }

    public function render(): View
    {
        return view('livewire.pages.app.director.inventory.stock-action-modal', [
            'inventory' => Inventory::query()->findOrFail($this->inventoryId),
            'materialOptions' => $this->materialOptions(),
            'hasMaterials' => Material::query()->exists(),
            'modeMeta' => $this->modeMeta(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'material_id' => ['required', 'integer', Rule::exists('materials', 'id')],
            'quantity' => [
                Rule::requiredIf(fn (): bool => in_array($this->mode, ['entry', 'exit', 'loss'], true)),
                'nullable',
                'integer',
                'min:1',
            ],
            'target_quantity' => [
                Rule::requiredIf(fn (): bool => $this->mode === 'adjustment'),
                'nullable',
                'integer',
                'min:0',
            ],
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
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            'material_id' => 'material',
            'quantity' => 'quantidade',
            'target_quantity' => 'saldo alvo',
            'notes' => 'observação',
        ];
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
                'label' => sprintf(
                    '%s%s',
                    $material->name,
                    $material->isComposite() ? ' ('.__('Composto').')' : ''
                ),
            ])
            ->all();
    }

    /**
     * @return array{title: string, description: string, action: string, quantity_label: string}
     */
    private function modeMeta(): array
    {
        return match ($this->mode) {
            'exit' => [
                'title' => __('Registrar saída'),
                'description' => __('Para materiais compostos, a saída consome automaticamente seus componentes.'),
                'action' => __('Registrar saída'),
                'quantity_label' => __('Quantidade de saída'),
            ],
            'adjustment' => [
                'title' => __('Ajustar saldo'),
                'description' => __('Defina o saldo final consolidado do material neste estoque.'),
                'action' => __('Aplicar ajuste'),
                'quantity_label' => __('Saldo alvo'),
            ],
            'loss' => [
                'title' => __('Registrar perda'),
                'description' => __('Registre avarias, perdas ou baixas não recuperáveis.'),
                'action' => __('Registrar perda'),
                'quantity_label' => __('Quantidade perdida'),
            ],
            default => [
                'title' => __('Registrar entrada'),
                'description' => __('Adicione saldo manualmente ao estoque selecionado.'),
                'action' => __('Registrar entrada'),
                'quantity_label' => __('Quantidade de entrada'),
            ],
        };
    }

    private function successMessage(Material $material): string
    {
        return match ($this->mode) {
            'exit' => $material->isComposite()
                ? __('Saída do kit registrada com baixa automática dos componentes.')
                : __('Saída manual registrada com sucesso.'),
            'adjustment' => __('Ajuste de saldo aplicado com sucesso.'),
            'loss' => __('Perda/avaria registrada com sucesso.'),
            default => __('Entrada manual registrada com sucesso.'),
        };
    }
}
