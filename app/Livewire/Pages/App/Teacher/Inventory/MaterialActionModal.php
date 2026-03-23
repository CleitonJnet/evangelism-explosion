<?php

namespace App\Livewire\Pages\App\Teacher\Inventory;

use App\Exceptions\Inventory\InsufficientStockException;
use App\Exceptions\Inventory\InvalidCompositeMaterialException;
use App\Models\Inventory;
use App\Models\Material;
use App\Models\MaterialComponent;
use App\Services\Inventory\StockMovementService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use InvalidArgumentException;
use Livewire\Attributes\On;
use Livewire\Component;

class MaterialActionModal extends Component
{
    use AuthorizesRequests;

    public int $materialId;

    public int $inventoryId;

    public bool $showModal = false;

    public bool $busy = false;

    public string $activeTab = 'entry';

    public ?int $entry_quantity = null;

    public ?string $entry_notes = null;

    public ?int $exit_quantity = null;

    public ?string $exit_notes = null;

    public ?int $adjustment_target_quantity = null;

    public ?string $adjustment_notes = null;

    public ?int $loss_quantity = null;

    public ?string $loss_notes = null;

    public function mount(int $materialId, int $inventoryId): void
    {
        $this->materialId = $materialId;
        $this->inventoryId = $inventoryId;
        $this->fillForm();
    }

    #[On('open-teacher-material-action-modal')]
    public function openModal(?int $materialId = null, ?string $tab = null): void
    {
        if ($materialId !== null && $materialId !== $this->materialId) {
            return;
        }

        $this->fillForm();
        $this->activeTab = $this->resolveAllowedTab($tab);
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetValidation();
        $this->fillForm();
    }

    public function selectTab(string $tab): void
    {
        if (in_array($tab, $this->availableTabs(), true)) {
            $this->activeTab = $tab;
            $this->resetValidation();
        }
    }

    public function saveEntry(): void
    {
        if ($this->busy) {
            return;
        }

        $this->ensureTabAllowed('entry');
        $this->busy = true;

        try {
            $validated = $this->validate([
                'entry_quantity' => ['required', 'integer', 'min:1'],
                'entry_notes' => ['nullable', 'string', 'max:2000'],
            ], $this->validationMessages(), [
                'entry_quantity' => 'quantidade de entrada',
                'entry_notes' => 'observação',
            ]);

            $inventory = $this->inventory();
            $material = $this->material();

            app(StockMovementService::class)->addStock(
                $inventory,
                $material,
                (int) $validated['entry_quantity'],
                Auth::user(),
                notes: $validated['entry_notes'] ?? null,
            );

            $this->dispatch('teacher-inventory-stock-updated', inventoryId: $inventory->id);
            $this->dispatch('toast', type: 'success', message: __('Entrada manual registrada com sucesso.'));
            $this->closeModal();
        } finally {
            $this->busy = false;
        }
    }

    public function saveExit(): void
    {
        if ($this->busy) {
            return;
        }

        $this->ensureTabAllowed('exit');
        $this->busy = true;

        try {
            $validated = $this->validate([
                'exit_quantity' => ['required', 'integer', 'min:1'],
                'exit_notes' => ['nullable', 'string', 'max:2000'],
            ], $this->validationMessages(), [
                'exit_quantity' => 'quantidade de saída',
                'exit_notes' => 'observação',
            ]);

            $inventory = $this->inventory();
            $material = $this->material();
            $service = app(StockMovementService::class);

            if ($material->isComposite()) {
                $service->removeCompositeMaterial(
                    $inventory,
                    $material,
                    (int) $validated['exit_quantity'],
                    Auth::user(),
                    notes: $validated['exit_notes'] ?? null,
                    allowDynamicComposition: true,
                );
            } else {
                $service->removeStock(
                    $inventory,
                    $material,
                    (int) $validated['exit_quantity'],
                    Auth::user(),
                    notes: $validated['exit_notes'] ?? null,
                );
            }

            $this->dispatch('teacher-inventory-stock-updated', inventoryId: $inventory->id);
            $this->dispatch('toast', type: 'success', message: __('Saída manual registrada com sucesso.'));
            $this->closeModal();
        } catch (InsufficientStockException|InvalidCompositeMaterialException $exception) {
            $this->addError('exit_quantity', $exception->getMessage());
            $this->dispatch('toast', type: 'error', message: $exception->getMessage());
        } finally {
            $this->busy = false;
        }
    }

    public function saveAdjustment(): void
    {
        if ($this->busy) {
            return;
        }

        $this->ensureTabAllowed('adjustment');
        $this->busy = true;

        try {
            $validated = $this->validate([
                'adjustment_target_quantity' => ['required', 'integer', 'min:0'],
                'adjustment_notes' => ['nullable', 'string', 'max:2000'],
            ], $this->validationMessages(), [
                'adjustment_target_quantity' => 'saldo alvo',
                'adjustment_notes' => 'observação',
            ]);

            $inventory = $this->inventory();
            $material = $this->material();

            app(StockMovementService::class)->adjustStock(
                $inventory,
                $material,
                (int) $validated['adjustment_target_quantity'],
                Auth::user(),
                notes: $validated['adjustment_notes'] ?? null,
            );

            $this->dispatch('teacher-inventory-stock-updated', inventoryId: $inventory->id);
            $this->dispatch('toast', type: 'success', message: __('Ajuste de saldo aplicado com sucesso.'));
            $this->closeModal();
        } finally {
            $this->busy = false;
        }
    }

