<?php

namespace App\Livewire\Pages\App\Teacher\Training;

use App\Models\Training;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class Registrations extends Component
{
    use AuthorizesRequests;

    public Training $training;

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
    }

    public function toggleKit(int $userId, bool $enabled): void
    {
        $this->updateEnrollment($userId, ['kit' => $enabled]);
    }

    public function removeRegistration(int $userId): void
    {
        if ($this->busy) {
            return;
        }

        $this->authorize('view', $this->training);
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
        return view('livewire.pages.app.teacher.training.registrations');
    }

    #[On('church-temp-reviewed')]
    public function handleChurchTempReviewed(): void
    {
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

        $this->authorize('view', $this->training);
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
                'students' => fn ($query) => $query
                    ->with(['church', 'church_temp'])
                    ->orderBy('name'),
            ])
            ->findOrFail($this->training->id);

        $students = $this->training->students
            ->sortBy(function (User $student): string {
                $churchName = $this->resolveChurchLabel($student);
                $priority = $this->hasChurchIssue($student) ? '0' : '1';

                return strtolower($priority.' '.$churchName.' '.$student->name);
            })
            ->values();

        $this->churchGroups = $students
            ->groupBy(function (User $student): string {
                return $this->resolveChurchLabel($student);
            })
            ->map(function ($group, string $churchName): array {
                $registrations = $group
                    ->map(fn (User $student): array => $this->mapRegistration($student))
                    ->values()
                    ->all();

                $totals = [
                    'registrations' => count($registrations),
                    'pastors' => collect($registrations)->where('is_pastor', true)->count(),
                    'accredited' => collect($registrations)->where('accredited', true)->count(),
                    'kits' => collect($registrations)->where('kit', true)->count(),
                    'payment_receipts' => collect($registrations)->where('has_payment_receipt', true)->count(),
                ];

                return [
                    'key' => md5($churchName),
                    'church_name' => $churchName,
                    'has_church_issue' => collect($registrations)
                        ->contains(fn (array $registration): bool => (bool) ($registration['has_church_issue'] ?? false)),
                    'summary' => sprintf(
                        '%d inscritos, %d pastor(es), %d comprovantes, %d kits entregues, %d credenciados',
                        $totals['registrations'],
                        $totals['pastors'],
                        $totals['payment_receipts'],
                        $totals['kits'],
                        $totals['accredited'],
                    ),
                    'totals' => $totals,
                    'registrations' => $registrations,
                ];
            })
            ->sortBy(function (array $churchGroup): string {
                $priority = $churchGroup['has_church_issue'] ? '0' : '1';

                return strtolower($priority.' '.($churchGroup['church_name'] ?? ''));
            })
            ->values()
            ->all();

        $this->totalRegistrations = $students->count();
        $this->totalChurches = count($this->churchGroups);
        $this->totalPastors = $students->filter(fn (User $student): bool => (bool) ($student->is_pastor ?? false))->count();
        $this->totalAccredited = $students->filter(fn (User $student): bool => (bool) $student->pivot?->accredited)->count();
        $this->totalKits = $students->filter(fn (User $student): bool => (bool) $student->pivot?->kit)->count();
        $this->totalPaymentReceipts = (int) collect($this->churchGroups)
            ->sum(fn (array $churchGroup): int => (int) ($churchGroup['totals']['payment_receipts'] ?? 0));
        $this->pendingChurchTempsCount = $students
            ->filter(fn (User $student): bool => $student->church_temp?->status === 'pending')
            ->map(fn (User $student): ?int => $student->church_temp?->id)
            ->filter()
            ->unique()
            ->count();

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
        $receipt = is_string($student->pivot?->payment_receipt)
            ? trim($student->pivot->payment_receipt)
            : '';
        $receiptExtension = strtolower(pathinfo($receipt, PATHINFO_EXTENSION));
        $paymentReceiptIsImage = in_array($receiptExtension, ['webp', 'jpeg', 'png', 'gif', 'webp'], true);
        $paymentReceiptIsPdf = $receiptExtension === 'pdf';
        $hasPaymentReceipt = $receipt !== '' && Storage::disk('public')->exists($receipt);
        $paymentReceiptUrl = $hasPaymentReceipt ? Storage::disk('public')->url($receipt) : null;

        return [
            'id' => $student->id,
            'name' => $student->name,
            'church_label' => $this->resolveChurchLabel($student),
            'church_name' => $this->resolveChurchLabel($student),
            'email' => $student->email,
            'phone' => $student->phone,
            'is_pastor' => (bool) ($student->is_pastor ?? false),
            'pastor_label' => (bool) ($student->is_pastor ?? false) ? 'Sim' : 'Nao',
            'accredited' => (bool) $student->pivot?->accredited,
            'kit' => (bool) $student->pivot?->kit,
            'has_payment_receipt' => $hasPaymentReceipt,
            'payment_confirmed' => (bool) $student->pivot?->payment,
            'has_church_issue' => $this->hasChurchIssue($student),
            'payment_receipt_path' => $hasPaymentReceipt ? $receipt : null,
            'payment_receipt_url' => $paymentReceiptUrl,
            'payment_receipt_is_image' => $hasPaymentReceipt && $paymentReceiptIsImage,
            'payment_receipt_is_pdf' => $hasPaymentReceipt && $paymentReceiptIsPdf,
        ];
    }

    private function hasChurchIssue(User $student): bool
    {
        $hasNoChurch = $student->church_id === null && $student->church_temp_id === null;
        $hasPendingChurchValidation = $student->church_id === null && $student->church_temp?->status === 'pending';

        return $hasNoChurch || $hasPendingChurchValidation;
    }

    private function studentHasPaymentReceipt(int $userId): bool
    {
        $student = $this->training->students->firstWhere('id', $userId);

        if (! $student) {
            return false;
        }

        $receipt = is_string($student->pivot?->payment_receipt)
            ? trim($student->pivot->payment_receipt)
            : '';

        return $receipt !== '' && Storage::disk('public')->exists($receipt);
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
        if ($student->church?->name) {
            return $student->church->name;
        }

        if ($student->church_temp?->name) {
            return '(PENDING) '.$student->church_temp->name;
        }

        return 'No church';
    }
}
