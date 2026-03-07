<?php

namespace App\Models;

use App\Helpers\MoneyHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'photo', 'status', 'price', 'description'];

    public function getPriceAttribute(string|int|float|null $value): ?string
    {
        return MoneyHelper::formatInput($value);
    }

    public function setPriceAttribute(string|int|float|null $value): void
    {
        $this->attributes['price'] = MoneyHelper::toDatabase($value);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function shippings()
    {
        return $this->belongsToMany(Shipping::class);
    }

    public function Inventories()
    {
        return $this->belongsToMany(Inventory::class)->withPivot('received_items', 'current_quantity', 'lost_items');
    }

    public function suppliers()
    {
        return $this->belongsToMany(Supplier::class);
    }
}
