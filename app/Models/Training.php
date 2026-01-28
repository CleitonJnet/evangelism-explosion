<?php

namespace App\Models;

use App\Helpers\MoneyHelper;
use App\Helpers\PhoneHelper;
use App\Helpers\PostalCodeHelper;
use App\TrainingStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Training extends Model
{
    use HasFactory;

    public const OJT_POLICY_ROTATE = 'ROTATE';

    public const OJT_POLICY_FIXED = 'FIXED';

    protected $fillable = ['course_id', 'teacher_id', 'church_id', 'coordinator', 'banner', 'url', 'gpwhatsapp', 'phone', 'email', 'street', 'number', 'complement', 'district', 'city', 'state', 'postal_code', 'price', 'price_church', 'discount', 'kits', 'totStudents', 'totChurches', 'totNewChurches', 'totPastors', 'totKitsReceived', 'totKitsUsed', 'totApproaches', 'totDecisions', 'totListeners', 'notes', 'status', 'welcome_duration_minutes', 'ojt_count_override', 'ojt_policy_override'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => TrainingStatus::class,
            'welcome_duration_minutes' => 'integer',
            'ojt_count_override' => 'integer',
        ];
    }

    public function statusKey(): string
    {
        $status = $this->status;

        if ($status instanceof TrainingStatus) {
            return $status->key();
        }

        $statusEnum = TrainingStatus::tryFrom((int) $status);

        return $statusEnum?->key() ?? TrainingStatus::Planning->key();
    }

    public function getPhoneAttribute(?string $value): ?string
    {
        return PhoneHelper::format_phone($value);
    }

    public function getPostalCodeAttribute(?string $value): ?string
    {
        return PostalCodeHelper::format_postalcode($value);
    }

    public function getPriceAttribute(string|int|float|null $value): ?string
    {
        return MoneyHelper::format_money($value);
    }

    public function getPriceChurchAttribute(string|int|float|null $value): ?string
    {
        return MoneyHelper::format_money($value);
    }

    public function getPaymentAttribute(): ?string
    {
        $rawPrice = $this->attributes['price'] ?? $this->getRawOriginal('price');
        $rawPriceChurch = $this->attributes['price_church'] ?? $this->getRawOriginal('price_church');
        $rawDiscount = $this->attributes['discount'] ?? $this->getRawOriginal('discount');

        $price = MoneyHelper::toFloat($rawPrice);
        $priceChurch = MoneyHelper::toFloat($rawPriceChurch);
        $discount = MoneyHelper::toFloat($rawDiscount);

        if ($price === null && $priceChurch === null && $discount === null) {
            return null;
        }

        $total = ($price ?? 0.0) + ($priceChurch ?? 0.0) - ($discount ?? 0.0);

        return MoneyHelper::format_money($total);
    }

    public function ojtExpectedCount(): int
    {
        if ($this->ojt_count_override !== null) {
            return (int) $this->ojt_count_override;
        }

        $defaultCount = $this->course?->ojt_default_count;

        return $defaultCount !== null ? (int) $defaultCount : 0;
    }

    public function ojtPolicy(): string
    {
        if ($this->ojt_policy_override) {
            return $this->ojt_policy_override;
        }

        $coursePolicy = $this->course?->ojt_default_policy;

        if ($coursePolicy) {
            return $coursePolicy;
        }

        $execution = (int) ($this->course?->execution ?? 0);

        return $execution === 1 ? self::OJT_POLICY_FIXED : self::OJT_POLICY_ROTATE;
    }

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id', 'id');
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(Receipt::class);
    }

    public function vourses(): HasMany
    {
        return $this->hasMany(Voucher::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(Media::class);
    }

    public function eventDates(): HasMany
    {
        return $this->hasMany(EventDate::class, 'training_id');
    }

    public function scheduleItems(): HasMany
    {
        return $this->hasMany(TrainingScheduleItem::class);
    }

    public function ojtSessions(): HasMany
    {
        return $this->hasMany(OjtSession::class);
    }

    /**
     * @return array{
     *     completed_sessions: int,
     *     expected_sessions: int,
     *     gospel_presentations: int,
     *     listeners_count: int,
     *     results_decisions: int,
     *     results_interested: int,
     *     results_rejection: int,
     *     results_assurance: int,
     *     follow_up_scheduled: int
     * }
     */
    public function ojtReportSummary(): array
    {
        $reportTotals = $this->ojtReportBaseQuery()
            ->selectRaw('
                COALESCE(SUM(ojt_reports.gospel_presentations), 0) as gospel_presentations,
                COALESCE(SUM(ojt_reports.listeners_count), 0) as listeners_count,
                COALESCE(SUM(ojt_reports.results_decisions), 0) as results_decisions,
                COALESCE(SUM(ojt_reports.results_interested), 0) as results_interested,
                COALESCE(SUM(ojt_reports.results_rejection), 0) as results_rejection,
                COALESCE(SUM(ojt_reports.results_assurance), 0) as results_assurance,
                COALESCE(SUM(CASE WHEN ojt_reports.follow_up_scheduled = 1 THEN 1 ELSE 0 END), 0) as follow_up_scheduled
            ')
            ->first();

        $completedSessions = OjtSession::query()
            ->where('training_id', $this->id)
            ->whereHas('teams.report', function (Builder $query): void {
                $query->whereNotNull('submitted_at');
            })
            ->count();

        return [
            'completed_sessions' => $completedSessions,
            'expected_sessions' => $this->ojtExpectedCount(),
            'gospel_presentations' => (int) ($reportTotals?->gospel_presentations ?? 0),
            'listeners_count' => (int) ($reportTotals?->listeners_count ?? 0),
            'results_decisions' => (int) ($reportTotals?->results_decisions ?? 0),
            'results_interested' => (int) ($reportTotals?->results_interested ?? 0),
            'results_rejection' => (int) ($reportTotals?->results_rejection ?? 0),
            'results_assurance' => (int) ($reportTotals?->results_assurance ?? 0),
            'follow_up_scheduled' => (int) ($reportTotals?->follow_up_scheduled ?? 0),
        ];
    }

    private function ojtReportBaseQuery(): Builder
    {
        return OjtReport::query()
            ->join('ojt_teams', 'ojt_reports.ojt_team_id', '=', 'ojt_teams.id')
            ->join('ojt_sessions', 'ojt_teams.ojt_session_id', '=', 'ojt_sessions.id')
            ->where('ojt_sessions.training_id', $this->id)
            ->whereNotNull('ojt_reports.submitted_at');
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'training_user')
            ->withPivot('accredited', 'kit', 'payment', 'payment_receipt');
    }
}
