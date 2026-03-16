<?php

namespace App\Services\Portals;

use App\Models\Training;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class StudentPortalOverviewService
{
    /**
     * @return array<string, mixed>
     */
    public function build(User $user): array
    {
        $trainings = $this->studentTrainings($user);
        $today = Carbon::today();

        $upcoming = $trainings
            ->filter(fn (Training $training): bool => $this->startsAt($training)?->isAfter($today->copy()->endOfDay()) ?? false)
            ->take(3)
            ->map(fn (Training $training): array => $this->mapTraining($training))
            ->values()
            ->all();

        $inProgress = $trainings
            ->filter(function (Training $training) use ($today): bool {
                $startsAt = $this->startsAt($training);
                $endsAt = $this->endsAt($training);

                if (! $startsAt || ! $endsAt) {
                    return false;
                }

                return $startsAt->startOfDay()->lte($today) && $endsAt->endOfDay()->gte($today);
            })
            ->map(fn (Training $training): array => $this->mapTraining($training))
            ->values()
            ->all();

        $history = $trainings
            ->filter(fn (Training $training): bool => $this->endsAt($training)?->isBefore($today->copy()->startOfDay()) ?? false)
            ->sortByDesc(fn (Training $training): string => (string) $this->endsAt($training)?->toDateTimeString())
            ->values();

        $receiptPendencies = $trainings
            ->filter(fn (Training $training): bool => $this->requiresReceipt($training) && ! $this->paymentConfirmed($training) && ! $this->hasReceipt($training))
            ->map(fn (Training $training): array => $this->mapTraining($training, 'pendente'))
            ->values();

        $receiptInReview = $trainings
            ->filter(fn (Training $training): bool => $this->requiresReceipt($training) && ! $this->paymentConfirmed($training) && $this->hasReceipt($training))
            ->map(fn (Training $training): array => $this->mapTraining($training, 'em_analise'))
            ->values();

        $certificates = $history
            ->map(function (Training $training): array {
                $courseCertificate = trim((string) data_get($training, 'course.certificate'));
                $hasCertificateConfig = $courseCertificate !== '';

                return [
                    ...$this->mapTraining($training),
                    'certificate_label' => $hasCertificateConfig ? $courseCertificate : 'Em preparacao',
                    'certificate_available' => false,
                    'certificate_status' => $hasCertificateConfig ? 'previsto para liberacao futura' : 'curso sem configuracao de certificado',
                ];
            })
            ->values()
            ->all();

        $historySummary = $history
            ->take(4)
            ->map(fn (Training $training): array => $this->mapTraining($training))
            ->values()
            ->all();

        return [
            'counts' => [
                'trainings' => $trainings->count(),
                'upcoming' => count($upcoming),
                'in_progress' => count($inProgress),
                'history' => $history->count(),
                'pending_receipts' => $receiptPendencies->count(),
                'receipts_in_review' => $receiptInReview->count(),
                'certificates' => count($certificates),
            ],
            'upcoming' => $upcoming,
            'in_progress' => $inProgress,
            'history' => $historySummary,
            'history_full' => $history->map(fn (Training $training): array => $this->mapTraining($training))->all(),
            'receipt_pendencies' => $receiptPendencies->all(),
            'receipt_in_review' => $receiptInReview->all(),
            'certificates' => $certificates,
            'shortcuts' => $this->shortcuts($upcoming, $inProgress, $receiptPendencies->all()),
        ];
    }

    /**
     * @return Collection<int, Training>
     */
    protected function studentTrainings(User $user): Collection
    {
        return Training::query()
            ->with([
                'course',
                'church',
                'eventDates' => fn ($query) => $query->orderBy('date')->orderBy('start_time'),
                'students' => fn ($query) => $query->whereKey($user->id),
            ])
            ->whereHas('students', fn ($query) => $query->whereKey($user->id))
            ->get()
            ->sortBy(fn (Training $training): string => (string) $this->startsAt($training)?->toDateTimeString())
            ->values();
    }

    /**
     * @param  array<string, string>|null  $status
     * @return array<string, mixed>
     */
    protected function mapTraining(Training $training, ?string $status = null): array
    {
        $startsAt = $this->startsAt($training);
        $endsAt = $this->endsAt($training);
        $enrollment = $training->students->first();
        $paymentRequired = $this->requiresReceipt($training);
        $receiptPending = $paymentRequired && ! $this->paymentConfirmed($training) && ! $this->hasReceipt($training);
        $receiptInReview = $paymentRequired && ! $this->paymentConfirmed($training) && $this->hasReceipt($training);

        return [
            'id' => $training->id,
            'title' => trim(implode(': ', array_filter([
                $training->course?->type,
                $training->course?->name,
            ]))),
            'course_name' => $training->course?->name ?? 'Treinamento',
            'church_name' => $training->church?->name ?? 'Base nao informada',
            'starts_at' => $startsAt?->format('d/m/Y H:i'),
            'starts_label' => $startsAt?->translatedFormat('d/m, D \\a\\s H:i'),
            'ends_at' => $endsAt?->format('d/m/Y H:i'),
            'schedule_summary' => $this->scheduleSummary($training),
            'location' => $this->locationLabel($training),
            'payment_required' => $paymentRequired,
            'payment_confirmed' => $this->paymentConfirmed($training),
            'has_receipt' => $this->hasReceipt($training),
            'receipt_pending' => $receiptPending,
            'receipt_in_review' => $receiptInReview,
            'accredited' => (bool) $enrollment?->pivot?->accredited,
            'kit' => (bool) $enrollment?->pivot?->kit,
            'status' => $status ?? $this->statusLabel($training),
            'detail_route' => route('app.portal.student.trainings.show', $training),
        ];
    }

    protected function startsAt(Training $training): ?Carbon
    {
        $event = $training->eventDates->first();

        if (! $event?->date) {
            return null;
        }

        return Carbon::parse(trim($event->date.' '.($event->start_time ?? '00:00:00')));
    }

    protected function endsAt(Training $training): ?Carbon
    {
        $event = $training->eventDates->last();

        if (! $event?->date) {
            return null;
        }

        return Carbon::parse(trim($event->date.' '.($event->end_time ?? '23:59:59')));
    }

    protected function paymentConfirmed(Training $training): bool
    {
        return (bool) $training->students->first()?->pivot?->payment;
    }

    protected function hasReceipt(Training $training): bool
    {
        return Str::of((string) $training->students->first()?->pivot?->payment_receipt)->trim()->isNotEmpty();
    }

    protected function requiresReceipt(Training $training): bool
    {
        return (float) preg_replace('/\D/', '', (string) $training->payment) > 0;
    }

    protected function locationLabel(Training $training): string
    {
        $parts = array_filter([
            $training->church?->name,
            $training->city,
            $training->state,
        ]);

        return $parts !== [] ? implode(' - ', $parts) : 'Local a confirmar';
    }

    protected function scheduleSummary(Training $training): string
    {
        if ($training->eventDates->isEmpty()) {
            return 'Datas a confirmar';
        }

        $first = $training->eventDates->first();
        $last = $training->eventDates->last();

        if ($first?->date === $last?->date) {
            return Carbon::parse((string) $first?->date)->translatedFormat('d/m/Y');
        }

        return Carbon::parse((string) $first?->date)->format('d/m').' a '.Carbon::parse((string) $last?->date)->format('d/m/Y');
    }

    protected function statusLabel(Training $training): string
    {
        $today = Carbon::today();
        $startsAt = $this->startsAt($training);
        $endsAt = $this->endsAt($training);

        if (! $startsAt || ! $endsAt) {
            return 'a confirmar';
        }

        if ($endsAt->endOfDay()->lt($today)) {
            return 'concluido';
        }

        if ($startsAt->startOfDay()->lte($today) && $endsAt->endOfDay()->gte($today)) {
            return 'em andamento';
        }

        return 'proximo';
    }

    /**
     * @param  array<int, array<string, mixed>>  $upcoming
     * @param  array<int, array<string, mixed>>  $inProgress
     * @param  array<int, array<string, mixed>>  $receiptPendencies
     * @return array<int, array<string, string>>
     */
    protected function shortcuts(array $upcoming, array $inProgress, array $receiptPendencies): array
    {
        return array_values(array_filter([
            $receiptPendencies[0] ?? null ? [
                'label' => 'Enviar comprovante',
                'description' => 'Ir direto para o treinamento com pendencia financeira.',
                'route' => $receiptPendencies[0]['detail_route'],
            ] : null,
            $inProgress[0] ?? null ? [
                'label' => 'Treinamento em andamento',
                'description' => 'Abrir os detalhes da atividade que ja esta acontecendo.',
                'route' => $inProgress[0]['detail_route'],
            ] : null,
            $upcoming[0] ?? null ? [
                'label' => 'Proximo treinamento',
                'description' => 'Revisar agenda, local e instrucoes do proximo encontro.',
                'route' => $upcoming[0]['detail_route'],
            ] : null,
            [
                'label' => 'Ver historico',
                'description' => 'Consultar treinamentos concluidos e progresso recente.',
                'route' => route('app.portal.student.history'),
            ],
            [
                'label' => 'Area de certificados',
                'description' => 'Acompanhar a futura disponibilizacao dos certificados.',
                'route' => route('app.portal.student.certificates'),
            ],
        ]));
    }
}
