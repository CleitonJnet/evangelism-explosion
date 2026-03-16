<?php

namespace App\Services\EventReports;

use App\Enums\EventReportStatus;
use App\Enums\EventReportType;
use App\Models\EventReport;
use App\Models\Training;

class EventReportWorkflowService
{
    public function isEditable(?EventReport $report): bool
    {
        if (! $report instanceof EventReport) {
            return true;
        }

        return in_array($report->status, [EventReportStatus::Draft, EventReportStatus::NeedsRevision], true);
    }

    public function hasMeaningfulContent(?EventReport $report): bool
    {
        if (! $report instanceof EventReport) {
            return false;
        }

        if ($this->filledString($report->summary)) {
            return true;
        }

        $sections = $report->relationLoaded('sections') ? $report->sections : $report->sections()->get();

        foreach ($sections as $section) {
            if ($this->contentHasValue($section->content)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<int, array{
     *     key: string,
     *     label: string,
     *     description: string,
     *     type: string,
     *     status_key: string,
     *     status_label: string,
     *     tone: string,
     *     is_editable: bool,
     *     submitted_at: ?string,
     *     can_request_revision: bool,
     *     last_review_comment: ?string
     * }>
     */
    public function buildTrainingSummary(Training $training): array
    {
        return [
            $this->buildSummaryItem($training->churchEventReport, EventReportType::Church),
            $this->buildSummaryItem($training->teacherEventReport, EventReportType::Teacher),
        ];
    }

    /**
     * @return array{
     *     key: string,
     *     label: string,
     *     description: string,
     *     type: string,
     *     status_key: string,
     *     status_label: string,
     *     tone: string,
     *     is_editable: bool,
     *     submitted_at: ?string,
     *     can_request_revision: bool,
     *     last_review_comment: ?string
     * }
     */
    public function buildSummaryItem(?EventReport $report, EventReportType $type): array
    {
        $status = $this->presentationStatus($report);
        $latestReview = $report?->relationLoaded('reviews') ? $report?->reviews->first() : $report?->reviews()->latest('reviewed_at')->latest('id')->first();

        return [
            'key' => $type->value,
            'label' => $type === EventReportType::Church ? 'Relatorio da igreja-base' : 'Relatorio do professor',
            'description' => $type === EventReportType::Church
                ? 'Relato operacional da base anfitria, incluindo acompanhamento local e consolidacao do evento.'
                : 'Relato ministerial e de execucao do professor responsavel pelo treinamento.',
            'type' => $type->value,
            'status_key' => $status['key'],
            'status_label' => $status['label'],
            'tone' => $status['tone'],
            'is_editable' => $this->isEditable($report),
            'submitted_at' => $report?->submitted_at?->format('d/m/Y H:i'),
            'can_request_revision' => $report?->status === EventReportStatus::NeedsRevision,
            'last_review_comment' => $this->filledString($latestReview?->comment) ? trim((string) $latestReview?->comment) : null,
        ];
    }

    /**
     * @return array{key: string, label: string, tone: string}
     */
    public function presentationStatus(?EventReport $report): array
    {
        if (! $report instanceof EventReport) {
            return [
                'key' => 'not_started',
                'label' => 'Nao iniciado',
                'tone' => 'slate',
            ];
        }

        if (in_array($report->status, [EventReportStatus::Submitted, EventReportStatus::Reviewed], true)) {
            return [
                'key' => 'submitted',
                'label' => 'Enviado',
                'tone' => 'emerald',
            ];
        }

        if ($report->status === EventReportStatus::Draft && ! $this->hasMeaningfulContent($report)) {
            return [
                'key' => 'not_started',
                'label' => 'Nao iniciado',
                'tone' => 'slate',
            ];
        }

        return [
            'key' => 'draft',
            'label' => 'Em rascunho',
            'tone' => $report->status === EventReportStatus::NeedsRevision ? 'amber' : 'sky',
        ];
    }

    private function contentHasValue(mixed $content): bool
    {
        if (is_string($content)) {
            return $this->filledString($content);
        }

        if (is_int($content) || is_float($content)) {
            return true;
        }

        if (is_bool($content)) {
            return $content;
        }

        if (! is_array($content)) {
            return false;
        }

        foreach ($content as $value) {
            if ($this->contentHasValue($value)) {
                return true;
            }
        }

        return false;
    }

    private function filledString(mixed $value): bool
    {
        return is_string($value) && trim($value) !== '';
    }
}
