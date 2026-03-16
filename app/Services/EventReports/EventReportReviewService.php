<?php

namespace App\Services\EventReports;

use App\Enums\EventReportReviewOutcome;
use App\Enums\EventReportStatus;
use App\Models\EventReport;
use App\Models\EventReportReview;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class EventReportReviewService
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function comment(EventReport $report, User $reviewer, ?string $comment = null, array $payload = []): EventReportReview
    {
        return $this->storeReview($report, $reviewer, EventReportReviewOutcome::Commented, $comment, $payload);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function requestChanges(EventReport $report, User $reviewer, ?string $comment = null, array $payload = []): EventReportReview
    {
        return $this->storeReview($report, $reviewer, EventReportReviewOutcome::ChangesRequested, $comment, $payload);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function approve(EventReport $report, User $reviewer, ?string $comment = null, array $payload = []): EventReportReview
    {
        return $this->storeReview($report, $reviewer, EventReportReviewOutcome::Approved, $comment, $payload);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function storeReview(EventReport $report, User $reviewer, EventReportReviewOutcome $outcome, ?string $comment, array $payload): EventReportReview
    {
        return DB::transaction(function () use ($report, $reviewer, $outcome, $comment, $payload): EventReportReview {
            $review = $report->reviews()->create([
                'reviewer_user_id' => $reviewer->id,
                'outcome' => $outcome,
                'comment' => $this->nullableString($comment),
                'payload' => $payload === [] ? null : $payload,
                'reviewed_at' => now(),
            ]);

            $attributes = [
                'last_reviewed_by_user_id' => $reviewer->id,
            ];

            if ($outcome === EventReportReviewOutcome::ChangesRequested) {
                $attributes['status'] = EventReportStatus::NeedsRevision;
                $attributes['review_requested_at'] = $review->reviewed_at;
                $attributes['reviewed_at'] = null;
            }

            if ($outcome === EventReportReviewOutcome::Approved) {
                $attributes['status'] = EventReportStatus::Reviewed;
                $attributes['review_requested_at'] = null;
                $attributes['reviewed_at'] = $review->reviewed_at;
            }

            $report->forceFill($attributes)->save();

            return $review->refresh();
        });
    }

    private function nullableString(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }
}
