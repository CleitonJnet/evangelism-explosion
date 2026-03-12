<?php

namespace App\Livewire\Pages\App\Teacher\Inventory;

use App\Models\Inventory;
use App\Models\StockMovement;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View as ViewView;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class View extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    public int $inventoryId;

    public string $materialSearch = '';

    public string $movementTypeFilter = '';

    public bool $showDeleteModal = false;

    public string $inventoryDeletionBlockedReason = '';

    public function mount(Inventory $inventory): void
    {
        $this->authorize('view', $inventory);
        $this->inventoryId = $inventory->id;
    }

    #[On('teacher-inventory-updated')]
    #[On('teacher-inventory-stock-updated')]
    public function refreshInventory(int $inventoryId): void
    {
        if ($this->inventoryId !== $inventoryId) {
            return;
        }
    }

    #[On('open-teacher-inventory-delete-modal')]
    public function openDeleteModal(): void
    {
        $inventory = Inventory::query()->findOrFail($this->inventoryId);

        $this->authorize('delete', $inventory);

        $this->inventoryDeletionBlockedReason = $inventory->stockMovements()->exists()
            ? __('Este estoque não pode ser excluído porque já possui movimentações registradas no histórico auditável.')
            : '';
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->inventoryDeletionBlockedReason = '';
    }

    public function deleteInventory(): void
    {
        $inventory = Inventory::query()->find($this->inventoryId);

        if (! $inventory) {
            $this->closeDeleteModal();
            session()->flash('success', __('Estoque removido com sucesso.'));
            $this->redirectRoute('app.teacher.inventory.index', navigate: true);

            return;
        }

        $this->authorize('delete', $inventory);

        if ($inventory->stockMovements()->exists()) {
            $this->inventoryDeletionBlockedReason = __('Este estoque não pode ser excluído porque já possui movimentações registradas no histórico auditável.');

            return;
        }

        $inventory->delete();

        $this->closeDeleteModal();
        session()->flash('success', __('Estoque removido com sucesso.'));
        $this->redirectRoute('app.teacher.inventory.index', navigate: true);
    }

    public function updatingMaterialSearch(): void {}

    public function updatingMovementTypeFilter(): void
    {
        $this->resetPage('movementsPage');
    }

    public function render(): ViewView
    {
        $inventory = Inventory::query()
            ->with('responsibleUser:id,name')
            ->findOrFail($this->inventoryId);

        $this->authorize('view', $inventory);
        $availableCompositeIds = $inventory->availableCompositeMaterialIds();

        $balancesBaseQuery = DB::table('materials')
            ->join('inventory_material', function ($join) use ($inventory): void {
                $join
                    ->on('inventory_material.material_id', '=', 'materials.id')
                    ->where('inventory_material.inventory_id', '=', $inventory->id);
            })
            ->when($this->materialSearch !== '', function ($query): void {
                $query->where('materials.name', 'like', '%'.$this->materialSearch.'%');
            });

        $simpleBalances = (clone $balancesBaseQuery)
            ->select([
                'materials.id',
                'materials.name',
                'materials.photo',
                'materials.price',
                'materials.type',
                'materials.minimum_stock',
                'materials.is_active',
                DB::raw('COALESCE(inventory_material.current_quantity, 0) as current_quantity'),
                DB::raw('COALESCE(inventory_material.received_items, 0) as received_items'),
                DB::raw('COALESCE(inventory_material.lost_items, 0) as lost_items'),
            ])
            ->where('materials.type', 'simple')
            ->orderByRaw('CASE WHEN COALESCE(inventory_material.current_quantity, 0) < materials.minimum_stock AND materials.minimum_stock > 0 THEN 0 ELSE 1 END')
            ->orderBy('materials.name')
            ->get();

        $compositeBalances = DB::table('materials')
            ->leftJoin('inventory_material', function ($join) use ($inventory): void {
                $join
                    ->on('inventory_material.material_id', '=', 'materials.id')
                    ->where('inventory_material.inventory_id', '=', $inventory->id);
            })
            ->when($this->materialSearch !== '', function ($query): void {
                $query->where('materials.name', 'like', '%'.$this->materialSearch.'%');
            })
            ->select([
                'materials.id',
                'materials.name',
                'materials.photo',
                'materials.price',
                'materials.type',
                'materials.minimum_stock',
                'materials.is_active',
                DB::raw('COALESCE(inventory_material.current_quantity, 0) as current_quantity'),
                DB::raw('COALESCE(inventory_material.received_items, 0) as received_items'),
                DB::raw('COALESCE(inventory_material.lost_items, 0) as lost_items'),
            ])
            ->selectSub(
                DB::table('material_components')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('material_components.parent_material_id', 'materials.id'),
                'components_count',
            )
            ->where('materials.type', 'composite')
            ->whereIn('materials.id', $availableCompositeIds === [] ? [0] : $availableCompositeIds)
            ->orderByRaw('CASE WHEN COALESCE(inventory_material.current_quantity, 0) < materials.minimum_stock AND materials.minimum_stock > 0 THEN 0 ELSE 1 END')
            ->orderBy('materials.name')
            ->get();

        $compositeBalances = $this->appendComposableQuantity($compositeBalances, $inventory->id);

        $lowStockItems = DB::table('materials')
            ->where('materials.minimum_stock', '>', 0)
            ->leftJoin('inventory_material', function ($join) use ($inventory): void {
                $join
                    ->on('inventory_material.material_id', '=', 'materials.id')
                    ->where('inventory_material.inventory_id', '=', $inventory->id);
            })
            ->where(function ($query) use ($availableCompositeIds): void {
                $query
                    ->where(function ($simpleQuery): void {
                        $simpleQuery
                            ->where('materials.type', 'simple')
                            ->whereNotNull('inventory_material.inventory_id');
                    });

                if ($availableCompositeIds !== []) {
                    $query->orWhere(function ($compositeQuery) use ($availableCompositeIds): void {
                        $compositeQuery
                            ->where('materials.type', 'composite')
                            ->whereIn('materials.id', $availableCompositeIds);
                    });
                }
            })
            ->select([
                'materials.id',
                'materials.name',
                'materials.type',
                'materials.minimum_stock',
                DB::raw('COALESCE(inventory_material.current_quantity, 0) as current_quantity'),
            ])
            ->orderBy('materials.name')
            ->get();

        $lowStockItems = $this->appendComposableQuantityToCollection($lowStockItems, $inventory->id)
            ->map(function (object $item): object {
                $item->available_alert_quantity = $item->type === 'composite'
                    ? (int) ($item->composable_quantity ?? 0)
                    : (int) $item->current_quantity;

                return $item;
            })
            ->filter(fn (object $item): bool => $item->available_alert_quantity < (int) $item->minimum_stock)
            ->take(5)
            ->values();

        $movements = StockMovement::query()
            ->with(['material:id,name,type', 'user:id,name'])
            ->where('inventory_id', $inventory->id)
            ->when($this->movementTypeFilter !== '', fn ($query) => $query->where('movement_type', $this->movementTypeFilter))
            ->latest()
            ->paginate(5, pageName: 'movementsPage');

        return view('livewire.pages.app.teacher.inventory.view', [
            'inventory' => $inventory,
            'simpleBalances' => $simpleBalances,
            'compositeBalances' => $compositeBalances,
            'lowStockItems' => $lowStockItems,
            'movements' => $movements,
            'movementTypeOptions' => [
                ['value' => '', 'label' => __('Todos os movimentos')],
                ['value' => StockMovement::TYPE_ENTRY, 'label' => __('Entrada')],
                ['value' => StockMovement::TYPE_EXIT, 'label' => __('Saída')],
                ['value' => StockMovement::TYPE_TRANSFER_IN, 'label' => __('Transferência recebida')],
                ['value' => StockMovement::TYPE_TRANSFER_OUT, 'label' => __('Transferência enviada')],
                ['value' => StockMovement::TYPE_ADJUSTMENT, 'label' => __('Ajuste')],
                ['value' => StockMovement::TYPE_LOSS, 'label' => __('Perda/Avaria')],
                ['value' => StockMovement::TYPE_KIT_COMPONENT_EXIT, 'label' => __('Baixa de componente')],
            ],
        ]);
    }

    private function appendComposableQuantity(Collection $compositeBalances, int $inventoryId): Collection
    {
        return $this->appendComposableQuantityToCollection($compositeBalances, $inventoryId);
    }

    private function appendComposableQuantityToCollection($items, int $inventoryId)
    {
        $compositeIds = collect($items)
            ->where('type', 'composite')
            ->pluck('id')
            ->map(fn (mixed $id): int => (int) $id)
            ->all();

        if ($compositeIds === []) {
            return collect($items)->map(function (object $item): object {
                $item->composable_quantity = 0;

                return $item;
            });
        }

        $componentStocks = DB::table('material_components')
            ->leftJoin('inventory_material', function ($join) use ($inventoryId): void {
                $join
                    ->on('inventory_material.material_id', '=', 'material_components.component_material_id')
                    ->where('inventory_material.inventory_id', '=', $inventoryId);
            })
            ->whereIn('material_components.parent_material_id', $compositeIds)
            ->select([
                'material_components.parent_material_id',
                'material_components.quantity',
                DB::raw('COALESCE(inventory_material.current_quantity, 0) as component_current_quantity'),
            ])
            ->get()
            ->groupBy('parent_material_id');

        return collect($items)->map(function (object $item) use ($componentStocks): object {
            if ($item->type !== 'composite') {
                $item->composable_quantity = 0;

                return $item;
            }

            $components = $componentStocks->get($item->id, collect());
            $item->composable_quantity = $components->isEmpty()
                ? 0
                : (int) floor($components->min(fn (object $component): float => ((int) $component->component_current_quantity) / max((int) $component->quantity, 1)));

            return $item;
        });
    }
}
