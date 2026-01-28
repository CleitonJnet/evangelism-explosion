<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OjtTeam extends Model
{
    /** @use HasFactory<\Database\Factories\OjtTeamFactory> */
    use HasFactory;

    protected $fillable = [
        'ojt_session_id',
        'mentor_id',
        'team_number',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'team_number' => 'integer',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(OjtSession::class, 'ojt_session_id');
    }

    public function mentor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mentor_id');
    }

    public function trainees(): HasMany
    {
        return $this->hasMany(OjtTeamTrainee::class);
    }

    public function report(): HasOne
    {
        return $this->hasOne(OjtReport::class);
    }
}
