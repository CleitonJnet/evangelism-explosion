<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OjtTrainingMentor extends Model
{
    /** @use HasFactory<\Database\Factories\OjtTrainingMentorFactory> */
    use HasFactory;

    protected $fillable = [
        'training_id',
        'mentor_id',
        'status',
    ];

    public function training(): BelongsTo
    {
        return $this->belongsTo(Training::class);
    }

    public function mentor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mentor_id');
    }
}
