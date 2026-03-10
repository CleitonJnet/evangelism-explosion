<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class Inventory extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'kind', 'phone', 'email', 'user_id', 'street', 'number', 'complement', 'district', 'city', 'state', 'postal_code', 'notes', 'is_active'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function materials(): BelongsToMany
    {
        return $this->belongsToMany(Material::class)->withPivot('received_items', 'current_quantity', 'lost_items');
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function responsibleUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function currentQuantityFor(Material|int $material): int
    {
        $materialId = $material instanceof Material ? $material->id : $material;

        return (int) DB::table('inventory_material')
            ->where('inventory_id', $this->id)
            ->where('material_id', $materialId)
            ->value('current_quantity');
    }

    public function hasAvailableStock(Material|int $material, int $quantity): bool
    {
        return $this->currentQuantityFor($material) >= $quantity;
    }

    public function hasActiveSimpleMaterialsWithStock(): bool
    {
        return $this->materials()
            ->where('materials.type', 'simple')
            ->where('materials.is_active', true)
            ->wherePivot('current_quantity', '>', 0)
            ->exists();
    }

    /**
     * @return array<int, int>
     */
    public function availableCompositeMaterialIds(): array
    {
        return DB::table('materials')
            ->where('materials.type', 'composite')
            ->whereExists(function ($query): void {
                $query->selectRaw('1')
                    ->from('material_components')
                    ->whereColumn('material_components.parent_material_id', 'materials.id');
            })
            ->whereNotExists(function ($query): void {
                $query->selectRaw('1')
                    ->from('material_components')
                    ->leftJoin('inventory_material', function ($join): void {
                        $join
                            ->on('inventory_material.material_id', '=', 'material_components.component_material_id')
                            ->where('inventory_material.inventory_id', '=', $this->id);
                    })
                    ->whereColumn('material_components.parent_material_id', 'materials.id')
                    ->whereRaw('COALESCE(inventory_material.current_quantity, 0) < material_components.quantity');
            })
            ->pluck('materials.id')
            ->map(fn (mixed $id): int => (int) $id)
            ->all();
    }

    /**
     * @return Collection<int, int>
     */
    public function teacherManageableMaterialIds(): Collection
    {
        return $this->materials()
            ->pluck('materials.id')
            ->map(fn (mixed $id): int => (int) $id)
            ->merge($this->availableCompositeMaterialIds())
            ->unique()
            ->values();
    }
}
