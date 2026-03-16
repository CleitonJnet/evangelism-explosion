<?php

namespace App\Livewire\Pages\App\Portal\Staff\Reports;

use App\Enums\EventReportReviewOutcome;
use App\Models\Training;
use App\Services\EventReports\StaffEventReportGovernanceService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;

class Show extends Component
{
    use AuthorizesRequests;

    public Training $training;

    /**
     * @var array<string, mixed>
     */
    public array $comparison = [];

    /**
     * @var array<string, mixed>
     */
    public array $reviewForm = [
        'action' => EventReportReviewOutcome::Commented->value,
        'classification' => 'aligned',
        'follow_up_required' => false,
        'comment' => '',
    ];

    public ?string $feedback = null;

    public bool $canManageReviews = false;

    public function mount(Training $training): void
    {
        $this->authorize('access-portal-staff');

        $this->training = $training;
        $this->canManageReviews = auth()->user()?->can('govern-portal-staff') ?? false;
        $this->refreshComparison();
    }

    public function render(): View
    {
        return view('livewire.pages.app.portal.staff.reports.show');
    }

    public function saveReview(): void
    {
        $this->authorize('govern-portal-staff');

        $validated = Validator::make(
            ['reviewForm' => $this->reviewForm],
            [
                'reviewForm.action' => ['required', Rule::in(array_map(static fn (EventReportReviewOutcome $outcome): string => $outcome->value, EventReportReviewOutcome::cases()))],
                'reviewForm.classification' => ['nullable', Rule::in(['aligned', 'attention', 'critical'])],
                'reviewForm.follow_up_required' => ['boolean'],
                'reviewForm.comment' => ['nullable', 'string', 'max:2000'],
            ],
            [
                'reviewForm.action.required' => 'Selecione como o Staff quer registrar esta leitura.',
            ],
        )->after(function ($validator): void {
            if (
                $this->reviewForm['action'] === EventReportReviewOutcome::ChangesRequested->value
                && blank(trim((string) ($this->reviewForm['comment'] ?? '')))
            ) {
                $validator->errors()->add('reviewForm.comment', 'Explique o ajuste solicitado antes de devolver o evento ao campo.');
            }
        })->validate()['reviewForm'];

        $user = auth()->user();
        abort_unless($user !== null, 403);

        $count = app(StaffEventReportGovernanceService::class)->recordTrainingReview(
            $this->training,
            $user,
            EventReportReviewOutcome::from($validated['action']),
            blank(trim((string) ($validated['comment'] ?? ''))) ? null : trim((string) $validated['comment']),
            [
                'scope' => 'staff_governance',
                'classification' => $validated['classification'] ?: null,
                'follow_up_required' => (bool) ($validated['follow_up_required'] ?? false),
            ],
        );

        $this->feedback = trans_choice('Leitura do Staff registrada em :count relatorio.|Leitura do Staff registrada em :count relatorios.', $count, ['count' => $count]);

        $this->refreshComparison();
    }

    private function refreshComparison(): void
    {
        $service = app(StaffEventReportGovernanceService::class);
        $user = auth()->user();

        abort_unless($user !== null, 403);
        abort_unless($service->canAccessTraining($user, $this->training), 404);

        $this->comparison = $service->buildComparison($user, $this->training);
    }
}
