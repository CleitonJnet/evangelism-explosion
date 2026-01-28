<?php

namespace App\Livewire\Pages\App\Student\Training;

use App\Models\OjtSession;
use App\Models\Training;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;

class Show extends Component
{
    use WithFileUploads;

    public Training $training;

    public ?string $workloadDuration = null;

    public string $churchAddress = '';

    public bool $paymentConfirmed = false;

    public ?string $paymentReceiptPath = null;

    public mixed $paymentReceipt = null;

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $ojtAssignments = [];

    public function mount(Training $training): void
    {
        $this->training = $training->load([
            'course',
            'church',
            'teacher',
            'eventDates' => fn ($query) => $query->orderBy('date')->orderBy('start_time'),
        ]);

        $this->churchAddress = implode(
            ', ',
            array_filter([
                $this->training->street ?? null,
                $this->training->number ?? null,
                $this->training->district ?? null,
                $this->training->city ?? null,
                $this->training->state ?? null,
            ]),
        );

        $this->workloadDuration = $this->calculateWorkloadDuration($this->training);

        $user = Auth::user();

        if (! $user) {
            abort(401);
        }

        $enrollment = $this->training->students()
            ->where('users.id', $user->id)
            ->first();

        if (! $enrollment) {
            abort(403);
        }

        $this->paymentConfirmed = (bool) $enrollment->pivot?->payment;
        $this->paymentReceiptPath = $enrollment->pivot?->payment_receipt;

        $this->ojtAssignments = $this->buildOjtAssignments($user->id);
    }

    public function render(): View
    {
        return view('livewire.pages.app.student.training.show');
    }

    public function uploadPaymentReceipt(): void
    {
        $user = Auth::user();

        if (! $user) {
            abort(401);
        }

        $isPaid = (float) preg_replace('/\D/', '', (string) $this->training->payment) > 0;

        if (! $isPaid) {
            $this->addError('paymentReceipt', __('Este treinamento não exige pagamento.'));

            return;
        }

        if ($this->paymentConfirmed) {
            $this->addError('paymentReceipt', __('Pagamento já confirmado.'));

            return;
        }

        $this->validate([
            'paymentReceipt' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        $path = $this->paymentReceipt->store("training-receipts/{$this->training->id}", 'public');

        $this->training->students()->updateExistingPivot($user->id, [
            'payment_receipt' => $path,
        ]);

        $this->paymentReceiptPath = $path;
        $this->reset('paymentReceipt');
        $this->dispatch('payment-receipt-uploaded');
    }

    private function calculateWorkloadDuration(Training $training): ?string
    {
        $workloadMinutes = $training->eventDates->reduce(function (int $total, $eventDate): int {
            if (! $eventDate->start_time || ! $eventDate->end_time) {
                return $total;
            }

            $start = Carbon::parse($eventDate->date.' '.$eventDate->start_time);
            $end = Carbon::parse($eventDate->date.' '.$eventDate->end_time);

            if ($end->lessThanOrEqualTo($start)) {
                return $total;
            }

            return $total + $start->diffInMinutes($end);
        }, 0);

        if ($workloadMinutes <= 0) {
            return null;
        }

        $hours = intdiv($workloadMinutes, 60);
        $minutes = $workloadMinutes % 60;

        return $minutes > 0
            ? sprintf('%02dh%02d', $hours, $minutes)
            : sprintf('%02dh', $hours);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildOjtAssignments(int $studentId): array
    {
        return $this->training->ojtSessions()
            ->whereDate('date', '>=', now()->toDateString())
            ->where('status', 'planned')
            ->whereHas('teams.trainees', function ($query) use ($studentId): void {
                $query->where('trainee_id', $studentId);
            })
            ->with(['teams' => function ($query) use ($studentId): void {
                $query->whereHas('trainees', function ($subQuery) use ($studentId): void {
                    $subQuery->where('trainee_id', $studentId);
                })->with(['mentor', 'trainees.trainee', 'report']);
            }])
            ->orderBy('date')
            ->orderBy('starts_at')
            ->get()
            ->map(function (OjtSession $session) use ($studentId): array {
                $team = $session->teams->first();
                $teammate = $team?->trainees->firstWhere('trainee_id', '!=', $studentId);
                $report = $team?->report;

                return [
                    'id' => $session->id,
                    'week_number' => $session->week_number,
                    'date' => $session->date?->format('Y-m-d'),
                    'starts_at' => $session->starts_at,
                    'ends_at' => $session->ends_at,
                    'mentor_name' => $team?->mentor?->name,
                    'teammate_name' => $teammate?->trainee?->name,
                    'report' => $report && $report->submitted_at ? [
                        'submitted_at' => $report->submitted_at?->format('Y-m-d H:i'),
                        'gospel_presentations' => $report->gospel_presentations,
                        'listeners_count' => $report->listeners_count,
                        'results_decisions' => $report->results_decisions,
                        'results_interested' => $report->results_interested,
                        'results_rejection' => $report->results_rejection,
                        'results_assurance' => $report->results_assurance,
                        'follow_up_scheduled' => (bool) $report->follow_up_scheduled,
                        'lesson_learned' => $report->lesson_learned,
                    ] : null,
                ];
            })
            ->values()
            ->all();
    }
}
