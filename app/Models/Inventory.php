<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
}
