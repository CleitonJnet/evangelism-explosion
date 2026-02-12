<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = ['date', 'time_start', 'time_end', 'training_id', 'section_id'];

    public function training()
    {
        return $this->belongsTo(Training::class);
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }
}
