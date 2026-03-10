<?php

namespace App\Livewire\Shared\Training;

use App\Livewire\Shared\Training\Concerns\InteractsWithTrainingContext;
use App\Models\Course;
use App\Models\Role;
use App\Models\Training;
use App\Models\User;
use App\Services\Metrics\TrainingRegistrationMetricsService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

abstract class RegistrationsPage extends Component
{
    use AuthorizesRequests;
    use InteractsWithTrainingContext;

    public Training $training;

    public string $search = '';

    public bool $showReceiptModal = false;

    public ?int $selectedRegistrationId = null;

    public string $selectedRegistrationName = '';

    public ?string $selectedPaymentReceiptUrl = null;

    public bool $selectedPaymentReceiptIsImage = false;

    public bool $selectedPaymentReceiptIsPdf = false;

    public bool $selectedHasPaymentReceipt = false;

    public bool $selectedPaymentConfirmed = false;

    /**
     * @var array<int, array{
     *     key: string,
     *     church_name: string,
     *     has_church_issue: bool,
     *     summary: string,
     *     totals: array{registrations: int, pastors: int, accredited: int, kits: int, payment_receipts: int},
     *     registrations: array<int, array{
     *         id: int,
     *         name: string,
     *         church_label: string,
     *         church_name: string,
     *         email: ?string,
     *         phone: ?string,
     *         is_pastor: bool,
     *         pastor_label: string,
     *         has_payment_receipt: bool,
     *         payment_confirmed: bool,
     *         has_church_issue: bool,
     *         kit: bool,
     *         accredited: bool,
     *         payment_receipt_path: ?string,
     *         payment_receipt_url: ?string,
     *         payment_receipt_is_image: bool,
     *         payment_receipt_is_pdf: bool
     *     }>
     * }>
     */
    public array $churchGroups = [];

    public int $totalRegistrations = 0;

    public int $totalChurches = 0;

    public int $totalPastors = 0;

    public int $totalAccredited = 0;

    public int $totalKits = 0;

    public int $totalPaymentReceipts = 0;

    public int $pendingChurchTempsCount = 0;

    public bool $busy = false;

    public function mount(Training $training): void
    {
        $this->authorize('view', $training);
        $this->training = $training;
        $this->initializeTrainingContext($training);
        $this->refreshRegistrations();
    }

    public function togglePayment(int $userId, bool $enabled): void
    {
        if ($enabled && ! $this->studentHasPaymentReceipt($userId)) {
            $this->addError('paymentConfirmation', __('O aluno ainda não enviou um comprovante válido.'));
            $this->selectedPaymentConfirmed = false;

            return;
        }

        $this->resetErrorBag('paymentConfirmation');
        $this->updateEnrollment($userId, ['payment' => $enabled]);

        if ($this->selectedRegistrationId === $userId) {
            $this->selectedPaymentConfirmed = $enabled;
        }
    }

    public function toggleAccredited(int $userId, bool $enabled): void
    {
        $this->updateEnrollment($userId, ['accredited' => $enabled]);

        if ($enabled && $this->trainingCourseAllowsFacilitatorRole()) {
            $this->assignFacilitatorRole($userId);
            $this->assignTeacherToImplementationCourses($userId);
        }
    }

    public function toggleKit(int $userId, bool $enabled): void
    {
        if (! $this->canToggleRegistrationKit()) {
            return;
        }

        $this->updateEnrollment($userId, ['kit' => $enabled]);
    }

    public function removeRegistration(int $userId): void
    {
        if ($this->busy) {
            return;
        }

        $this->authorize('update', $this->training);
        $this->busy = true;

        try {
            if (! $this->training->students->contains('id', $userId)) {
                return;
            }

            $this->training->students()->detach($userId);
            $this->refreshRegistrations();
        } finally {
            $this->busy = false;
        }
    }

    public function render(): View
    {
        return view('livewire.shared.training.registrations-page');
    }

    #[On('church-temp-reviewed')]
    public function handleChurchTempReviewed(): void
    {
        $this->refreshRegistrations();
    }

    #[On('training-material-delivered')]
    public function handleTrainingMaterialDelivered(?int $trainingId = null): void
    {
        if ($trainingId !== null && $trainingId !== $this->training->id) {
            return;
        }

        $this->refreshRegistrations();
    }

    public function updatedSearch(string $search): void
    {
        $normalizedSearch = trim($search);

        if ($normalizedSearch !== $search) {
            $this->search = $normalizedSearch;

            return;
        }

        $this->refreshRegistrations();
    }

    public function openReceiptModal(int $userId): void
    {
        $student = $this->training->students->firstWhere('id', $userId);

        if (! $student) {
            return;
        }

        $registration = $this->mapRegistration($student);

        $this->selectedRegistrationId = $registration['id'];
        $this->selectedRegistrationName = $registration['name'];
        $this->selectedPaymentReceiptUrl = $registration['payment_receipt_url'];
        $this->selectedPaymentReceiptIsImage = $registration['payment_receipt_is_image'];
        $this->selectedPaymentReceiptIsPdf = $registration['payment_receipt_is_pdf'];
        $this->selectedHasPaymentReceipt = $registration['has_payment_receipt'];
        $this->selectedPaymentConfirmed = $registration['payment_confirmed'];
        $this->resetErrorBag('paymentConfirmation');
        $this->showReceiptModal = true;
    }

