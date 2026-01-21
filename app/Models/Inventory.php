<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;
    protected $fillable = ['name','phone','email','street','number','complement','district','city','state','postal_code','notes',];

    public function materials(){
        return $this->belongsToMany(Material::class)->withPivot('received_items','current_quantity','lost_items');
    }
}
