<?php

namespace App\Models;

use App\Helpers\MoneyHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

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

    public function studyCourses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'course_study_material')->withTimestamps();
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

    /**
     * @return array<int, string>
     */
    public function deletionBlockers(): array
    {
        $blockers = [];

        $hasStock = DB::table('inventory_material')
            ->where('material_id', $this->id)
            ->where(function ($query): void {
                $query
                    ->where('current_quantity', '>', 0)
                    ->orWhere('received_items', '>', 0)
                    ->orWhere('lost_items', '>', 0);
            })
            ->exists();

        if ($hasStock) {
            $blockers[] = __('Este item possui saldo ou histórico consolidado em estoque.');
        }

        if ($this->stockMovements()->exists()) {
            $blockers[] = __('Este item já possui movimentações registradas.');
        }

        if ($this->components()->exists() || $this->parentCompositions()->exists()) {
            $blockers[] = __('Este item participa da composição de materiais compostos.');
        }

        if ($this->courses()->exists() || $this->studyCourses()->exists()) {
            $blockers[] = __('Este item está vinculado a cursos.');
        }

        if ($this->suppliers()->exists()) {
            $blockers[] = __('Este item está vinculado a fornecedores.');
        }

        return $blockers;
    }

    public function canBeDeletedPermanently(): bool
    {
        return $this->deletionBlockers() === [];
    }
}
