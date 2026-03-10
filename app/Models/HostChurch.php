<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    public function admins(): HasMany
    {
        return $this->hasMany(HostChurchAdmin::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'host_church_admins')
            ->withPivot(['certified_at', 'status'])
            ->withTimestamps();
    }
}
