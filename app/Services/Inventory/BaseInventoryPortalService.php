<?php

namespace App\Services\Inventory;

use App\Models\Inventory;
use App\Models\StockMovement;
use App\Models\Training;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BaseInventoryPortalService
{
    /**
     * @return array<string, mixed>
     */
    public function build(User $user): array
    {
        $inventories = $this->baseInventories($user);
        $inventoryIds = $inventories->pluck('id')->map(fn (mixed $id): int => (int) $id)->all();
        $items = $this->items($inventoryIds);
        $recentEntries = $this->recentEntries($inventoryIds);
        $eventUsage = $this->eventUsage($inventoryIds);
        $alerts = $this->alerts($items);
        $upcomingNeeds = $this->upcomingNeeds($user, $inventoryIds);

        return [
            'inventories' => $inventories->map(fn (Inventory $inventory): array => [
                'id' => $inventory->id,
                'name' => $inventory->name,
                'status' => $inventory->is_active ? 'Ativo' : 'Inativo',
                'responsible' => $inventory->responsibleUser?->name ?: 'Responsavel nao informado',
                'location' => trim(implode(' / ', array_filter([$inventory->city, $inventory->state]))) ?: 'Local nao informado',
                'low_stock_count' => $alerts->where('inventory_id', $inventory->id)->count(),
            ])->values()->all(),
            'items' => $items->all(),
            'recentEntries' => $recentEntries->all(),
            'eventUsage' => $eventUsage->all(),
            'alerts' => $alerts->values()->all(),
            'upcomingNeeds' => $upcomingNeeds->all(),
            'summary' => [
                'inventories_count' => $inventories->count(),
                'materials_count' => $items->count(),
                'low_stock_count' => $alerts->count(),
                'recent_entries_count' => $recentEntries->count(),
                'event_usage_count' => $eventUsage->count(),
                'upcoming_needs_count' => $upcomingNeeds->count(),
            ],
        ];
    }

    /**
     * @return Collection<int, Inventory>
     */
    private function baseInventories(User $user): Collection
    {
        if (! $user->church_id) {
            return collect();
        }

        return Inventory::query()
            ->with(['responsibleUser:id,name', 'church:id,name'])
            ->where('kind', 'base')
            ->where('church_id', $user->church_id)
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
                'materials.id as material_id',
                'materials.name as material_name',
                'materials.type',
                'materials.minimum_stock',
                'materials.is_active',
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
                'material_id' => (int) $item->material_id,
                'material_name' => $item->material_name,
                'type' => $item->type,
                'minimum_stock' => (int) $item->minimum_stock,
                'current_quantity' => (int) $item->current_quantity,
                'received_items' => (int) $item->received_items,
                'lost_items' => (int) $item->lost_items,
                'is_active' => (bool) $item->is_active,
                'needs_restock' => (int) $item->minimum_stock > 0 && (int) $item->current_quantity < (int) $item->minimum_stock,
            ]);
    }

    /**
     * @param  array<int, int>  $inventoryIds
     * @return Collection<int, array<string, mixed>>
     */
    private function recentEntries(array $inventoryIds): Collection
    {
        if ($inventoryIds === []) {
            return collect();
        }

        return StockMovement::query()
            ->with(['inventory:id,name', 'material:id,name', 'user:id,name'])
            ->whereIn('inventory_id', $inventoryIds)
            ->whereIn('movement_type', [StockMovement::TYPE_ENTRY, StockMovement::TYPE_TRANSFER_IN, StockMovement::TYPE_ADJUSTMENT])
            ->latest()
            ->limit(12)
            ->get()
            ->map(fn (StockMovement $movement): array => [
                'inventory_name' => $movement->inventory?->name ?? 'Estoque da base',
                'material_name' => $movement->material?->name ?? 'Material removido',
                'type_label' => $movement->typeLabel(),
                'quantity' => (int) $movement->quantity,
                'balance_after' => (int) ($movement->balance_after ?? 0),
                'actor' => $movement->user?->name ?: 'Sistema',
                'notes' => $movement->notes,
                'created_at' => $movement->created_at?->format('d/m/Y H:i'),
            ]);
    }

    /**
     * @param  array<int, int>  $inventoryIds
     * @return Collection<int, array<string, mixed>>
     */
    private function eventUsage(array $inventoryIds): Collection
    {
        if ($inventoryIds === []) {
            return collect();
        }

        return StockMovement::query()
            ->join('trainings', 'trainings.id', '=', 'stock_movements.training_id')
            ->leftJoin('courses', 'courses.id', '=', 'trainings.course_id')
            ->leftJoin('churches', 'churches.id', '=', 'trainings.church_id')
            ->whereIn('stock_movements.inventory_id', $inventoryIds)
            ->whereNotNull('stock_movements.training_id')
            ->whereIn('stock_movements.movement_type', [
                StockMovement::TYPE_EXIT,
                StockMovement::TYPE_KIT_COMPONENT_EXIT,
                StockMovement::TYPE_LOSS,
            ])
            ->groupBy('stock_movements.training_id', 'courses.type', 'courses.name', 'churches.name')
            ->selectRaw('stock_movements.training_id')
            ->selectRaw("COALESCE(courses.type, 'Treinamento') as course_type")
            ->selectRaw("COALESCE(courses.name, 'Evento') as course_name")
            ->selectRaw("COALESCE(churches.name, 'Base nao informada') as church_name")
            ->selectRaw('SUM(stock_movements.quantity) as total_quantity')
            ->selectRaw('COUNT(DISTINCT stock_movements.material_id) as materials_count')
            ->selectRaw('MAX(stock_movements.created_at) as last_movement_at')
            ->orderByDesc('last_movement_at')
            ->limit(10)
            ->get()
            ->map(fn (object $usage): array => [
                'training_id' => (int) $usage->training_id,
                'title' => trim($usage->course_type.' - '.$usage->course_name),
                'church_name' => $usage->church_name,
                'total_quantity' => (int) $usage->total_quantity,
                'materials_count' => (int) $usage->materials_count,
                'last_movement_at' => CarbonImmutable::parse($usage->last_movement_at)->format('d/m/Y H:i'),
                'route' => route('app.portal.base.trainings.show', ['training' => $usage->training_id]),
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
    private function upcomingNeeds(User $user, array $inventoryIds): Collection
    {
        if (! $user->church_id || $inventoryIds === []) {
            return collect();
        }

        $availableByMaterial = DB::table('inventory_material')
            ->whereIn('inventory_id', $inventoryIds)
            ->groupBy('material_id')
            ->pluck(DB::raw('SUM(current_quantity)'), 'material_id');

        return Training::query()
            ->with([
                'course.materials:id,name,minimum_stock',
                'eventDates' => fn ($query) => $query->orderBy('date')->orderBy('start_time'),
            ])
            ->where('church_id', $user->church_id)
            ->whereHas('eventDates', fn (Builder $query) => $query->whereDate('date', '>=', CarbonImmutable::today()->toDateString()))
            ->orderBy('id')
            ->get()
            ->map(function (Training $training) use ($availableByMaterial): array {
                $missingMaterials = $training->course?->materials
                    ->map(function ($material) use ($availableByMaterial): ?array {
                        $available = (int) ($availableByMaterial[$material->id] ?? 0);

                        if ($available > 0) {
                            return null;
                        }

                        return [
                            'name' => $material->name,
                            'available' => $available,
                            'minimum_stock' => (int) $material->minimum_stock,
                        ];
                    })
                    ->filter()
                    ->values()
                    ->all() ?? [];

                return [
                    'training_id' => $training->id,
                    'title' => trim(((string) $training->course?->type ? $training->course?->type.' - ' : '').($training->course?->name ?? 'Treinamento')),
                    'first_date' => ($training->eventDates->first()?->date ? CarbonImmutable::parse($training->eventDates->first()->date)->format('d/m/Y') : null),
                    'missing_materials' => $missingMaterials,
                    'route' => route('app.portal.base.trainings.materials', $training),
                ];
            })
            ->filter(fn (array $training): bool => $training['missing_materials'] !== [])
            ->values();
    }
}
