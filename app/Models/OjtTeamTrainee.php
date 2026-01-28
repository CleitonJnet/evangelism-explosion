<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OjtTeamTrainee extends Model
{
    /** @use HasFactory<\Database\Factories\OjtTeamTraineeFactory> */
    use HasFactory;

    protected $fillable = [
        'ojt_team_id',
        'trainee_id',
        'order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'order' => 'integer',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(OjtTeam::class, 'ojt_team_id');
    }

    public function trainee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'trainee_id');
    }
}
