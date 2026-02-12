<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainingScheduleItem extends Model
{
    /** @use HasFactory<\Database\Factories\TrainingScheduleItemFactory> */
    use HasFactory;

    protected $fillable = [
        'training_id',
        'section_id',
        'date',
        'starts_at',
        'ends_at',
        'type',
        'title',
        'position',
        'planned_duration_minutes',
        'suggested_duration_minutes',
        'min_duration_minutes',
        'origin',
        'status',
        'conflict_reason',
        'meta',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'position' => 'int',
            'conflict_reason' => 'array',
            'meta' => 'array',
        ];
    }

    public function training(): BelongsTo
    {
        return $this->belongsTo(Training::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }
}
