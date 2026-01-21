<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HostChurch extends Model
{
    protected $fillable = [
        'church_id',
        'since_date',
        'notes',
    ];

    protected $casts = [
        'since_date' => 'date',
    ];

    // Igreja (registro “principal”)
    public function church()
    {
        return $this->belongsTo(Church::class);
    }

    // Registros da pivô (útil para auditoria/CRUD)
    public function admins()
    {
        return $this->hasMany(HostChurchAdmin::class);
    }

    // Usuários que são admins (atalho prático)
    public function users()
    {
        return $this->belongsToMany(User::class, 'host_church_admins')
            ->withPivot(['certified_at', 'status'])
            ->withTimestamps();
    }
}
