<?php

namespace App\Models;

use App\Enums\EventReportReviewOutcome;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventReportReview extends Model
{
    protected $fillable = [
        'event_report_id',
        'reviewer_user_id',
        'outcome',
        'comment',
        'payload',
        'reviewed_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'outcome' => EventReportReviewOutcome::class,
            'payload' => 'array',
            'reviewed_at' => 'datetime',
        ];
    }

    public function eventReport(): BelongsTo
    {
        return $this->belongsTo(EventReport::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_user_id');
    }
}
