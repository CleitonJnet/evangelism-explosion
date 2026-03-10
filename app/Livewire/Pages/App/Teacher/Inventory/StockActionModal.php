<?php

namespace App\Livewire\Pages\App\Teacher\Inventory;

use App\Exceptions\Inventory\InsufficientStockException;
use App\Exceptions\Inventory\InvalidCompositeMaterialException;
use App\Models\Inventory;
use App\Models\Material;
use App\Services\Inventory\StockMovementService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use InvalidArgumentException;
use Livewire\Attributes\On;
use Livewire\Component;

class StockActionModal extends Component
{
    use AuthorizesRequests;

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

    #[On('open-teacher-inventory-stock-action-modal')]
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
            $this->authorize('update', $inventory);

            $material = Material::query()->findOrFail((int) $validated['material_id']);

            if (! $this->allowedMaterialIds($inventory)->contains((int) $material->id)) {
                throw new InvalidArgumentException('Selecione um material já transferido para este estoque.');
            }

            $actor = Auth::user();
            $service = app(StockMovementService::class);

            match ($this->mode) {
                'entry' => $service->addStock($inventory, $material, (int) $validated['quantity'], $actor, notes: $validated['notes'] ?? null),
                'exit' => $material->isComposite()
                    ? $service->removeCompositeMaterial($inventory, $material, (int) $validated['quantity'], $actor, notes: $validated['notes'] ?? null, allowDynamicComposition: true)
                    : $service->removeStock($inventory, $material, (int) $validated['quantity'], $actor, notes: $validated['notes'] ?? null),
                'adjustment' => $service->adjustStock($inventory, $material, (int) $validated['target_quantity'], $actor, notes: $validated['notes'] ?? null),
                'loss' => $service->registerLoss($inventory, $material, (int) $validated['quantity'], $actor, notes: $validated['notes'] ?? null),
                default => throw new InvalidArgumentException('Modo de movimentação inválido.'),
            };

            $this->dispatch('teacher-inventory-stock-updated', inventoryId: $inventory->id);
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
        $inventory = Inventory::query()->findOrFail($this->inventoryId);
        $this->authorize('view', $inventory);
        $materialOptions = $this->materialOptions($inventory);

        return view('livewire.pages.app.teacher.inventory.stock-action-modal', [
            'inventory' => $inventory,
            'materialOptions' => $materialOptions,
            'hasMaterials' => $materialOptions !== [],
            'modeMeta' => $this->modeMeta(),
        ]);
    }

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

    protected function messages(): array
    {
        return [
            'required' => 'O campo :attribute é obrigatório.',
            'integer' => 'O campo :attribute deve ser um número inteiro.',
            'min' => 'O campo :attribute deve ser no mínimo :min.',
            'max' => 'O campo :attribute não pode ter mais de :max caracteres.',
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'material_id' => 'material',
            'quantity' => 'quantidade',
            'target_quantity' => 'saldo alvo',
            'notes' => 'observação',
        ];
    }

    private function materialOptions(Inventory $inventory): array
    {
        return Material::query()
            ->whereIn('id', $this->allowedMaterialIds($inventory)->all())
            ->orderBy('materials.name')
            ->get(['materials.id', 'materials.name', 'materials.type'])
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
                'description' => __('Adicione saldo manualmente ao estoque sob sua responsabilidade.'),
                'action' => __('Registrar entrada'),
                'quantity_label' => __('Quantidade de entrada'),
            ],
        };
    }

    /**
     * @return Collection<int, int>
     */
    private function allowedMaterialIds(Inventory $inventory): Collection
    {
        $transferredMaterialIds = $inventory->materials()
            ->pluck('materials.id')
            ->map(fn (mixed $id): int => (int) $id);

        if ($this->mode !== 'exit') {
            return $transferredMaterialIds;
        }

        return $transferredMaterialIds
            ->merge($inventory->availableCompositeMaterialIds())
            ->unique()
            ->values();
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
