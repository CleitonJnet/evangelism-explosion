<?php

namespace App\Livewire\Pages\App\Portal\Base\Training;

use App\Enums\EventReportType;
use App\Models\EventReport;
use App\Models\Training;
use App\Services\EventReports\EventReportService;
use App\Services\EventReports\EventReportWorkflowService;
use App\Services\Portals\PortalBaseCapabilityService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Component;

class Reports extends Component
{
    use AuthorizesRequests;

    public Training $training;

    public ?EventReport $churchReport = null;

    public ?EventReport $teacherReport = null;

    /**
     * @var array<string, mixed>
     */
    public array $churchForm = [];

    /**
     * @var array<string, mixed>
     */
    public array $teacherForm = [];

    /**
     * @var array<string, bool>
     */
    public array $portalCapabilities = [];

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $reportSummary = [];

    public ?string $churchFeedback = null;

    public ?string $teacherFeedback = null;

    public function mount(Training $training): void
    {
        $this->authorize('viewBaseContext', $training);

        $this->training = $training;
        $this->refreshState();
    }

    public function render(): View
    {
        return view('livewire.pages.app.portal.base.training.reports');
    }

    public function saveChurchDraft(): void
    {
        $this->authorize('submitChurchEventReport', $this->training);

        $validated = $this->validateChurchForm(false);

        $this->persistReport(
            EventReportType::Church,
            $this->churchReport,
            $this->churchPayload($validated),
            false,
            'churchReportLock',
            'Rascunho do relatorio da igreja salvo com sucesso.',
        );
    }

    public function submitChurchReport(): void
    {
        $this->authorize('submitChurchEventReport', $this->training);

        $validated = $this->validateChurchForm(true);

        $this->persistReport(
            EventReportType::Church,
            $this->churchReport,
            $this->churchPayload($validated),
            true,
            'churchReportLock',
            'Relatorio da igreja enviado com sucesso.',
        );
    }

    public function saveTeacherDraft(): void
    {
        $this->authorize('submitTeacherEventReport', $this->training);

        $validated = $this->validateTeacherForm(false);

        $this->persistReport(
            EventReportType::Teacher,
            $this->teacherReport,
            $this->teacherPayload($validated),
            false,
            'teacherReportLock',
            'Rascunho do relatorio do professor salvo com sucesso.',
        );
    }

    public function submitTeacherReport(): void
    {
        $this->authorize('submitTeacherEventReport', $this->training);

        $validated = $this->validateTeacherForm(true);

        $this->persistReport(
            EventReportType::Teacher,
            $this->teacherReport,
            $this->teacherPayload($validated),
            true,
            'teacherReportLock',
            'Relatorio do professor enviado com sucesso.',
        );
    }

