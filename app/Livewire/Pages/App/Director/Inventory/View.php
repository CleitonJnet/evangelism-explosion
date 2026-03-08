<?php

namespace App\Livewire\Pages\App\Director\Inventory;

use App\Models\Inventory;
use App\Models\StockMovement;
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

    public string $materialTypeFilter = '';

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

    public function updatingMaterialSearch(): void
    {
        $this->resetPage('balancesPage');
    }

    public function updatingMaterialTypeFilter(): void
    {
        $this->resetPage('balancesPage');
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

        $balancesBaseQuery = DB::table('inventory_material')
            ->join('materials', 'materials.id', '=', 'inventory_material.material_id')
            ->where('inventory_material.inventory_id', $inventory->id)
            ->when($this->materialSearch !== '', function ($query): void {
                $query->where('materials.name', 'like', '%'.$this->materialSearch.'%');
            })
            ->when($this->materialTypeFilter !== '', fn ($query) => $query->where('materials.type', $this->materialTypeFilter));

        $balances = (clone $balancesBaseQuery)
            ->select([
                'materials.id',
                'materials.name',
                'materials.type',
                'materials.minimum_stock',
                'materials.is_active',
                'inventory_material.current_quantity',
                'inventory_material.received_items',
                'inventory_material.lost_items',
            ])
            ->orderByRaw('CASE WHEN inventory_material.current_quantity < materials.minimum_stock AND materials.minimum_stock > 0 THEN 0 ELSE 1 END')
            ->orderBy('materials.name')
            ->paginate(10, pageName: 'balancesPage');

        $lowStockItems = DB::table('inventory_material')
            ->join('materials', 'materials.id', '=', 'inventory_material.material_id')
            ->where('inventory_material.inventory_id', $inventory->id)
            ->where('materials.minimum_stock', '>', 0)
            ->whereColumn('inventory_material.current_quantity', '<', 'materials.minimum_stock')
            ->select([
                'materials.id',
                'materials.name',
                'materials.minimum_stock',
                'inventory_material.current_quantity',
            ])
            ->orderBy('materials.name')
            ->limit(5)
            ->get();

        $movements = StockMovement::query()
            ->with(['material:id,name,type', 'user:id,name'])
            ->where('inventory_id', $inventory->id)
            ->when($this->movementTypeFilter !== '', fn ($query) => $query->where('movement_type', $this->movementTypeFilter))
            ->latest()
            ->paginate(10, pageName: 'movementsPage');

        return view('livewire.pages.app.director.inventory.view', [
            'inventory' => $inventory,
            'balances' => $balances,
            'lowStockItems' => $lowStockItems,
            'movements' => $movements,
            'materialTypeOptions' => [
                ['value' => '', 'label' => __('Todos os tipos')],
                ['value' => 'simple', 'label' => __('Simples')],
                ['value' => 'composite', 'label' => __('Composto')],
            ],
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
}
