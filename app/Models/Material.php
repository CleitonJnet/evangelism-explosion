<?php

namespace App\Models;

use App\Helpers\MoneyHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Material extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'type', 'photo', 'status', 'price', 'minimum_stock', 'is_active', 'description'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'minimum_stock' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function getPriceAttribute(string|int|float|null $value): ?string
    {
        return MoneyHelper::formatInput($value);
    }

    public function setPriceAttribute(string|int|float|null $value): void
    {
        $this->attributes['price'] = MoneyHelper::toDatabase($value);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    public function shippings(): BelongsToMany
    {
        return $this->belongsToMany(Shipping::class);
    }

    public function Inventories(): BelongsToMany
    {
        return $this->belongsToMany(Inventory::class)->withPivot('received_items', 'current_quantity', 'lost_items');
    }

    public function suppliers(): BelongsToMany
    {
        return $this->belongsToMany(Supplier::class, 'material_suppliers');
    }

    public function components(): HasMany
    {
        return $this->hasMany(MaterialComponent::class, 'parent_material_id');
    }

    public function parentCompositions(): HasMany
    {
        return $this->hasMany(MaterialComponent::class, 'component_material_id');
    }

    public function componentMaterials(): BelongsToMany
    {
        return $this->belongsToMany(Material::class, 'material_components', 'parent_material_id', 'component_material_id')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    public function parentMaterials(): BelongsToMany
    {
        return $this->belongsToMany(Material::class, 'material_components', 'component_material_id', 'parent_material_id')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class)->withTimestamps();
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function isComposite(): bool
    {
        return $this->type === 'composite';
    }

    public function typeLabel(): string
    {
        return $this->isComposite() ? __('Composto') : __('Simples');
    }

    public function typeBadgeClasses(): string
    {
        return $this->isComposite()
            ? 'bg-amber-100 text-amber-800'
            : 'bg-sky-100 text-sky-800';
    }
}
