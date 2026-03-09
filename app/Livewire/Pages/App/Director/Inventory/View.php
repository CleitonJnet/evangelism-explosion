<?php

namespace App\Livewire\Pages\App\Director\Inventory;

use App\Models\Inventory;
use App\Models\StockMovement;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View as ViewView;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class View extends Component
{
    use WithPagination;

    public int $inventoryId;

    public string $materialSearch = '';

    public string $movementTypeFilter = '';

    public function mount(Inventory $inventory): void
    {
        $this->inventoryId = $inventory->id;
    }

    #[On('director-inventory-updated')]
    #[On('director-inventory-stock-updated')]
    public function refreshInventory(int $inventoryId): void
    {
        if ($this->inventoryId !== $inventoryId) {
            return;
        }
    }

    #[On('director-material-updated')]
    public function refreshMaterial(?int $materialId = null): void {}

    #[On('director-material-created')]
    public function refreshInventoryAfterMaterialCreation(?int $materialId = null): void
    {
        if ($materialId !== null) {
            $this->resetPage('simpleBalancesPage');
            $this->resetPage('compositeBalancesPage');
        }
    }

    #[On('director-material-deleted')]
    public function refreshInventoryAfterMaterialDeletion(?int $materialId = null): void
    {
        if ($materialId !== null) {
            $this->resetPage('simpleBalancesPage');
            $this->resetPage('compositeBalancesPage');
        }
    }

    public function updatingMaterialSearch(): void
    {
        $this->resetPage('simpleBalancesPage');
        $this->resetPage('compositeBalancesPage');
    }

    public function updatingMovementTypeFilter(): void
    {
        $this->resetPage('movementsPage');
    }

    public function render(): ViewView
    {
        $inventory = Inventory::query()
            ->with('responsibleUser:id,name')
            ->findOrFail($this->inventoryId);

        $balancesBaseQuery = DB::table('materials')
            ->leftJoin('inventory_material', function ($join) use ($inventory): void {
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
            ->paginate(10, pageName: 'simpleBalancesPage');

        $compositeBalances = (clone $balancesBaseQuery)
            ->select([
                'materials.id',
                'materials.name',
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
            ->orderByRaw('CASE WHEN COALESCE(inventory_material.current_quantity, 0) < materials.minimum_stock AND materials.minimum_stock > 0 THEN 0 ELSE 1 END')
            ->orderBy('materials.name')
            ->paginate(10, pageName: 'compositeBalancesPage');

        $compositeBalances = $this->appendComposableQuantity($compositeBalances, $inventory->id);

        $lowStockItems = DB::table('materials')
            ->leftJoin('inventory_material', function ($join) use ($inventory): void {
                $join
                    ->on('inventory_material.material_id', '=', 'materials.id')
                    ->where('inventory_material.inventory_id', '=', $inventory->id);
            })
            ->where('materials.minimum_stock', '>', 0)
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

        return view('livewire.pages.app.director.inventory.view', [
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

    private function appendComposableQuantity(LengthAwarePaginator $compositeBalances, int $inventoryId): LengthAwarePaginator
    {
        $compositeBalances->setCollection(
            $this->appendComposableQuantityToCollection($compositeBalances->getCollection(), $inventoryId),
        );

        return $compositeBalances;
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

            $components = $componentStocks->get($item->id);

            $item->composable_quantity = $components === null || $components->isEmpty()
                ? 0
                : (int) $components->map(function (object $component): int {
                    $requiredQuantity = (int) $component->quantity;

                    if ($requiredQuantity <= 0) {
                        return 0;
                    }

                    return intdiv((int) $component->component_current_quantity, $requiredQuantity);
                })->min();

            return $item;
        });
    }
}
