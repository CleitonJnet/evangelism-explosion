<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Receipt extends Model
{
    use HasFactory;
    protected $filable = ['file','training_id'];

    public function training(){
        return $this->belongsTo(Training::class);
    }
}
