<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    use HasFactory;

    protected $fillable = ['training_id', 'name', 'path_original', 'path_thumbnail', 'path_optimized', 'extension'];

    public function training()
    {
        return $this->belongsTo(Training::class);
    }
}
