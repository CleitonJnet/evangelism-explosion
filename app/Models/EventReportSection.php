<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventReportSection extends Model
{
    protected $fillable = [
        'event_report_id',
        'key',
        'title',
        'position',
        'content',
        'meta',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'position' => 'integer',
            'content' => 'array',
            'meta' => 'array',
        ];
    }

    public function eventReport(): BelongsTo
    {
        return $this->belongsTo(EventReport::class);
    }
}
