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
                <span>{{ $user?->birthdate?->format('d/m/Y') ?? 'Nao informado' }}</span>
            </div>
            <div class="flex flex-col gap-1">
                <span class="text-xs font-semibold uppercase text-neutral-500">Genero</span>
                <span>{{ $user?->gender_label ?? __('Não informado') }}</span>
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
                    class="mt-6 rounded-2xl border border-amber-200 bg-amber-50/70 p-5 text-sm text-amber-900 dark:border-amber-700 dark:bg-amber-950/30 dark:text-amber-200">
                    <div class="grid gap-4 lg:grid-cols-2">
                        <div class="rounded-xl border border-amber-200/80 bg-white/60 p-4">
                            <div class="mb-3 flex flex-col gap-1">
                                <div class="text-xs font-semibold uppercase text-amber-800 dark:text-amber-200">
                                    Passo 1: dados para pagamento
                                </div>
                                <div class="text-sm text-amber-900 dark:text-amber-200">
                                    Realize o pagamento do treinamento pelos dados abaixo.
                                </div>
                                <div class="text-sm font-semibold text-amber-900 dark:text-amber-100">
                                    Valor a pagar: {{ $training->payment ?? 'Gratuito' }}
                                </div>
                            </div>

                            @if (!$paymentReceiptPath)
                                <div class="flex flex-col gap-4 sm:flex-row sm:items-center" data-payment-pix-card>
                                    <img src="{{ $pixQr }}" alt="QR Code PIX"
                                        class="h-36 w-36 rounded-xl border border-amber-200 bg-white p-1">
                                    <div class="space-y-2">
                                        <div class="text-xs font-semibold uppercase text-amber-800 dark:text-amber-200">
                                            Pagamento via PIX
                                        </div>
                                        <div class="mt-3 flex flex-col relative gap-3 sm:flex-row sm:items-start">
                                            <span data-pix-key
                                                class="w-full rounded-xl bg-slate-50 px-3 py-2 text-sm text-slate-700 ring-1 ring-slate-300">
                                                {{ $pixKey }}
                                            </span>
                                            <span
                                                class="absolute inset-0 bottom-0 hidden items-center justify-center rounded-xl bg-sky-950/50 px-4 text-sm font-bold text-white backdrop-blur-[1px]"
                                                data-copy-feedback>
                                                Chave PIX copiada
                                            </span>
                                        </div>

                                        <div class="text-xs text-amber-700 dark:text-amber-300">
                                            Use o QR Code ou a chave para concluir o pagamento do treinamento.
                                        </div>
                                        <div>
                                            <x-src.btn-silver label="Copiar chave Pix" class="py-1.5! text-xs"
                                                data-copy-pix />
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="flex h-full flex-col justify-center gap-2 text-sm">
                                    <div class="text-xs font-semibold uppercase text-amber-800 dark:text-amber-200">
                                        Comprovante enviado
                                    </div>
                                    <div class="font-medium text-amber-900 dark:text-amber-200">
                                        O QR Code e os dados PIX ficam ocultos apos o envio do comprovante.
                                    </div>
                                    <div class="text-xs text-amber-700 dark:text-amber-300">
                                        Se precisar reenviar, exclua o comprovante atual para exibir os dados novamente.
                                    </div>
                                </div>
                            @endif

                            <div class="mt-4 border-t border-amber-200/70 pt-4">
                                <div class="flex flex-col gap-2">
                                    @if ($paymentReceiptPath)
                                        <div
                                            class="text-xs font-semibold uppercase tracking-wide text-sky-800 dark:text-sky-200">
                                            Pagamento em análise
                                        </div>
                                        <div class="text-sm font-medium text-sky-900 dark:text-sky-200">
                                            Recebemos seu comprovante e ele está aguardando a confirmação da coordenação
                                            do evento.
                                        </div>
                                        <div class="text-xs text-sky-700 dark:text-sky-300">
                                            Assim que a validação for concluída, seu status será atualizado automaticamente para
                                            pagamento confirmado.
                                        </div>
                                    @else
                                        <div
                                            class="text-xs font-semibold uppercase tracking-wide text-amber-800 dark:text-amber-200">
                                            Aguardando comprovante
                                        </div>
                                        <div class="text-sm font-medium text-amber-900 dark:text-amber-200">
                                            Após realizar o pagamento, envie o comprovante para iniciar a validação da
                                            coordenação do evento.
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div
                            class="rounded-2xl border border-neutral-200 bg-neutral-50 p-5 text-sm text-neutral-700 dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-200">
                            <form wire:submit="uploadPaymentReceipt" class="flex flex-col gap-3">
                                <div class="text-xs font-semibold uppercase text-neutral-500">Passo 2: comprovante de pagamento</div>

                                <input id="payment-receipt-input" type="file" wire:model="paymentReceipt"
                                    accept=".webp,.jpeg,.png,.pdf" class="hidden" />
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
                                            ['webp', 'jpeg', 'png'],
                                            true,
                                        );
                                        $selectedReceiptIsPdf = $selectedReceiptExtension === 'pdf';
                                    @endphp

                                    <label for="payment-receipt-input"
                                        class="cursor-pointer rounded-xl border border-neutral-200 bg-white p-3">
                                        <div class="mb-2 text-xs font-semibold uppercase text-neutral-500">
                                            {{ __('Arquivo selecionado') }}
                                        </div>

                                        @if ($selectedReceiptIsImage)
                                            <img src="{{ $paymentReceipt->temporaryUrl() }}"
                                                alt="{{ __('Pré-visualização do comprovante') }}"
                                                class="h-auto max-h-56 w-auto rounded-lg border border-neutral-200 object-cover" />
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
                                    </label>
                                @elseif ($paymentReceiptUrl)
                                    <label for="payment-receipt-input"
                                        class="cursor-pointer rounded-xl border border-neutral-200 bg-white p-3">
                                        <div class="mb-2 text-xs font-semibold uppercase text-neutral-500">
                                            {{ __('Comprovante enviado') }}
                                        </div>

                                        @if ($paymentReceiptIsImage)
                                            <img src="{{ $paymentReceiptUrl }}"
                                                alt="{{ __('Comprovante de pagamento') }}"
                                                class="h-auto max-h-56 w-auto rounded-lg border border-neutral-200 object-contain" />
                                        @elseif ($paymentReceiptIsPdf)
                                            <div class="flex flex-wrap items-center gap-3">
                                                <span
                                                    class="inline-flex rounded-md border border-slate-300 bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-700">
                                                    PDF
                                                </span>
                                                <a href="{{ $paymentReceiptUrl }}" target="_blank"
                                                    rel="noopener noreferrer"
                                                    class="text-xs font-semibold text-sky-700 underline">
                                                    {{ __('Abrir comprovante') }}
                                                </a>
                                            </div>
                                        @endif
                                    </label>
                                @elseif ($paymentReceiptPath)
                                    <div class="text-xs text-amber-700">
                                        <span
                                            class="font-bold">{{ __('Comprovante registrado, mas indisponível no momento.') }}</span>
                                    </div>
                                @else
                                    <label for="payment-receipt-input"
                                        class="group relative block cursor-pointer overflow-hidden rounded-xl border border-neutral-200 bg-white">
                                        <img src="{{ asset('images/paymentPIX.webp') }}"
                                            alt="{{ __('Clique aqui para enviar o comprovante de pagamento') }}"
                                            class="h-auto max-h-72 w-full object-contain transition duration-300 group-hover:scale-[1.01]" />
                                        <div
                                            class="absolute inset-0 flex items-end justify-center bg-gradient-to-t from-black/55 via-black/25 to-transparent p-4 text-center text-sm font-semibold text-white">
                                            Clique aqui para enviar o comprovante de pagamento
                                        </div>
                                    </label>
                                @endif

                                <div class="flex flex-wrap items-center gap-3">
                                    <flux:button variant="primary" type="submit" wire:loading.attr="disabled"
                                        :disabled="!$paymentReceipt">
                                        Enviar comprovante
                                    </flux:button>

                                    @if ($paymentReceipt)
                                        <x-src.btn-silver label="Remover arquivo selecionado"
                                            wire:click="clearSelectedPaymentReceipt" class="py-1.5! text-xs" />
                                    @elseif ($paymentReceiptPath)
                                        <x-src.btn-silver label="Excluir comprovante enviado"
                                            wire:click="removePaymentReceipt" class="py-1.5! text-xs" />
                                    @endif
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endif
        @endif

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
        (() => {
            if (window.__studentPixCopyHandlerInitialized) {
                return;
            }

            window.__studentPixCopyHandlerInitialized = true;

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

            const showFeedback = (copyFeedback) => {
                if (!copyFeedback) {
                    return;
                }

                copyFeedback.classList.remove('hidden');
                copyFeedback.classList.add('inline-flex');
                setTimeout(() => {
                    copyFeedback.classList.add('hidden');
                    copyFeedback.classList.remove('inline-flex');
                }, 2000);
            };

            document.addEventListener('click', async (event) => {
                const copyButton = event.target.closest('[data-copy-pix]');

                if (!copyButton) {
                    return;
                }

                const pixCard = copyButton.closest('[data-payment-pix-card]') ?? document;
                const pixKey = pixCard.querySelector('[data-pix-key]');
                const copyFeedback = pixCard.querySelector('[data-copy-feedback]');
                const pixValue = pixKey?.textContent?.trim() ?? '';

                if (pixValue === '') {
                    return;
                }

                try {
                    if (navigator.clipboard?.writeText) {
                        await navigator.clipboard.writeText(pixValue);
                        showFeedback(copyFeedback);

                        return;
                    }

                    if (fallbackCopy(pixValue)) {
                        showFeedback(copyFeedback);
                    }
                } catch (error) {
                    console.warn('Nao foi possivel copiar a chave PIX.', error);
                }
            });
        })();
    </script>
@endpush
