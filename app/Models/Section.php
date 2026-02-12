<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    use HasFactory;

    protected $fillable = ['banner', 'name', 'order', 'duration', 'devotional', 'description', 'knowhow', 'course_id'];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function lessonplan()
    {
        return $this->hasMany(Lessonplan::class);
    }
}
