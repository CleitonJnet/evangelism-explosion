<?php

namespace App\Models;

use App\Enums\StpApproachResult;
use App\Enums\StpApproachStatus;
use App\Enums\StpApproachType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StpApproach extends Model
{
    use HasFactory;

    protected $fillable = [
        'training_id',
        'stp_session_id',
        'stp_team_id',
        'type',
        'status',
        'position',
        'person_name',
        'phone',
        'email',
        'street',
        'number',
        'complement',
        'district',
        'city',
        'state',
        'postal_code',
        'reference_point',
        'gospel_explained_times',
        'people_count',
        'result',
        'means_growth',
        'follow_up_scheduled_at',
        'public_q2_answer',
        'public_lesson',
        'created_by_user_id',
        'reported_by_user_id',
        'reviewed_by_user_id',
        'reviewed_at',
        'payload',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => StpApproachType::class,
            'status' => StpApproachStatus::class,
            'result' => StpApproachResult::class,
            'position' => 'integer',
            'payload' => 'array',
            'means_growth' => 'boolean',
            'follow_up_scheduled_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function training(): BelongsTo
    {
        return $this->belongsTo(Training::class);
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(StpSession::class, 'stp_session_id');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(StpTeam::class, 'stp_team_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by_user_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }
}
