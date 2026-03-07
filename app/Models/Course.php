<?php

namespace App\Models;

use App\Helpers\MoneyHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = ['order', 'execution', 'min_stp_sessions', 'type', 'initials', 'name', 'slogan', 'learnMoreLink', 'certificate', 'color', 'price', 'description', 'targetAudience', 'knowhow', 'logo', 'banner', 'ministry_id'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'execution' => 'integer',
            'min_stp_sessions' => 'integer',
        ];
    }

    public function getPriceAttribute(string|int|float|null $value): ?string
    {
        return MoneyHelper::formatInput($value);
    }

    public function setPriceAttribute(string|int|float|null $value): void
    {
        $this->attributes['price'] = MoneyHelper::toDatabase($value);
    }

    public function ministry()
    {
        return $this->belongsTo(Ministry::class);
    }

    public function sections()
    {
        return $this->hasMany(Section::class);
    }

    public function teachers()
    {
        return $this->belongsToMany(User::class)->withPivot('status');
    }

    public function lessonplan()
    {
        return $this->hasMany(Lessonplan::class);
    }
}
