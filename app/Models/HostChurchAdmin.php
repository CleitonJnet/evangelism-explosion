<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HostChurchAdmin extends Model
{
    protected $fillable = [
        'host_church_id',
        'user_id',
        'certified_at',
        'status',
    ];

    protected $casts = [
        'certified_at' => 'date',
    ];

    public function hostChurch(): BelongsTo
    {
        return $this->belongsTo(HostChurch::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
