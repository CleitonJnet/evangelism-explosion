<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OjtSession extends Model
{
    /** @use HasFactory<\Database\Factories\OjtSessionFactory> */
    use HasFactory;

    protected $fillable = [
        'training_id',
        'date',
        'starts_at',
        'ends_at',
        'week_number',
        'status',
        'meta',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'week_number' => 'integer',
            'meta' => 'array',
        ];
    }

    public function training(): BelongsTo
    {
        return $this->belongsTo(Training::class);
    }

    public function teams(): HasMany
    {
        return $this->hasMany(OjtTeam::class);
    }
}
