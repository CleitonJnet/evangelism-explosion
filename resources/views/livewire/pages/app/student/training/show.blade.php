@php
    $user = auth()->user();
    $isPaid = (float) preg_replace('/\D/', '', (string) $training->payment) > 0;
    $pixKey = $training->pixKeyForPayment();
    $pixQr = $training->pixQrCodeUrlForPayment();
@endphp

<div class="flex flex-col gap-6">
    <div
        class="rounded-2xl border border-neutral-200 bg-white p-6 shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
        <div class="flex flex-col gap-2">
            <div class="text-sm font-semibold text-neutral-500">Bem-vindo(a)</div>
            <div class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">{{ $user?->name }}</div>
        </div>
        <div class="mt-4 grid gap-3 text-sm text-neutral-700 dark:text-neutral-200 sm:grid-cols-2">
            <div class="flex flex-col gap-1">
                <span class="text-xs font-semibold uppercase text-neutral-500">E-mail</span>
                <span>{{ $user?->email }}</span>
            </div>
            <div class="flex flex-col gap-1">
                <span class="text-xs font-semibold uppercase text-neutral-500">Telefone</span>
                <span>{{ $user?->phone ?? 'Nao informado' }}</span>
            </div>
            <div class="flex flex-col gap-1">
                <span class="text-xs font-semibold uppercase text-neutral-500">Nascimento</span>
                <span>{{ $user?->birthdate ?? 'Nao informado' }}</span>
            </div>
            <div class="flex flex-col gap-1">
                <span class="text-xs font-semibold uppercase text-neutral-500">Genero</span>
                <span>
                    @if ($user?->gender == 'M')
                        {{ __('Male') }}
                    @elseif ($user?->gender == 'F')
                        {{ __('Femele') }}
                    @else
                        {{ __('Não Informado') }}
                    @endif
                </span>
            </div>
        </div>
    </div>

    <div
        class="rounded-2xl border border-neutral-200 bg-white p-6 shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
        <div class="flex flex-col gap-2">
            <span class="text-sm font-semibold text-neutral-500">Treinamento</span>
            <div class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">
                {{ $training->course?->type }}: {{ $training->course?->name }}
            </div>
            @if ($training->course?->slogan)
                <p class="text-sm text-neutral-600 dark:text-neutral-300">{{ $training->course?->slogan }}</p>
            @endif
        </div>

        <div class="mt-5 flex flex-wrap gap-3 text-sm">
            <span
                class="inline-flex items-center gap-1 rounded-full border border-neutral-200 bg-neutral-50 px-3 py-1 text-neutral-700 dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-200">
                Carga horaria: <strong
                    class="text-neutral-900 dark:text-neutral-100">{{ $workloadDuration ?? '00h' }}</strong>
            </span>
            <span
                class="inline-flex items-center gap-1 rounded-full border border-neutral-200 bg-neutral-50 px-3 py-1 text-neutral-700 dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-200">
                Investimento: <strong
                    class="text-neutral-900 dark:text-neutral-100">{{ $training->payment ?? 'Gratuito' }}</strong>
            </span>
        </div>

        @if ($isPaid)
            @if ($paymentConfirmed)
                <div
                    class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50/70 p-5 text-sm text-emerald-900 dark:border-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-200">
                    <div class="flex flex-col gap-2">
                        <div class="text-xs font-semibold uppercase text-emerald-800 dark:text-emerald-200">
                            Pagamento confirmado
                        </div>
                        <div class="text-sm">
                            Seu pagamento foi confirmado. Obrigado!
                        </div>
                    </div>
                </div>
            @else
                <div
                    class="mt-6 rounded-2xl border border-sky-200 bg-sky-50/80 p-5 text-sm text-sky-900 shadow-sm dark:border-sky-700 dark:bg-sky-950/30 dark:text-sky-200">
                    <div class="flex flex-col gap-2">
                        @if ($paymentReceiptPath)
                            <div class="text-xs font-semibold uppercase tracking-wide text-sky-800 dark:text-sky-200">
                                Pagamento em análise
                            </div>
                            <div class="text-sm font-medium">
                                Recebemos seu comprovante e ele está aguardando a confirmação da coordenação do evento.
                            </div>
                            <div class="text-xs text-sky-800/90 dark:text-sky-200/90">
                                Assim que a validação for concluída, seu status será atualizado automaticamente para
                                pagamento confirmado.
                            </div>
                        @else
                            <div class="text-xs font-semibold uppercase tracking-wide text-sky-800 dark:text-sky-200">
                                Aguardando comprovante
                            </div>
                            <div class="text-sm font-medium">
                                Após realizar o pagamento, envie o comprovante para iniciar a validação da coordenação
                                do evento.
                            </div>
                        @endif
                    </div>
                </div>

                <div
                    class="mt-6 rounded-2xl border border-amber-200 bg-amber-50/70 p-5 text-sm text-amber-900 dark:border-amber-700 dark:bg-amber-950/30 dark:text-amber-200">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                        <img src="{{ $pixQr }}" alt="QR Code PIX"
                            class="h-36 w-36 rounded-xl border border-amber-200 bg-white p-1">
                        <div class="space-y-2">
                            <div class="text-xs font-semibold uppercase text-amber-800 dark:text-amber-200">
                                Pagamento via PIX
                            </div>
                            {{-- <div class="text-base">
                                Chave: <span class="font-semibold">{{ $pixKey }}</span>
                            </div> --}}
                            <div class="mt-3 flex flex-col gap-3 sm:flex-row sm:items-start relative">
                                <span data-pix-key
                                    class="w-full rounded-xl bg-slate-50 px-3 py-2 text-sm text-slate-700 ring-1 ring-slate-300">
                                    {{ $pixKey }}
                                </span>
                                <span
                                    class="hidden inset-0 text-sm font-bold text-white absolute bottom-0 bg-sky-950/50 justify-center items-center px-4 rounded-xl backdrop-blur-[1px]"
                                    data-copy-feedback>
                                    Chave PIX copiada
                                </span>
                            </div>

                            <div class="text-xs text-amber-700 dark:text-amber-300">
                                Use o QR Code ou a chave para concluir o pagamento do treinamento.
                            </div>
                            <div>
                                <x-src.btn-silver label="Copiar chave Pix" class="py-1.5! text-xs" data-copy-pix />
                            </div>

                        </div>
                    </div>
                </div>
            @endif

            @if (!$paymentConfirmed)
                <div
                    class="mt-4 rounded-2xl border border-neutral-200 bg-neutral-50 p-5 text-sm text-neutral-700 dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-200">
                    <form wire:submit="uploadPaymentReceipt" class="flex flex-col gap-3">
                        <div class="text-xs font-semibold uppercase text-neutral-500">Comprovante de pagamento</div>

                        <input type="file" wire:model="paymentReceipt" accept=".webp,.jpeg,.webp,.webp,.pdf"
                            class="w-full rounded-xl border border-neutral-200 bg-white p-2 text-sm text-neutral-700 file:me-4 file:rounded-lg file:border-0 file:bg-neutral-900 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-200 dark:file:bg-neutral-700" />
                        <div class="text-[11px] text-neutral-500">
                            {{ __('Formatos aceitos: webp, PNG, WEBP ou PDF (até 5MB).') }}
                        </div>

                        @error('paymentReceipt')
                            <div class="text-xs text-red-600 dark:text-red-400">{{ $message }}</div>
                        @enderror

                        @if ($paymentReceipt)
                            @php
                                $selectedReceiptExtension = strtolower(
                                    (string) $paymentReceipt->getClientOriginalExtension(),
                                );
                                $selectedReceiptName = (string) $paymentReceipt->getClientOriginalName();
                                $selectedReceiptIsImage = in_array(
                                    $selectedReceiptExtension,
                                    ['webp', 'jpeg', 'png', 'webp'],
                                    true,
                                );
                                $selectedReceiptIsPdf = $selectedReceiptExtension === 'pdf';
                            @endphp

                            <div class="rounded-xl border border-neutral-200 bg-white p-3">
                                <div class="mb-2 text-xs font-semibold uppercase text-neutral-500">
                                    {{ __('Arquivo selecionado') }}
                                </div>

                                @if ($selectedReceiptIsImage)
                                    <img src="{{ $paymentReceipt->temporaryUrl() }}"
                                        alt="{{ __('Pré-visualização do comprovante') }}"
                                        class="h-auto max-h-56 w-auto rounded-lg border border-neutral-200 object-contain" />
                                @elseif ($selectedReceiptIsPdf)
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex h-14 w-14 items-center justify-center rounded-lg border border-slate-300 bg-slate-100 text-xs font-bold text-slate-700">
                                            PDF
                                        </div>
                                        <div class="text-xs font-medium text-slate-700">
                                            {{ $selectedReceiptName }}
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @elseif ($paymentReceiptUrl)
                            <div class="rounded-xl border border-neutral-200 bg-white p-3">
                                <div class="mb-2 text-xs font-semibold uppercase text-neutral-500">
                                    {{ __('Comprovante enviado') }}
                                </div>

                                @if ($paymentReceiptIsImage)
                                    <img src="{{ $paymentReceiptUrl }}" alt="{{ __('Comprovante de pagamento') }}"
                                        class="h-auto max-h-56 w-auto rounded-lg border border-neutral-200 object-contain" />
                                @elseif ($paymentReceiptIsPdf)
                                    <div class="flex flex-wrap items-center gap-3">
                                        <span
                                            class="inline-flex rounded-md border border-slate-300 bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-700">
                                            PDF
                                        </span>
                                        <a href="{{ $paymentReceiptUrl }}" target="_blank" rel="noopener noreferrer"
                                            class="text-xs font-semibold text-sky-700 underline">
                                            {{ __('Abrir comprovante') }}
                                        </a>
                                    </div>
                                @endif
                            </div>
                        @elseif ($paymentReceiptPath)
                            <div class="text-xs text-amber-700">
                                <span
                                    class="font-bold">{{ __('Comprovante registrado, mas indisponível no momento.') }}</span>
                            </div>
                        @endif

                        <div class="flex flex-wrap items-center gap-3">
                            <flux:button variant="primary" type="submit" wire:loading.attr="disabled">
                                Enviar comprovante
                            </flux:button>

                            <x-app.action-message on="payment-receipt-uploaded">
                                Comprovante enviado. Pagamento em análise.
                            </x-app.action-message>
                        </div>
                    </form>
                </div>
            @endif
        @endif

        <div
            class="mt-6 rounded-2xl border border-neutral-200 bg-neutral-50 p-5 text-sm text-neutral-700 dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-200">
            <div class="flex flex-col gap-2">
                <div class="text-xs font-semibold uppercase text-neutral-500">{{ __('OJT') }}</div>
                <div class="text-lg font-semibold text-neutral-900 dark:text-neutral-100">{{ __('OJT Sessions') }}
                </div>
                <div class="text-sm text-neutral-600 dark:text-neutral-300">
                    {{ __('Upcoming sessions assigned to you.') }}
                </div>
            </div>

            <div class="mt-4 flex flex-col gap-4">
                @forelse ($ojtAssignments as $assignment)
                    <div class="rounded-xl border border-neutral-200 bg-white p-4 text-sm text-neutral-700 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-200"
                        wire:key="student-ojt-session-{{ $assignment['id'] }}">
                        <div class="flex flex-wrap items-center gap-3 text-sm text-neutral-600 dark:text-neutral-300">
                            <span class="font-semibold text-neutral-900 dark:text-neutral-100">
                                {{ __('Week') }} {{ $assignment['week_number'] }}
                            </span>
                            <span>{{ $assignment['date'] }}</span>
                            @if ($assignment['starts_at'])
                                <span>
                                    {{ $assignment['starts_at'] }}
                                    @if ($assignment['ends_at'])
                                        - {{ $assignment['ends_at'] }}
                                    @endif
                                </span>
                            @endif
                        </div>

                        <div class="mt-3 grid gap-3 sm:grid-cols-2">
                            <div class="flex flex-col gap-1">
                                <span
                                    class="text-xs font-semibold uppercase text-neutral-500">{{ __('Mentor') }}</span>
                                <span class="font-semibold text-neutral-900 dark:text-neutral-100">
                                    {{ $assignment['mentor_name'] ?? __('Mentor') }}
                                </span>
                            </div>
                            <div class="flex flex-col gap-1">
                                <span
                                    class="text-xs font-semibold uppercase text-neutral-500">{{ __('Teammate') }}</span>
                                <span class="font-semibold text-neutral-900 dark:text-neutral-100">
                                    {{ $assignment['teammate_name'] ?? __('Trainee') }}
                                </span>
                            </div>
                        </div>

                        @if ($assignment['report'])
                            <div
                                class="mt-4 rounded-xl border border-neutral-200 bg-neutral-50 p-4 text-xs text-neutral-700 dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-200">
                                <div class="text-xs font-semibold uppercase text-neutral-500">
                                    {{ __('Report Summary') }}
                                </div>
                                <div class="mt-3 grid gap-3 sm:grid-cols-3">
                                    <div>
                                        {{ __('Gospel presentations') }}:
                                        <span class="font-semibold text-neutral-900 dark:text-neutral-100">
                                            {{ $assignment['report']['gospel_presentations'] }}
                                        </span>
                                    </div>
                                    <div>
                                        {{ __('Listeners') }}:
                                        <span class="font-semibold text-neutral-900 dark:text-neutral-100">
                                            {{ $assignment['report']['listeners_count'] }}
                                        </span>
                                    </div>
                                    <div>
                                        {{ __('Decisions') }}:
                                        <span class="font-semibold text-neutral-900 dark:text-neutral-100">
                                            {{ $assignment['report']['results_decisions'] }}
                                        </span>
                                    </div>
                                    <div>
                                        {{ __('Interested') }}:
                                        <span class="font-semibold text-neutral-900 dark:text-neutral-100">
                                            {{ $assignment['report']['results_interested'] }}
                                        </span>
                                    </div>
                                    <div>
                                        {{ __('Rejection') }}:
                                        <span class="font-semibold text-neutral-900 dark:text-neutral-100">
                                            {{ $assignment['report']['results_rejection'] }}
                                        </span>
                                    </div>
                                    <div>
                                        {{ __('Assurance') }}:
                                        <span class="font-semibold text-neutral-900 dark:text-neutral-100">
                                            {{ $assignment['report']['results_assurance'] }}
                                        </span>
                                    </div>
                                    <div>
                                        {{ __('Follow-up scheduled') }}:
                                        <span class="font-semibold text-neutral-900 dark:text-neutral-100">
                                            {{ $assignment['report']['follow_up_scheduled'] ? __('Yes') : __('No') }}
                                        </span>
                                    </div>
                                </div>
                                @if ($assignment['report']['lesson_learned'])
                                    <div class="mt-3 text-xs text-neutral-600 dark:text-neutral-300">
                                        <span class="font-semibold">{{ __('Lesson learned') }}:</span>
                                        {{ $assignment['report']['lesson_learned'] }}
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="mt-3 text-xs text-neutral-500 dark:text-neutral-400">
                                {{ __('No report submitted yet.') }}
                            </div>
                        @endif
                    </div>
                @empty
                    <div
                        class="rounded-xl border border-neutral-200 bg-white p-4 text-sm text-neutral-600 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300">
                        {{ __('No upcoming OJT sessions assigned yet.') }}
                    </div>
                @endforelse
            </div>
        </div>

        <div class="mt-6 flex flex-wrap gap-3">
            @foreach ($training->eventDates as $dateEvent)
                <div wire:key="date-{{ $dateEvent->id }}"
                    class="rounded-xl border border-neutral-200 bg-neutral-50 p-4 text-sm text-neutral-700 dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-200"
                    style="flex: 1 1 350px;">
                    <span class="font-semibold text-neutral-900 dark:text-neutral-100">
                        {{ \Illuminate\Support\Str::ucfirst(\Carbon\Carbon::parse($dateEvent->date)->locale('pt_BR')->isoFormat('dddd')) }}
                        - {{ date('d/m', strtotime($dateEvent->date)) }}
                    </span>
                    <span class="text-neutral-600 dark:text-neutral-300">
                        das {{ date('H:i', strtotime($dateEvent->start_time)) }} as
                        {{ date('H:i', strtotime($dateEvent->end_time)) }}
                    </span>
                </div>
            @endforeach
        </div>

        <div class="mt-6 flex flex-wrap gap-4 text-sm text-neutral-700 dark:text-neutral-200">
            <div style="flex: 1 1 350px;"
                class="rounded-xl border border-neutral-200 bg-neutral-50 p-4 dark:border-neutral-700 dark:bg-neutral-800">
                <div class="text-xs font-semibold uppercase text-neutral-500">Local</div>
                <div class="mt-2 font-semibold text-neutral-900 dark:text-neutral-100">{{ $training->church?->name }}
                </div>
                <div class="mt-1">{{ $churchAddress ?: 'Endereco nao informado' }}</div>
            </div>
            <div style="flex: 1 1 auto;"
                class="rounded-xl border border-neutral-200 bg-neutral-50 p-4 dark:border-neutral-700 dark:bg-neutral-800">
                <div class="text-xs font-semibold uppercase text-neutral-500">Contato</div>
                <div class="mt-2 font-semibold text-neutral-900 dark:text-neutral-100">{{ $training->coordinator }}
                </div>
                <div class="mt-1">{{ $training->phone }}</div>
                <div class="truncate">{{ $training->email }}</div>
            </div>
        </div>
    </div>
