<?php

use App\Models\Training;
use Illuminate\Support\Collection;
use Livewire\Volt\Component;

new class extends Component {
    public function mount(): void
    {
        if (!auth()->user()) {
            abort(401);
        }
    }

    /**
     * @return Collection<int, Training>
     */
    public function getTrainingsProperty(): Collection
    {
        $user = auth()->user();

        if (!$user) {
            return collect();
        }

        return Training::query()
            ->with(['course', 'church', 'students' => fn($query) => $query->whereKey($user->id), 'eventDates' => fn($query) => $query->orderBy('date')->orderBy('start_time')])
            ->whereHas('students', fn($query) => $query->whereKey($user->id))
            ->get()
            ->sortBy(function (Training $training) {
                $firstDate = $training->eventDates->first();

                if (!$firstDate) {
                    return '9999-12-31 23:59:59';
                }

                return sprintf('%s %s', $firstDate->date, $firstDate->start_time ?? '00:00:00');
            })
            ->values();
    }
}; ?>

<div class="flex flex-col gap-6">
    <div class="flex flex-col gap-2">
        <h1 class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">Meus treinamentos</h1>
        <p class="text-sm text-neutral-600 dark:text-neutral-300">
            Confira os treinamentos em que voce esta inscrito.
        </p>
    </div>

    @if ($this->trainings->isEmpty())
        <div
            class="rounded-2xl border border-neutral-200 bg-white p-6 text-sm text-neutral-600 shadow-sm dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300">
            Voce ainda nao se inscreveu em nenhum treinamento.
        </div>
    @else
        <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($this->trainings as $training)
                @php
                    $courseType = $training->course?->type ?? 'Treinamento';
                    $courseName = $training->course?->name ?? 'Nao informado';
                    $churchName = $training->church?->name ?? 'Base nao informada';
                    $dates = $training->eventDates;
                    $firstDate = $dates->first();
                    $isPaid = (float) preg_replace('/\D/', '', (string) $training->payment) > 0;
                    $pixKey = $training->pixKeyForPayment();
                    $pixQr = $training->pixQrCodeUrlForPayment();
                    $enrollment = $training->students->first();
                    $paymentConfirmed = (bool) $enrollment?->pivot?->payment;
                    $paymentReceiptPath = $enrollment?->pivot?->payment_receipt;
                    $addressParts = array_filter([
                        $training?->street,
                        $training?->number,
                        $training?->district,
                        $training?->city,
                        $training?->state,
                    ]);
                    $address = $addressParts ? implode(', ', $addressParts) : 'Endereco nao informado';
                @endphp

                <div wire:key="training-{{ $training->id }}"
                    class="flex h-full flex-col gap-4 rounded-2xl border border-neutral-200 bg-white p-6 shadow-sm transition hover:shadow-md dark:border-neutral-700 dark:bg-neutral-900">
                    <div class="flex items-start justify-between gap-3">
                        <div class="text-lg font-semibold text-neutral-900 dark:text-neutral-100 uppercase">
                            {{ $courseType }}: {{ $courseName }}
                        </div>
                        <span
                            class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 dark:border-emerald-800 dark:bg-emerald-950 dark:text-emerald-200">
                            Inscrito
                        </span>
                    </div>

                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-semibold uppercase text-neutral-500">Base</span>
                        <span class="font-semibold text-neutral-900 dark:text-neutral-100">{{ $churchName }}</span>
                        <span class="text-sm">{{ $address }}</span>
                    </div>

                    @if ($dates->isNotEmpty())
                        <div class="flex flex-col gap-2 text-sm text-neutral-700 dark:text-neutral-200">
                            <span class="text-xs font-semibold uppercase text-neutral-500">Data de início</span>
                            <ul class="flex flex-col gap-1">
                                <li class="flex items-center gap-2 text-neutral-600 dark:text-neutral-300">
                                    <span class="font-semibold text-neutral-900 dark:text-neutral-100">
                                        {{ \Illuminate\Support\Carbon::parse($firstDate->date)->format('d/m') }}
                                    </span>
                                    <span>
                                        {{ $firstDate->start_time ? \Illuminate\Support\Carbon::parse($firstDate->start_time)->format('H:i') : '--:--' }}
                                        -
                                        {{ $firstDate->end_time ? \Illuminate\Support\Carbon::parse($firstDate->end_time)->format('H:i') : '--:--' }}
                                    </span>
                                </li>
                            </ul>
                        </div>
                    @endif

                    @if ($isPaid)
                        @if ($paymentConfirmed)
                            <div
                                class="rounded-2xl border border-emerald-200 bg-emerald-50/70 p-4 text-sm text-emerald-900 dark:border-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-200">
                                <div class="flex flex-col gap-2">
                                    <div class="text-xs font-semibold uppercase text-emerald-800 dark:text-emerald-200">
                                        Pagamento confirmado
                                    </div>
                                    <div class="text-sm font-medium">
                                        Seu pagamento foi confirmado.
                                    </div>
                                </div>
                            </div>
                        @else
                            {{-- <div
                                class="rounded-2xl border border-amber-200 bg-amber-50/70 p-4 text-sm text-amber-900 dark:border-amber-700 dark:bg-amber-950/30 dark:text-amber-200">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                                    <img src="{{ $pixQr }}" alt="QR Code PIX"
                                        class="h-16 w-16 rounded-xl border border-amber-200 bg-white p-1">
                                    <div class="space-y-1">
                                        <div class="text-xs font-semibold uppercase text-amber-800 dark:text-amber-200">
                                            Pagamento via PIX
                                        </div>
                                        <div class="text-sm">
                                            Chave: <span class="font-semibold">{{ $pixKey }}</span>
                                        </div>
                                        <div class="text-xs text-amber-700 dark:text-amber-300">
                                            Use o QR Code ou a chave para concluir o pagamento.
                                        </div>
                                    </div>
                                </div>
                            </div> --}}

                            <div
                                class="rounded-2xl border border-sky-200 bg-sky-50/80 p-4 text-sm text-sky-900 shadow-sm dark:border-sky-700 dark:bg-sky-950/30 dark:text-sky-200">
                                <div class="flex flex-col gap-2">
                                    @if ($paymentReceiptPath)
                                        <div
                                            class="text-xs font-semibold uppercase tracking-wide text-sky-800 dark:text-sky-200">
                                            Pagamento em analise
                                        </div>
                                        <div class="text-sm font-medium">
                                            Recebemos seu comprovante e ele esta aguardando a confirmacao da coordenacao
                                            do evento.
                                        </div>
                                        <div class="text-xs text-sky-800/90 dark:text-sky-200/90">
                                            Assim que a validacao for concluida, seu status sera atualizado
                                            automaticamente para
                                            pagamento confirmado.
                                        </div>
                                    @else
                                        <div
                                            class="text-xs font-semibold uppercase tracking-wide text-sky-800 dark:text-sky-200">
                                            Aguardando comprovante
                                        </div>
                                        <div class="text-sm font-medium">
                                            Apos realizar o pagamento, envie o comprovante para iniciar a validacao da
                                            coordenacao
                                            do evento.
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    @endif

                    <div class="mt-auto">
                        <a href="{{ route('app.student.training.show', $training) }}"
                            class="inline-flex items-center justify-center rounded-xl border border-neutral-200 bg-neutral-100 px-4 py-2 text-sm font-semibold text-neutral-800 transition hover:bg-neutral-200 dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-100 dark:hover:bg-neutral-700">
                            Ver detalhes
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
