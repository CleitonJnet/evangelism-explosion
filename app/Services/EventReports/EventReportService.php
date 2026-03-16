<?php

namespace App\Services\EventReports;

use App\Enums\EventReportStatus;
use App\Enums\EventReportType;
use App\Models\EventReport;
use App\Models\Training;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EventReportService
{
    public function __construct(private EventReportWorkflowService $workflowService) {}

    public function ensureReport(Training $training, EventReportType $type, ?User $actor = null): EventReport
    {
        $report = EventReport::query()->firstOrCreate(
            [
                'training_id' => $training->id,
                'type' => $type,
            ],
            [
                'church_id' => $training->church_id,
                'created_by_user_id' => $actor?->id,
                'updated_by_user_id' => $actor?->id,
                'title' => $this->defaultTitle($training, $type),
                'context' => $this->defaultContext($training, $type),
                'status' => EventReportStatus::Draft,
            ],
        );

        if ($report->church_id === null && $training->church_id !== null) {
            $report->forceFill(['church_id' => $training->church_id])->save();
        }

        return $report->refresh();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function saveDraft(EventReport $report, array $data, User $actor): EventReport
    {
        $this->ensureEditable($report);

        return DB::transaction(function () use ($report, $data, $actor): EventReport {
            $report->fill($this->reportAttributes($data, $report));
            $report->status = $report->status === EventReportStatus::NeedsRevision
                ? EventReportStatus::NeedsRevision
                : EventReportStatus::Draft;
            $report->updated_by_user_id = $actor->id;
            $report->save();

            if (array_key_exists('sections', $data)) {
                $this->syncSections($report, $data['sections']);
            }

            return $report->refresh()->load(['sections', 'reviews']);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function submit(EventReport $report, array $data, User $actor): EventReport
    {
        $this->ensureEditable($report);

        return DB::transaction(function () use ($report, $data, $actor): EventReport {
            $report->fill($this->reportAttributes($data, $report));
            $report->status = EventReportStatus::Submitted;
            $report->submitted_at = now();
            $report->submitted_by_user_id = $actor->id;
            $report->updated_by_user_id = $actor->id;
            $report->review_requested_at = null;
            $report->reviewed_at = null;
            $report->last_reviewed_by_user_id = null;
            $report->save();

            if (array_key_exists('sections', $data)) {
                $this->syncSections($report, $data['sections']);
            }

            return $report->refresh()->load(['sections', 'reviews']);
        });
    }

    private function ensureEditable(EventReport $report): void
    {
        if ($this->workflowService->isEditable($report)) {
            return;
        }

        throw ValidationException::withMessages([
            'report' => __('Este relatorio esta bloqueado apos o envio. Aguarde uma solicitacao de revisao do Staff para editar novamente.'),
        ]);
    }

    private function syncSections(EventReport $report, mixed $sections): void
    {
        if (! is_array($sections)) {
            $report->sections()->delete();

            return;
        }

        $normalizedSections = collect($sections)
            ->map(function (mixed $section, int|string $fallbackPosition): ?array {
                if (! is_array($section)) {
                    return null;
                }

                $key = trim((string) ($section['key'] ?? ''));

                if ($key === '') {
                    return null;
                }

                return [
                    'key' => $key,
                    'title' => $this->nullableString($section['title'] ?? null),
                    'position' => (int) ($section['position'] ?? $fallbackPosition),
                    'content' => is_array($section['content'] ?? null) ? $section['content'] : null,
                    'meta' => is_array($section['meta'] ?? null) ? $section['meta'] : null,
                ];
            })
            ->filter()
            ->values();

        $existingKeys = $normalizedSections->pluck('key')->all();

        if ($existingKeys === []) {
            $report->sections()->delete();

            return;
        }

        $report->sections()->whereNotIn('key', $existingKeys)->delete();

        $normalizedSections->each(function (array $section) use ($report): void {
            $report->sections()->updateOrCreate(
                ['key' => $section['key']],
                Arr::except($section, ['key']),
            );
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function reportAttributes(array $data, EventReport $report): array
    {
        $training = $report->relationLoaded('training') ? $report->training : $report->training()->first();

        return [
            'church_id' => $report->church_id ?? $training?->church_id,
            'schema_version' => max(1, (int) ($data['schema_version'] ?? $report->schema_version ?? 1)),
            'title' => $this->nullableString($data['title'] ?? $report->title),
            'summary' => $this->nullableString($data['summary'] ?? null),
            'context' => is_array($data['context'] ?? null)
                ? $data['context']
                : ($report->context ?? $this->defaultContext($training, $report->type)),
            'meta' => is_array($data['meta'] ?? null) ? $data['meta'] : $report->meta,
        ];
    }

    private function defaultTitle(Training $training, EventReportType $type): string
    {
        $label = $type === EventReportType::Church ? 'Relatorio da igreja' : 'Relatorio do professor';

        return sprintf('%s - treinamento #%d', $label, $training->id);
    }

    /**
     * @return array<string, mixed>
     */
    private function defaultContext(?Training $training, EventReportType $type): array
    {
        return [
            'training_id' => $training?->id,
            'church_id' => $training?->church_id,
            'teacher_id' => $training?->teacher_id,
            'report_type' => $type->value,
        ];
    }

    private function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