</div>

@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const copyButton = document.querySelector('[data-copy-pix]');
            const pixKey = document.querySelector('[data-pix-key]');
            const copyFeedback = document.querySelector('[data-copy-feedback]');

            if (!copyButton || !pixKey || !copyFeedback) {
                return;
            }

            const showFeedback = () => {
                copyFeedback.classList.remove('hidden');
                copyFeedback.classList.add('inline-flex');
                setTimeout(() => {
                    copyFeedback.classList.add('hidden');
                    copyFeedback.classList.remove('inline-flex');
                }, 2000);
            };

            const fallbackCopy = (text) => {
                const tempInput = document.createElement('textarea');
                tempInput.value = text;
                tempInput.setAttribute('readonly', '');
                tempInput.style.position = 'absolute';
                tempInput.style.left = '-9999px';
                document.body.appendChild(tempInput);
                tempInput.select();
                tempInput.setSelectionRange(0, tempInput.value.length);
                const success = document.execCommand('copy');
                document.body.removeChild(tempInput);
                return success;
            };

            copyButton.addEventListener('click', async () => {
                const pixValue = pixKey.textContent?.trim() ?? '';
                if (!pixValue) {
                    return;
                }

                try {
                    if (navigator.clipboard?.writeText) {
                        await navigator.clipboard.writeText(pixValue);
                        showFeedback();
                        return;
                    }

                    if (fallbackCopy(pixValue)) {
                        showFeedback();
                    }
                } catch (error) {
                    console.warn('Nao foi possivel copiar a chave PIX.', error);
                }
            });
        });
    </script>
@endpush
