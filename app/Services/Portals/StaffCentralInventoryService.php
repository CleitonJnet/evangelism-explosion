<?php

namespace App\Services\Portals;

use App\Models\Inventory;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StaffCentralInventoryService
{
    /**
     * @return array<string, mixed>
     */
    public function build(User $user): array
    {
        $inventories = $this->centralInventories();
        $inventoryIds = $inventories->pluck('id')->map(fn (mixed $id): int => (int) $id)->all();
        $items = $this->items($inventoryIds);
        $alerts = $this->alerts($items);
        $recentMovements = $this->recentMovements($inventoryIds);

        return [
            'summary' => [
                'inventories_count' => $inventories->count(),
                'materials_count' => $items->count(),
                'low_stock_count' => $alerts->count(),
                'movements_count' => $recentMovements->count(),
                'active_inventory_count' => $inventories->where('is_active', true)->count(),
            ],
            'inventories' => $inventories
                ->map(fn (Inventory $inventory): array => [
                    'id' => $inventory->id,
                    'name' => $inventory->name,
                    'status' => $inventory->is_active ? 'Ativo' : 'Inativo',
                    'responsible' => $inventory->responsibleUser?->name ?: 'Responsavel nao informado',
                    'location' => trim(implode(' / ', array_filter([$inventory->city, $inventory->state]))) ?: 'Local nao informado',
                    'church_name' => $inventory->church?->name,
                    'materials_count' => $items->where('inventory_id', $inventory->id)->count(),
                    'low_stock_count' => $alerts->where('inventory_id', $inventory->id)->count(),
                    'legacy_route' => $user->can('access-director') ? route('app.director.inventory.show', $inventory) : null,
                ])
                ->values()
                ->all(),
            'items' => $items->all(),
            'alerts' => $alerts->values()->all(),
            'recent_movements' => $recentMovements->all(),
            'legacy_index_route' => $user->can('access-director') ? route('app.director.inventory.index') : null,
            'can_manage_directly' => $user->can('access-director'),
        ];
    }

    /**
     * @return Collection<int, Inventory>
     */
    private function centralInventories(): Collection
    {
        return Inventory::query()
            ->with(['responsibleUser:id,name', 'church:id,name'])
            ->where('kind', 'central')
            ->orderBy('name')
            ->get();
    }

    /**
     * @param  array<int, int>  $inventoryIds
     * @return Collection<int, array<string, mixed>>
     */
    private function items(array $inventoryIds): Collection
    {
        if ($inventoryIds === []) {
            return collect();
        }

        return DB::table('inventory_material')
            ->join('inventories', 'inventories.id', '=', 'inventory_material.inventory_id')
            ->join('materials', 'materials.id', '=', 'inventory_material.material_id')
            ->whereIn('inventory_material.inventory_id', $inventoryIds)
            ->select([
                'inventories.id as inventory_id',
                'inventories.name as inventory_name',
                'materials.name as material_name',
                'materials.type',
                'materials.minimum_stock',
                DB::raw('COALESCE(inventory_material.current_quantity, 0) as current_quantity'),
                DB::raw('COALESCE(inventory_material.received_items, 0) as received_items'),
                DB::raw('COALESCE(inventory_material.lost_items, 0) as lost_items'),
            ])
            ->orderBy('inventories.name')
            ->orderByRaw('CASE WHEN COALESCE(inventory_material.current_quantity, 0) < materials.minimum_stock AND materials.minimum_stock > 0 THEN 0 ELSE 1 END')
            ->orderBy('materials.name')
            ->get()
            ->map(fn (object $item): array => [
                'inventory_id' => (int) $item->inventory_id,
                'inventory_name' => $item->inventory_name,
                'material_name' => $item->material_name,
                'type' => $item->type,
                'minimum_stock' => (int) $item->minimum_stock,
                'current_quantity' => (int) $item->current_quantity,
                'received_items' => (int) $item->received_items,
                'lost_items' => (int) $item->lost_items,
                'needs_restock' => (int) $item->minimum_stock > 0 && (int) $item->current_quantity < (int) $item->minimum_stock,
            ]);
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $items
     * @return Collection<int, array<string, mixed>>
     */
    private function alerts(Collection $items): Collection
    {
        return $items
            ->filter(fn (array $item): bool => $item['needs_restock'])
            ->sortBy([
                ['inventory_name', 'asc'],
                ['material_name', 'asc'],
            ])
            ->map(fn (array $item): array => [
                'inventory_id' => $item['inventory_id'],
                'inventory_name' => $item['inventory_name'],
                'material_name' => $item['material_name'],
                'current_quantity' => $item['current_quantity'],
                'minimum_stock' => $item['minimum_stock'],
                'gap' => max(0, $item['minimum_stock'] - $item['current_quantity']),
            ]);
    }

    /**
     * @param  array<int, int>  $inventoryIds
     * @return Collection<int, array<string, mixed>>
     */
    private function recentMovements(array $inventoryIds): Collection
    {
        if ($inventoryIds === []) {
            return collect();
        }

        return StockMovement::query()
            ->with(['inventory:id,name', 'material:id,name', 'user:id,name'])
            ->whereIn('inventory_id', $inventoryIds)
            ->latest()
            ->limit(12)
            ->get()
            ->map(fn (StockMovement $movement): array => [
                'inventory_name' => $movement->inventory?->name ?? 'Estoque central',
                'material_name' => $movement->material?->name ?? 'Material removido',
                'type_label' => $movement->typeLabel(),
                'type_classes' => $movement->typeBadgeClasses(),
                'quantity' => (int) $movement->quantity,
                'balance_after' => (int) ($movement->balance_after ?? 0),
                'actor' => $movement->user?->name ?: 'Sistema',
                'notes' => $movement->notes,
                'created_at' => $movement->created_at?->format('d/m/Y H:i'),
            ]);
    }
}