    private function refreshState(): void
    {
        $this->training = Training::query()->with([
            'course',
            'church',
            'teacher',
            'churchEventReport.sections',
            'churchEventReport.reviews.reviewer',
            'teacherEventReport.sections',
            'teacherEventReport.reviews.reviewer',
        ])->findOrFail($this->training->id);

        $user = Auth::user();
        abort_unless($user !== null, 403);

        $this->portalCapabilities = app(PortalBaseCapabilityService::class)->eventSummary($user, $this->training);
        $this->churchReport = $this->training->churchEventReport;
        $this->teacherReport = $this->training->teacherEventReport;
        $this->reportSummary = app(EventReportWorkflowService::class)->buildTrainingSummary($this->training);
        $this->churchForm = $this->mapChurchForm($this->churchReport);
        $this->teacherForm = $this->mapTeacherForm($this->teacherReport);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function persistReport(
        EventReportType $type,
        ?EventReport $report,
        array $payload,
        bool $submit,
        string $lockErrorKey,
        string $successMessage,
    ): void {
        $user = Auth::user();
        abort_unless($user !== null, 403);

        $this->resetValidation($lockErrorKey);

        $service = app(EventReportService::class);
        $report ??= $service->ensureReport($this->training, $type, $user);

        try {
            if ($submit) {
                $service->submit($report, $payload, $user);
            } else {
                $service->saveDraft($report, $payload, $user);
            }
        } catch (ValidationException $exception) {
            $message = Arr::first(Arr::flatten($exception->errors()));

            if (is_string($message) && $message !== '') {
                $this->addError($lockErrorKey, $message);

                return;
            }

            throw $exception;
        }

        if ($type === EventReportType::Church) {
            $this->churchFeedback = $successMessage;
        } else {
            $this->teacherFeedback = $successMessage;
        }

        $this->refreshState();
    }

    /**
     * @return array<string, mixed>
     */
    private function validateChurchForm(bool $submit): array
    {
        $validator = Validator::make(
            ['churchForm' => $this->churchForm],
            [
                'churchForm.title' => ['nullable', 'string', 'max:120'],
                'churchForm.summary' => [$submit ? 'required' : 'nullable', 'string', 'max:3000'],
                'churchForm.attendance_registered' => ['nullable', 'integer', 'min:0'],
                'churchForm.attendance_present' => ['nullable', 'integer', 'min:0'],
                'churchForm.attendance_decisions' => ['nullable', 'integer', 'min:0'],
                'churchForm.follow_up_actions' => ['nullable', 'string', 'max:2000'],
                'churchForm.local_highlights' => ['nullable', 'string', 'max:2000'],
                'churchForm.support_needed' => ['nullable', 'string', 'max:2000'],
            ],
            [
                'churchForm.summary.required' => __('Informe um resumo antes de enviar o relatorio da igreja.'),
            ],
        );

        $validator->after(function ($validator) use ($submit): void {
            if (! $submit) {
                return;
            }

            if (! $this->hasChurchOperationalDetails()) {
                $validator->errors()->add('churchForm.local_highlights', __('Preencha pelo menos um detalhe operacional da igreja antes de enviar.'));
            }
        });

        return $validator->validate()['churchForm'];
    }

    /**
     * @return array<string, mixed>
     */
    private function validateTeacherForm(bool $submit): array
    {
        $validator = Validator::make(
            ['teacherForm' => $this->teacherForm],
            [
                'teacherForm.title' => ['nullable', 'string', 'max:120'],
                'teacherForm.summary' => [$submit ? 'required' : 'nullable', 'string', 'max:3000'],
                'teacherForm.sessions_completed' => ['nullable', 'integer', 'min:0'],
                'teacherForm.people_trained' => ['nullable', 'integer', 'min:0'],
                'teacherForm.practical_contacts' => ['nullable', 'integer', 'min:0'],
                'teacherForm.ministry_highlights' => ['nullable', 'string', 'max:2000'],
                'teacherForm.recommendations' => ['nullable', 'string', 'max:2000'],
                'teacherForm.next_steps' => ['nullable', 'string', 'max:2000'],
            ],
            [
                'teacherForm.summary.required' => __('Informe um resumo antes de enviar o relatorio do professor.'),
            ],
        );

        $validator->after(function ($validator) use ($submit): void {
            if (! $submit) {
                return;
            }

            if (! $this->hasTeacherOperationalDetails()) {
                $validator->errors()->add('teacherForm.ministry_highlights', __('Preencha pelo menos um detalhe ministerial do professor antes de enviar.'));
            }
        });

        return $validator->validate()['teacherForm'];
    }

    private function hasChurchOperationalDetails(): bool
    {
        return $this->filledString($this->churchForm['local_highlights'] ?? null)
            || $this->filledString($this->churchForm['follow_up_actions'] ?? null)
            || $this->filledString($this->churchForm['support_needed'] ?? null)
            || $this->nullableInt($this->churchForm['attendance_registered'] ?? null) !== null
            || $this->nullableInt($this->churchForm['attendance_present'] ?? null) !== null
            || $this->nullableInt($this->churchForm['attendance_decisions'] ?? null) !== null;
    }

    private function hasTeacherOperationalDetails(): bool
    {
        return $this->filledString($this->teacherForm['ministry_highlights'] ?? null)
            || $this->filledString($this->teacherForm['recommendations'] ?? null)
            || $this->filledString($this->teacherForm['next_steps'] ?? null)
            || $this->nullableInt($this->teacherForm['sessions_completed'] ?? null) !== null
            || $this->nullableInt($this->teacherForm['people_trained'] ?? null) !== null
            || $this->nullableInt($this->teacherForm['practical_contacts'] ?? null) !== null;
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function churchPayload(array $validated): array
    {
        return [
            'title' => $this->nullableString($validated['title'] ?? null),
            'summary' => $this->nullableString($validated['summary'] ?? null),
            'sections' => [
                [
                    'key' => 'attendance',
                    'title' => 'Participacao local',
                    'position' => 1,
                    'content' => [
                        'registered' => $this->nullableInt($validated['attendance_registered'] ?? null),
                        'present' => $this->nullableInt($validated['attendance_present'] ?? null),
                        'decisions' => $this->nullableInt($validated['attendance_decisions'] ?? null),
                    ],
                ],
                [
                    'key' => 'follow_up',
                    'title' => 'Acompanhamento da base',
                    'position' => 2,
                    'content' => [
                        'actions' => $this->nullableString($validated['follow_up_actions'] ?? null),
                    ],
                ],
                [
                    'key' => 'host_highlights',
                    'title' => 'Destaques operacionais',
                    'position' => 3,
                    'content' => [
                        'highlights' => $this->nullableString($validated['local_highlights'] ?? null),
                        'support_needed' => $this->nullableString($validated['support_needed'] ?? null),
                    ],
                ],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function teacherPayload(array $validated): array
    {
        return [
            'title' => $this->nullableString($validated['title'] ?? null),
            'summary' => $this->nullableString($validated['summary'] ?? null),
            'sections' => [
                [
                    'key' => 'execution',
                    'title' => 'Execucao do treinamento',
                    'position' => 1,
                    'content' => [
                        'sessions_completed' => $this->nullableInt($validated['sessions_completed'] ?? null),
                        'people_trained' => $this->nullableInt($validated['people_trained'] ?? null),
                        'practical_contacts' => $this->nullableInt($validated['practical_contacts'] ?? null),
                    ],
                ],
                [
                    'key' => 'ministry_highlights',
                    'title' => 'Destaques ministeriais',
                    'position' => 2,
                    'content' => [
                        'highlights' => $this->nullableString($validated['ministry_highlights'] ?? null),
                    ],
                ],
                [
                    'key' => 'next_cycle',
                    'title' => 'Recomendacoes e proximos passos',
                    'position' => 3,
                    'content' => [
                        'recommendations' => $this->nullableString($validated['recommendations'] ?? null),
                        'next_steps' => $this->nullableString($validated['next_steps'] ?? null),
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapChurchForm(?EventReport $report): array
    {
        $sections = $report?->sections?->keyBy('key') ?? collect();

        return [
            'title' => (string) ($report?->title ?? ''),
            'summary' => (string) ($report?->summary ?? ''),
            'attendance_registered' => data_get($sections, 'attendance.content.registered'),
            'attendance_present' => data_get($sections, 'attendance.content.present'),
            'attendance_decisions' => data_get($sections, 'attendance.content.decisions'),
            'follow_up_actions' => (string) (data_get($sections, 'follow_up.content.actions') ?? ''),
            'local_highlights' => (string) (data_get($sections, 'host_highlights.content.highlights') ?? ''),
            'support_needed' => (string) (data_get($sections, 'host_highlights.content.support_needed') ?? ''),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapTeacherForm(?EventReport $report): array
    {
        $sections = $report?->sections?->keyBy('key') ?? collect();

        return [
            'title' => (string) ($report?->title ?? ''),
            'summary' => (string) ($report?->summary ?? ''),
            'sessions_completed' => data_get($sections, 'execution.content.sessions_completed'),
            'people_trained' => data_get($sections, 'execution.content.people_trained'),
            'practical_contacts' => data_get($sections, 'execution.content.practical_contacts'),
            'ministry_highlights' => (string) (data_get($sections, 'ministry_highlights.content.highlights') ?? ''),
            'recommendations' => (string) (data_get($sections, 'next_cycle.content.recommendations') ?? ''),
            'next_steps' => (string) (data_get($sections, 'next_cycle.content.next_steps') ?? ''),
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

    private function nullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    private function filledString(mixed $value): bool
    {
        return is_string($value) && trim($value) !== '';
    }
}
