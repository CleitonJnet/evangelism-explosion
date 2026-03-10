<?php

namespace App\Services\Metrics;

use App\Models\Training;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class TrainingRegistrationMetricsService
{
    /**
     * @return array{
     *     churchGroups: array<int, array{
     *         key: string,
     *         church_name: string,
     *         has_church_issue: bool,
     *         summary: string,
     *         totals: array{registrations: int, pastors: int, accredited: int, kits: int, payment_receipts: int},
     *         registrations: array<int, array{
     *             id: int,
     *             name: string,
     *             church_label: string,
     *             church_name: string,
     *             email: ?string,
     *             phone: ?string,
     *             is_pastor: bool,
     *             pastor_label: string,
     *             accredited: bool,
     *             kit: bool,
     *             has_payment_receipt: bool,
     *             payment_confirmed: bool,
     *             has_church_issue: bool,
     *             payment_receipt_path: ?string,
     *             payment_receipt_url: ?string,
     *             payment_receipt_is_image: bool,
     *             payment_receipt_is_pdf: bool
     *         }>
     *     }>,
     *     totalRegistrations: int,
     *     totalChurches: int,
     *     totalPastors: int,
     *     totalAccredited: int,
     *     totalKits: int,
     *     totalPaymentReceipts: int,
     *     pendingChurchTempsCount: int
     * }
     */
    public function build(Training $training, string $search = ''): array
    {
        $training->loadMissing([
            'students' => fn ($query) => $query
                ->with(['church', 'church_temp'])
                ->orderBy('name'),
        ]);

        $searchTerm = mb_strtolower(trim($search), 'UTF-8');

        $students = $training->students
            ->filter(fn (User $student): bool => $this->matchesSearch($student, $searchTerm))
            ->sortBy(function (User $student): string {
                $churchName = $this->resolveChurchLabel($student);
                $priority = $this->hasChurchIssue($student) ? '0' : '1';

                return strtolower($priority.' '.$churchName.' '.$student->name);
            })
            ->values();

        $churchGroups = $students
            ->groupBy(fn (User $student): string => $this->resolveChurchLabel($student))
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

        return [
            'churchGroups' => $churchGroups,
            'totalRegistrations' => $students->count(),
            'totalChurches' => count($churchGroups),
            'totalPastors' => $students->filter(fn (User $student): bool => (bool) ($student->is_pastor ?? false))->count(),
            'totalAccredited' => $students->filter(fn (User $student): bool => (bool) $student->pivot?->accredited)->count(),
            'totalKits' => $students->filter(fn (User $student): bool => (bool) $student->pivot?->kit)->count(),
            'totalPaymentReceipts' => (int) collect($churchGroups)
                ->sum(fn (array $churchGroup): int => (int) ($churchGroup['totals']['payment_receipts'] ?? 0)),
            'pendingChurchTempsCount' => $students
                ->filter(fn (User $student): bool => $student->church_temp?->status === 'pending')
                ->map(fn (User $student): ?int => $student->church_temp?->id)
                ->filter()
                ->unique()
                ->count(),
        ];
    }

    /**
     * @return array{
     *     totalRegistrations: int,
     *     totalParticipatingChurches: int,
     *     totalPastors: int,
     *     totalUsedKits: int
     * }
     */
    public function summarizeOverview(Training $training): array
    {
        $training->loadMissing('students.church');

        return [
            'totalRegistrations' => $training->students->count(),
            'totalParticipatingChurches' => $training->students
                ->pluck('church_id')
                ->filter()
                ->unique()
                ->count(),
            'totalPastors' => $training->students
                ->filter(fn (User $student): bool => (bool) ($student->is_pastor ?? false))
                ->count(),
            'totalUsedKits' => $training->students
                ->filter(fn (User $student): bool => (bool) $student->pivot?->kit)
                ->count(),
        ];
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
    public function mapRegistration(User $student): array
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
            'pastor_label' => (bool) ($student->is_pastor ?? false) ? 'Sim' : 'Não',
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

    public function hasChurchIssue(User $student): bool
    {
        $hasNoChurch = $student->church_id === null && $student->church_temp_id === null;
        $hasPendingChurchValidation = $student->church_id === null && $student->church_temp?->status === 'pending';

        return $hasNoChurch || $hasPendingChurchValidation;
    }

    public function resolveChurchLabel(User $student): string
    {
        if ($student->church?->name) {
            return $student->church->name;
        }

        if ($student->church_temp?->name) {
            return '(PENDING) '.$student->church_temp->name;
        }

        return 'No church';
    }

    public function hasPaymentReceipt(User $student): bool
    {
        $receipt = is_string($student->pivot?->payment_receipt)
            ? trim($student->pivot->payment_receipt)
            : '';

        return $receipt !== '' && Storage::disk('public')->exists($receipt);
    }

    public function matchesSearch(User $student, string $searchTerm): bool
    {
        if ($searchTerm === '') {
            return true;
        }

        $fields = [
            $student->name,
            $student->email,
            $this->resolveChurchLabel($student),
            $student->church?->name,
            $student->church?->city,
            $student->church?->state,
            $student->church?->district,
            $student->church_temp?->name,
            $student->church_temp?->city,
            $student->church_temp?->state,
            $student->church_temp?->district,
            $student->city,
            $student->state,
            $student->district,
        ];

        $haystack = mb_strtolower(implode(' ', array_filter($fields)), 'UTF-8');

        return str_contains($haystack, $searchTerm);
    }
}
