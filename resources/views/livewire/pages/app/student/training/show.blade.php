@php
    $user = auth()->user();
    $isPaid = (float) preg_replace('/\D/', '', (string) $training->payment) > 0;
    $pixKey = 'eebrasil@eebrasil.org.br';
    $pixQr = asset('images/qrcode-pix-ee.webp');
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
                    class="mt-6 rounded-2xl border border-amber-200 bg-amber-50/70 p-5 text-sm text-amber-900 dark:border-amber-700 dark:bg-amber-950/30 dark:text-amber-200">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                        <img src="{{ $pixQr }}" alt="QR Code PIX EE Brasil"
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
                                    eebrasil@eebrasil.org.br
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

            <div
                class="mt-4 rounded-2xl border border-neutral-200 bg-neutral-50 p-5 text-sm text-neutral-700 dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-200">
                <form wire:submit="uploadPaymentReceipt" class="flex flex-col gap-3">
                    <div class="text-xs font-semibold uppercase text-neutral-500">Comprovante de pagamento</div>

                    <input type="file" wire:model="paymentReceipt" accept=".jpg,.jpeg,.png,.pdf"
                        class="w-full rounded-xl border border-neutral-200 bg-white p-2 text-sm text-neutral-700 file:me-4 file:rounded-lg file:border-0 file:bg-neutral-900 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-200 dark:file:bg-neutral-700" />

                    @error('paymentReceipt')
                        <div class="text-xs text-red-600 dark:text-red-400">{{ $message }}</div>
                    @enderror

                    @if ($paymentReceiptPath)
                        <div class="text-xs text-amber-700">
                            <span class="font-bold">Comprovante enviado.</span> Aguardando confirmação do professor.
                        </div>
                    @endif

                    <div class="flex flex-wrap items-center gap-3">
                        <flux:button variant="primary" type="submit" wire:loading.attr="disabled">
                            Enviar comprovante
                        </flux:button>

                        <x-app.action-message on="payment-receipt-uploaded">
                            Comprovante enviado.
                        </x-app.action-message>
                    </div>
                </form>
            </div>
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
