<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shipping extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'phone', 'email', 'street', 'number', 'complement', 'district', 'city', 'state', 'postal_code', 'notes'];

    public function materials()
    {
        return $this->belongsToMany(Material::class);
    }
}
