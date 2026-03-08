<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialComponent extends Model
{
    use HasFactory;

    protected $fillable = ['parent_material_id', 'component_material_id', 'quantity'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'parent_material_id' => 'integer',
            'component_material_id' => 'integer',
            'quantity' => 'integer',
        ];
    }

    public function parentMaterial(): BelongsTo
    {
        return $this->belongsTo(Material::class, 'parent_material_id');
    }

    public function componentMaterial(): BelongsTo
    {
        return $this->belongsTo(Material::class, 'component_material_id');
    }
}
