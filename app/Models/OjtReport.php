<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OjtReport extends Model
{
    /** @use HasFactory<\Database\Factories\OjtReportFactory> */
    use HasFactory;

    protected $fillable = [
        'ojt_team_id',
        'contact_type',
        'contact_type_counts',
        'gospel_presentations',
        'listeners_count',
        'results_decisions',
        'results_interested',
        'results_rejection',
        'results_assurance',
        'follow_up_scheduled',
        'outline_participation',
        'lesson_learned',
        'public_report',
        'submitted_at',
        'is_locked',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'gospel_presentations' => 'integer',
            'listeners_count' => 'integer',
            'results_decisions' => 'integer',
            'results_interested' => 'integer',
            'results_rejection' => 'integer',
            'results_assurance' => 'integer',
            'follow_up_scheduled' => 'bool',
            'outline_participation' => 'array',
            'contact_type_counts' => 'array',
            'public_report' => 'array',
            'submitted_at' => 'datetime',
            'is_locked' => 'bool',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(OjtTeam::class, 'ojt_team_id');
    }
}
