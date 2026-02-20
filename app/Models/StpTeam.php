<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StpTeam extends Model
{
    use HasFactory;

    protected $fillable = [
        'stp_session_id',
        'mentor_user_id',
        'name',
        'position',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'position' => 'integer',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(StpSession::class, 'stp_session_id');
    }

    public function mentor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mentor_user_id');
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'stp_team_students')
            ->withPivot('position')
            ->withTimestamps();
    }

    public function approaches(): HasMany
    {
        return $this->hasMany(StpApproach::class);
    }
}
