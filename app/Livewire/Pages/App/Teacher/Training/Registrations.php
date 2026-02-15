<?php

namespace App\Livewire\Pages\App\Teacher\Training;

use App\Models\Training;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Livewire\Component;

class Registrations extends Component
{
    private const MANUAL_PAYMENT_RECEIPT_TOKEN = '__teacher_confirmed__';

    public Training $training;

    /**
     * @var array<int, array{
     *     key: string,
     *     church_name: string,
     *     summary: string,
     *     totals: array{registrations: int, pastors: int, accredited: int, kits: int, payment_receipts: int},
     *     registrations: array<int, array{
     *         id: int,
     *         name: string,
     *         church_name: string,
     *         email: ?string,
     *         phone: ?string,
     *         is_pastor: bool,
     *         pastor_label: string,
     *         has_payment_receipt: bool,
     *         kit: bool,
     *         accredited: bool,
     *         payment_receipt_url: ?string
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

    public bool $busy = false;

    public function mount(Training $training): void
    {
        $this->authorizeTraining($training);
        $this->training = $training;
        $this->refreshRegistrations();
    }

    public function togglePaymentReceipt(int $userId, bool $enabled): void
    {
        $student = $this->training->students->firstWhere('id', $userId);

        if (! $student) {
            return;
        }

        $currentReceipt = is_string($student->pivot?->payment_receipt)
            ? trim($student->pivot->payment_receipt)
            : '';

        if ($enabled && $currentReceipt !== '') {
            return;
        }

        $newValue = $enabled ? self::MANUAL_PAYMENT_RECEIPT_TOKEN : null;

        $this->updateEnrollment($userId, ['payment_receipt' => $newValue]);
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

        $this->authorizeTraining($this->training);
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

    /**
     * @param  array<string, bool|string|null>  $attributes
     */
    private function updateEnrollment(int $userId, array $attributes): void
    {
        if ($this->busy) {
            return;
        }

        $this->authorizeTraining($this->training);
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
                    ->with('church')
                    ->orderBy('name'),
            ])
            ->findOrFail($this->training->id);

        $students = $this->training->students
            ->sortBy(function (User $student): string {
                $churchName = $student->church?->name ?? 'Sem igreja vinculada';

                return strtolower($churchName.' '.$student->name);
            })
            ->values();

        $this->churchGroups = $students
            ->groupBy(function (User $student): string {
                return $student->church?->name ?? 'Sem igreja vinculada';
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
            ->values()
            ->all();

        $this->totalRegistrations = $students->count();
        $this->totalChurches = count($this->churchGroups);
        $this->totalPastors = $students->filter(fn (User $student): bool => filled($student->pastor))->count();
        $this->totalAccredited = $students->filter(fn (User $student): bool => (bool) $student->pivot?->accredited)->count();
        $this->totalKits = $students->filter(fn (User $student): bool => (bool) $student->pivot?->kit)->count();
        $this->totalPaymentReceipts = $students
            ->filter(fn (User $student): bool => filled($student->pivot?->payment_receipt))
            ->count();
    }

    /**
     * @return array{
     *     id: int,
     *     name: string,
     *     church_name: string,
     *     email: ?string,
     *     phone: ?string,
     *     is_pastor: bool,
     *     pastor_label: string,
     *     accredited: bool,
     *     kit: bool,
     *     has_payment_receipt: bool,
     *     payment_receipt_url: ?string
     * }
     */
    private function mapRegistration(User $student): array
    {
        $receipt = is_string($student->pivot?->payment_receipt)
            ? trim($student->pivot->payment_receipt)
            : '';
        $hasPaymentReceipt = $receipt !== '';
        $isManualReceipt = $receipt === self::MANUAL_PAYMENT_RECEIPT_TOKEN;

        return [
            'id' => $student->id,
            'name' => $student->name,
            'church_name' => $student->church?->name ?? 'Sem igreja vinculada',
            'email' => $student->email,
            'phone' => $student->phone,
            'is_pastor' => filled($student->pastor),
            'pastor_label' => filled($student->pastor) ? 'Sim' : 'Nao',
            'accredited' => (bool) $student->pivot?->accredited,
            'kit' => (bool) $student->pivot?->kit,
            'has_payment_receipt' => $hasPaymentReceipt,
            'payment_receipt_url' => (! $isManualReceipt && $hasPaymentReceipt)
                ? Storage::disk('public')->url($receipt)
                : null,
        ];
    }

    private function authorizeTraining(Training $training): void
    {
        $teacherId = Auth::id();

        if (! $teacherId || $training->teacher_id !== $teacherId) {
            abort(403);
        }
    }
}
