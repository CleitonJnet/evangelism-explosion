<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    use HasFactory;

    protected $fillable = ['training_id', 'name', 'value'];

    public function training()
    {
        return $this->belongsTo(Training::class);
    }
}
