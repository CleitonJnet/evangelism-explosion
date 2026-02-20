<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StpSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'training_id',
        'sequence',
        'label',
        'starts_at',
        'ends_at',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sequence' => 'integer',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function training(): BelongsTo
    {
        return $this->belongsTo(Training::class);
    }

    public function teams(): HasMany
    {
        return $this->hasMany(StpTeam::class);
    }

    public function approaches(): HasMany
    {
        return $this->hasMany(StpApproach::class);
    }
}
