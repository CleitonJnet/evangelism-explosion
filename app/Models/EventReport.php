<?php

namespace App\Models;

use App\Enums\EventReportStatus;
use App\Enums\EventReportType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventReport extends Model
{
    protected $fillable = [
        'training_id',
        'church_id',
        'created_by_user_id',
        'updated_by_user_id',
        'submitted_by_user_id',
        'last_reviewed_by_user_id',
        'type',
        'status',
        'schema_version',
        'title',
        'summary',
        'context',
        'meta',
        'submitted_at',
        'review_requested_at',
        'reviewed_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => EventReportType::class,
            'status' => EventReportStatus::class,
            'schema_version' => 'integer',
            'context' => 'array',
            'meta' => 'array',
            'submitted_at' => 'datetime',
            'review_requested_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function training(): BelongsTo
    {
        return $this->belongsTo(Training::class);
    }

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by_user_id');
    }

    public function lastReviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_reviewed_by_user_id');
    }

    public function sections(): HasMany
    {
        return $this->hasMany(EventReportSection::class)->orderBy('position')->orderBy('id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(EventReportReview::class)->latest('reviewed_at')->latest('id');
    }

    public function isChurchReport(): bool
    {
        return $this->type === EventReportType::Church;
    }

    public function isTeacherReport(): bool
    {
        return $this->type === EventReportType::Teacher;
    }
}