    public function saveLoss(): void
    {
        if ($this->busy) {
            return;
        }

        $this->ensureTabAllowed('loss');
        $this->busy = true;

        try {
            $validated = $this->validate([
                'loss_quantity' => ['required', 'integer', 'min:1'],
                'loss_notes' => ['nullable', 'string', 'max:2000'],
            ], $this->validationMessages(), [
                'loss_quantity' => 'quantidade perdida',
                'loss_notes' => 'observação',
            ]);

            $inventory = $this->inventory();
            $material = $this->material();

            app(StockMovementService::class)->registerLoss(
                $inventory,
                $material,
                (int) $validated['loss_quantity'],
                Auth::user(),
                notes: $validated['loss_notes'] ?? null,
            );

            $this->dispatch('teacher-inventory-stock-updated', inventoryId: $inventory->id);
            $this->dispatch('toast', type: 'success', message: __('Perda/avaria registrada com sucesso.'));
            $this->closeModal();
        } catch (InsufficientStockException $exception) {
            $this->addError('loss_quantity', $exception->getMessage());
            $this->dispatch('toast', type: 'error', message: $exception->getMessage());
        } finally {
            $this->busy = false;
        }
    }

    public function render(): View
    {
        $inventory = $this->inventory();
        $material = $this->material();
        $currentQuantity = $inventory->currentQuantityFor($material);
        $composableQuantity = $material->isComposite() ? $this->composableQuantity($inventory, $material) : 0;

        return view('livewire.pages.app.teacher.inventory.material-action-modal', [
            'inventory' => $inventory,
            'material' => $material,
            'currentQuantity' => $currentQuantity,
            'composableQuantity' => $composableQuantity,
            'compositionItems' => $this->compositionItems($inventory, $material),
            'availableTabs' => $this->availableTabs(),
        ]);
    }

    private function fillForm(): void
    {
        $inventory = $this->inventory();
        $material = $this->material();

        $this->activeTab = $this->resolveAllowedTab();
        $this->entry_quantity = null;
        $this->entry_notes = null;
        $this->exit_quantity = null;
        $this->exit_notes = null;
        $this->adjustment_target_quantity = $inventory->currentQuantityFor($material);
        $this->adjustment_notes = null;
        $this->loss_quantity = null;
        $this->loss_notes = null;
        $this->resetErrorBag();
    }

    /**
     * @return array<int, string>
     */
    private function availableTabs(): array
    {
        $inventory = $this->inventory();
        $material = $this->material();

        if (! $material->isComposite()) {
            return ['entry', 'exit', 'loss'];
        }

        $hasDirectInventoryRow = $inventory->materials()
            ->where('materials.id', $material->id)
            ->exists();

        if ($hasDirectInventoryRow) {
            return ['entry', 'exit', 'loss', 'composition'];
        }

        return ['exit', 'composition'];
    }

    private function resolveAllowedTab(?string $requestedTab = null): string
    {
        $availableTabs = $this->availableTabs();

        if ($requestedTab !== null && in_array($requestedTab, $availableTabs, true)) {
            return $requestedTab;
        }

        if (in_array('exit', $availableTabs, true)) {
            return 'exit';
        }

        return $availableTabs[0];
    }

    private function ensureTabAllowed(string $tab): void
    {
        if (! in_array($tab, $this->availableTabs(), true)) {
            throw new InvalidArgumentException(__('Esta ação não está disponível para este produto no estoque delegado.'));
        }
    }

    private function inventory(): Inventory
    {
        $inventory = Inventory::query()->findOrFail($this->inventoryId);
        $this->authorize('update', $inventory);

        return $inventory;
    }

    private function material(): Material
    {
        return Material::query()
            ->with('courses:id,name,initials,color,type')
            ->findOrFail($this->materialId);
    }

    private function composableQuantity(Inventory $inventory, Material $material): int
    {
        $components = $material->components()->get(['component_material_id', 'quantity']);

        if ($components->isEmpty()) {
            return 0;
        }

        return (int) floor($components->min(function (object $component) use ($inventory): float {
            return $inventory->currentQuantityFor((int) $component->component_material_id) / max((int) $component->quantity, 1);
        }));
    }

    /**
     * @return array<int, array{
     *     id: int,
     *     name: string,
     *     quantity: int,
     *     current_quantity: int,
     *     is_active: bool
     * }>
     */
    private function compositionItems(Inventory $inventory, Material $material): array
    {
        if (! $material->isComposite()) {
            return [];
        }

        return $material->components()
            ->with('componentMaterial:id,name,is_active')
            ->get()
            ->map(function (MaterialComponent $component) use ($inventory): array {
                $componentMaterial = $component->componentMaterial;

                return [
                    'id' => (int) $component->component_material_id,
                    'name' => (string) ($componentMaterial?->name ?: __('Item não encontrado')),
                    'quantity' => (int) $component->quantity,
                    'current_quantity' => $inventory->currentQuantityFor((int) $component->component_material_id),
                    'is_active' => (bool) ($componentMaterial?->is_active ?? false),
                ];
            })
            ->all();
    }

    private function validationMessages(): array
    {
        return [
            'required' => 'O campo :attribute é obrigatório.',
            'integer' => 'O campo :attribute deve ser um número inteiro.',
            'min' => 'O campo :attribute deve ser no mínimo :min.',
            'max' => 'O campo :attribute não pode ter mais de :max caracteres.',
        ];
    }
}
