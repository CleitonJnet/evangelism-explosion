<?php

namespace App\Models;

use App\Enums\StpApproachResult;
use App\Enums\StpApproachStatus;
use App\Enums\StpApproachType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

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

    /**
     * @return array{
     *     status: ?string,
     *     started_at: ?string,
     *     completed_at: ?string,
     *     sessions_planned: int,
     *     sessions_completed: int,
     *     next_step: ?string,
     *     next_step_registered_at: ?string,
     *     local_church_referral_at: ?string,
     *     follow_up_pending: bool
     * }
     */
    public function discipleshipPayload(): array
    {
        $payload = data_get($this->payload, 'discipleship', []);

        if (! is_array($payload)) {
            $payload = [];
        }

        return [
            'status' => $this->stringOrNull($payload['status'] ?? null),
            'started_at' => $this->dateTimeStringOrNull($payload['started_at'] ?? null),
            'completed_at' => $this->dateTimeStringOrNull($payload['completed_at'] ?? null),
            'sessions_planned' => max(0, (int) ($payload['sessions_planned'] ?? 0)),
            'sessions_completed' => max(0, (int) ($payload['sessions_completed'] ?? 0)),
            'next_step' => $this->stringOrNull($payload['next_step'] ?? null),
            'next_step_registered_at' => $this->dateTimeStringOrNull($payload['next_step_registered_at'] ?? null),
            'local_church_referral_at' => $this->dateTimeStringOrNull($payload['local_church_referral_at'] ?? null),
            'follow_up_pending' => (bool) ($payload['follow_up_pending'] ?? false),
        ];
    }

    public function hasDiscipleshipTrack(): bool
    {
        $payload = $this->discipleshipPayload();

        return $payload['status'] !== null
            || $payload['started_at'] !== null
            || $payload['completed_at'] !== null
            || $payload['sessions_planned'] > 0
            || $payload['sessions_completed'] > 0
            || $payload['next_step'] !== null
            || $payload['local_church_referral_at'] !== null
            || $payload['follow_up_pending'] === true;
    }

    private function stringOrNull(mixed $value): ?string
    {
        $stringValue = trim((string) $value);

        return $stringValue !== '' ? $stringValue : null;
    }

    private function dateTimeStringOrNull(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof Carbon) {
            return $value->toDateTimeString();
        }

        try {
            return Carbon::parse((string) $value)->toDateTimeString();
        } catch (\Throwable) {
            return null;
        }
    }
}
