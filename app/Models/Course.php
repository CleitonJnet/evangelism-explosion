<?php

namespace App\Models;

use App\Helpers\MoneyHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function ministry(): BelongsTo
    {
        return $this->belongsTo(Ministry::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class);
    }

    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot('status');
    }

    public function lessonplan(): HasMany
    {
        return $this->hasMany(Lessonplan::class);
    }

    public function materials(): BelongsToMany
    {
        return $this->belongsToMany(Material::class)->withTimestamps();
    }
}
