<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChurchTemp extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'pastor', 'email', 'phone', 'street', 'number', 'complement', 'district', 'city', 'postal_code', 'contact', 'contact_phone', 'contact_email', 'notes', 'logo', 'state', 'status', 'normalized_name', 'resolved_church_id', 'resolved_by', 'resolved_at'];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
