<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainingNewChurch extends Model
{
    use HasFactory;

    protected $fillable = [
        'training_id',
        'church_id',
        'source_church_temp_id',
        'created_by',
    ];

    public function training(): BelongsTo
    {
        return $this->belongsTo(Training::class);
    }

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    public function sourceChurchTemp(): BelongsTo
    {
        return $this->belongsTo(ChurchTemp::class, 'source_church_temp_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