    public function closeReceiptModal(): void
    {
        $this->showReceiptModal = false;
        $this->selectedRegistrationId = null;
        $this->selectedRegistrationName = '';
        $this->selectedPaymentReceiptUrl = null;
        $this->selectedPaymentReceiptIsImage = false;
        $this->selectedPaymentReceiptIsPdf = false;
        $this->selectedHasPaymentReceipt = false;
        $this->selectedPaymentConfirmed = false;
        $this->resetErrorBag('paymentConfirmation');
    }

    /**
     * @param  array<string, bool|string|null>  $attributes
     */
    private function updateEnrollment(int $userId, array $attributes): void
    {
        if ($this->busy) {
            return;
        }

        $this->authorize('update', $this->training);
        $this->busy = true;

        try {
            if (! $this->training->students->contains('id', $userId)) {
                return;
            }

            $this->training->students()->updateExistingPivot($userId, $attributes);
            $this->refreshRegistrations();
        } finally {
            $this->busy = false;
        }
    }

    private function refreshRegistrations(): void
    {
        $this->training = Training::query()
            ->with([
                'church',
                'course',
                'students' => fn ($query) => $query
                    ->with(['church', 'church_temp'])
                    ->orderBy('name'),
            ])
            ->findOrFail($this->training->id);

        $metrics = app(TrainingRegistrationMetricsService::class)->build($this->training, $this->search);

        $this->churchGroups = $metrics['churchGroups'];
        $this->totalRegistrations = $metrics['totalRegistrations'];
        $this->totalChurches = $metrics['totalChurches'];
        $this->totalPastors = $metrics['totalPastors'];
        $this->totalAccredited = $metrics['totalAccredited'];
        $this->totalKits = $metrics['totalKits'];
        $this->totalPaymentReceipts = $metrics['totalPaymentReceipts'];
        $this->pendingChurchTempsCount = $metrics['pendingChurchTempsCount'];

        if ($this->showReceiptModal && $this->selectedRegistrationId) {
            $this->syncSelectedRegistration($this->selectedRegistrationId);
        }
    }

    /**
     * @return array{
     *     id: int,
     *     name: string,
     *     church_label: string,
     *     church_name: string,
     *     email: ?string,
     *     phone: ?string,
     *     is_pastor: bool,
     *     pastor_label: string,
     *     accredited: bool,
     *     kit: bool,
     *     has_payment_receipt: bool,
     *     payment_confirmed: bool,
     *     has_church_issue: bool,
     *     payment_receipt_path: ?string,
     *     payment_receipt_url: ?string,
     *     payment_receipt_is_image: bool,
     *     payment_receipt_is_pdf: bool
     * }
     */
    private function mapRegistration(User $student): array
    {
        return app(TrainingRegistrationMetricsService::class)->mapRegistration($student);
    }

    private function hasChurchIssue(User $student): bool
    {
        return app(TrainingRegistrationMetricsService::class)->hasChurchIssue($student);
    }

    private function studentHasPaymentReceipt(int $userId): bool
    {
        $student = $this->training->students->firstWhere('id', $userId);

        if (! $student) {
            return false;
        }

        return app(TrainingRegistrationMetricsService::class)->hasPaymentReceipt($student);
    }

    private function syncSelectedRegistration(int $userId): void
    {
        $student = $this->training->students->firstWhere('id', $userId);

        if (! $student) {
            $this->closeReceiptModal();

            return;
        }

        $registration = $this->mapRegistration($student);
        $this->selectedRegistrationName = $registration['name'];
        $this->selectedPaymentReceiptUrl = $registration['payment_receipt_url'];
        $this->selectedPaymentReceiptIsImage = $registration['payment_receipt_is_image'];
        $this->selectedPaymentReceiptIsPdf = $registration['payment_receipt_is_pdf'];
        $this->selectedHasPaymentReceipt = $registration['has_payment_receipt'];
        $this->selectedPaymentConfirmed = $registration['payment_confirmed'];
    }

    private function resolveChurchLabel(User $student): string
    {
        return app(TrainingRegistrationMetricsService::class)->resolveChurchLabel($student);
    }

    private function matchesSearch(User $student, string $searchTerm): bool
    {
        return app(TrainingRegistrationMetricsService::class)->matchesSearch($student, $searchTerm);
    }

    private function trainingCourseAllowsFacilitatorRole(): bool
    {
        return (int) ($this->training->course?->execution ?? -1) === 0;
    }

    private function assignFacilitatorRole(int $userId): void
    {
        $user = User::query()->find($userId);

        if (! $user) {
            return;
        }

        $facilitatorRole = Role::query()->firstOrCreate(['name' => 'Facilitator']);

        $user->roles()->syncWithoutDetaching([$facilitatorRole->id]);
    }

    private function assignTeacherToImplementationCourses(int $userId): void
    {
        $ministryId = $this->training->course?->ministry_id;

        if (! $ministryId) {
            return;
        }

        $implementationCourses = Course::query()
            ->where('ministry_id', $ministryId)
            ->where('execution', 1)
            ->get();

        foreach ($implementationCourses as $course) {
            $course->teachers()->syncWithoutDetaching([
                $userId => ['status' => 1],
            ]);
        }
    }
}
