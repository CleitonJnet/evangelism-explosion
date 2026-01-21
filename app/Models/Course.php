<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = ['order', 'type', 'initials', 'name', 'slogan', 'learnMoreLink', 'certificate', 'color',  'price', 'description', 'targetAudience', 'knowhow', 'logo', 'banner', 'ministry_id' ];

    public function ministry(){
        return $this->belongsTo(Ministry::class);
    }

    public function sections(){
        return $this->hasMany(Section::class);
    }

    public function teachers(){
        return $this->belongsToMany(User::class)->withPivot('status');
    }

    public function lessonplan(){
        return $this->hasMany(Lessonplan::class);
    }
}
