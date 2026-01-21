<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventDate extends Model
{
    protected $fillable = [
        'training_id',
        'date',
        'start_time',
        'end_time',
    ];

    public function training()
    {
        return $this->belongsTo(Training::class, 'training_id');
    }
}
