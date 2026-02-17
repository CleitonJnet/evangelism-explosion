<div wire:loading.class="pointer-events-none"
    wire:target="togglePayment,toggleAccredited,toggleKit,removeRegistration,openReceiptModal">
    @php
        $genericReceiptThumbnail = asset('images/svg/qr-code-icon.svg');
    @endphp
    <section class="flex gap-4 flex-wrap">
        <article
            class="rounded-2xl border border-sky-950/45 bg-linear-to-br from-slate-100 via-white to-slate-200 px-4 py-3 flex-auto min-w-fit">
            <div class="text-xs text-slate-500">{{ __('Total de inscritos') }}</div>
            <div class="text-3xl font-bold text-slate-900">{{ $totalRegistrations }}</div>
        </article>
        <article
            class="rounded-2xl border border-sky-950/45 bg-linear-to-br from-slate-100 via-white to-slate-200 px-4 py-3 flex-auto min-w-fit">
            <div class="text-xs text-slate-500">{{ __('Igrejas representadas') }}</div>
            <div class="text-3xl font-bold text-slate-900">{{ $totalChurches }}</div>
        </article>
        <article
            class="rounded-2xl border border-sky-950/45 bg-linear-to-br from-slate-100 via-white to-slate-200 px-4 py-3 flex-auto min-w-fit">
            <div class="text-xs text-slate-500">{{ __('Pastores inscritos') }}</div>
            <div class="text-3xl font-bold text-slate-900">{{ $totalPastors }}</div>
        </article>
        <article
            class="rounded-2xl border border-sky-950/45 bg-linear-to-br from-slate-100 via-white to-slate-200 px-4 py-3 flex-auto min-w-fit">
            <div class="text-xs text-slate-500">{{ __('Comprovantes marcados') }}</div>
            <div class="text-3xl font-bold text-slate-900">{{ $totalPaymentReceipts }}</div>
        </article>
        <article
            class="rounded-2xl border border-sky-950/45 bg-linear-to-br from-slate-100 via-white to-slate-200 px-4 py-3 flex-auto min-w-fit">
            <div class="text-xs text-slate-500">{{ __('Kits entregues') }}</div>
            <div class="text-3xl font-bold text-slate-900">{{ $totalKits }}</div>
        </article>
        <article
            class="rounded-2xl border border-sky-950/45 bg-linear-to-br from-slate-100 via-white to-slate-200 px-4 py-3 flex-auto min-w-fit">
            <div class="text-xs text-slate-500">{{ __('Credenciados') }}</div>
            <div class="text-3xl font-bold text-slate-900">{{ $totalAccredited }}</div>
        </article>
    </section>

    <section class="mt-4 grid gap-5">
        @if ($pendingChurchTempsCount > 0)
            <article
                class="rounded-2xl border border-amber-300 bg-amber-50 px-4 py-3 text-amber-900 flex flex-wrap items-center justify-between gap-3">
                <div class="text-sm font-semibold">
                    {{ $pendingChurchTempsCount }} {{ __('igreja(s) pendente(s) de validação para este treinamento.') }}
                </div>
                <x-src.btn-gold type="button" wire:click="$dispatch('open-church-temp-review-modal')" class="px-4 py-2">
                    {{ __('Validate Churches') }}
                </x-src.btn-gold>
            </article>
        @endif

        @forelse ($churchGroups as $churchGroup)
            <article x-data="{ open: true }"
                class="rounded-2xl border border-slate-300 bg-white shadow-sm overflow-hidden"
                wire:key="church-group-{{ $churchGroup['key'] }}">
                <header
                    class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 bg-linear-to-r from-slate-100 to-white px-4 py-3">
                    <div class="min-w-0">
                        <h2 class="truncate text-base font-semibold text-slate-900">
                            {{ $churchGroup['church_name'] }}
                        </h2>
                        <p class="text-xs text-slate-500">{{ $churchGroup['summary'] }}</p>
                    </div>

                    <button type="button"
                        class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-3 py-1 text-xs font-semibold text-slate-700 transition hover:bg-slate-100 cursor-pointer"
                        x-on:click="open = !open"
                        x-text="open ? '{{ __('Ocultar lista') }}' : '{{ __('Exibir lista') }}'"></button>
                </header>

                <div x-show="open" class="overflow-hidden rounded-b-2xl">
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-5xl text-left text-sm">
                            <thead class="bg-linear-to-b from-sky-200 to-sky-300 text-xs uppercase text-slate-700">
                                <tr class="border-b border-slate-300">
                                    <th class="px-3 py-2 w-20">{{ __('Pastor') }}</th>
                                    <th class="px-3 py-2">{{ __('Nome') }}</th>
                                    <th class="px-3 py-2">{{ __('Contato') }}</th>
                                    <th class="px-3 py-2 w-44 text-center">{{ __('Comprovante') }}</th>
                                    <th class="px-3 py-2 w-28 text-center">{{ __('Kit') }}</th>
                                    <th class="px-3 py-2 w-32 text-center">{{ __('Credenciado') }}</th>
                                    <th class="px-3 py-2 w-20 text-center">{{ __('Ação') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                @foreach ($churchGroup['registrations'] as $registration)
                                    <tr class="odd:bg-white even:bg-slate-50/50 hover:bg-sky-50/40"
                                        wire:key="registration-{{ $registration['id'] }}">
                                        <td class="px-3 py-2">
                                            <span
                                                class="inline-flex rounded-md px-2 py-1 text-xs font-semibold {{ $registration['is_pastor'] ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-600' }}">
                                                {{ $registration['pastor_label'] }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-2">
                                            <div class="font-semibold select-text text-slate-900">
                                                {{ $registration['name'] }}
                                            </div>
                                        </td>
                                        <td class="px-3 py-2">
                                            <div class="grid gap-0.5 text-xs text-slate-600 select-text">
                                                <span>{{ $registration['email'] ?: __('Sem email') }}</span>
                                                <span>{{ $registration['phone'] ?: __('Sem telefone') }}</span>
                                            </div>
                                        </td>
                                        <td class="px-3 py-2 align-middle">
                                            <div class="grid justify-items-center">
                                                @php
                                                    $paymentBorderClass = $registration['payment_confirmed']
                                                        ? 'border-emerald-500'
                                                        : ($registration['has_payment_receipt']
                                                            ? 'border-amber-500'
                                                            : 'border-red-500');
                                                    $paymentStatusTooltip = $registration['payment_confirmed']
                                                        ? __('Pagamento confirmado')
                                                        : ($registration['has_payment_receipt']
                                                            ? __('Comprovante enviado. Pagamento em análise')
                                                            : __('Pagamento pendente. Comprovante não enviado'));
                                                @endphp
                                                <button type="button"
                                                    class="group relative cursor-pointer rounded-xl border-4 p-0.5 {{ $paymentBorderClass }}"
                                                    aria-label="{{ $paymentStatusTooltip }}"
                                                    wire:click="openReceiptModal({{ $registration['id'] }})">
                                                    @if ($registration['has_payment_receipt'] && $registration['payment_receipt_is_image'])
                                                        <img src="{{ $registration['payment_receipt_url'] }}"
                                                            alt="{{ __('Comprovante de pagamento') }}"
                                                            class="h-9 w-9 rounded-lg object-cover shadow-sm transition group-hover:scale-105">
                                                    @elseif ($registration['has_payment_receipt'] && $registration['payment_receipt_is_pdf'])
                                                        <div
                                                            class="flex h-9 w-9 items-center justify-center rounded-lg bg-slate-100 text-[11px] font-bold text-slate-700 shadow-sm transition group-hover:scale-105">
                                                            PDF
                                                        </div>
                                                    @else
                                                        <div
                                                            class="flex h-9 w-9 items-center justify-center rounded-lg bg-slate-100 p-2 shadow-sm transition group-hover:scale-105">
                                                            <img src="{{ $genericReceiptThumbnail }}"
                                                                alt="{{ __('Sem comprovante') }}"
                                                                class="h-full w-full object-contain">
                                                        </div>
                                                    @endif
                                                    <span
                                                        class="pointer-events-none absolute -top-2 left-1/2 z-20 w-max max-w-44 -translate-x-1/2 -translate-y-full rounded-md bg-slate-900 px-2 py-1 text-[10px] font-semibold text-white opacity-0 shadow-lg transition duration-150 group-hover:opacity-100 group-focus-visible:opacity-100">
                                                        {{ $paymentStatusTooltip }}
                                                    </span>
                                                </button>
                                            </div>
                                        </td>
                                        <td class="px-3 py-2 align-top">
                                            <div class="grid justify-items-center">
                                                <x-app.switch-schedule :label="__('Kit')" :key="'kit-' . $registration['id']"
                                                    :checked="$registration['kit']"
                                                    wire:change="toggleKit({{ $registration['id'] }}, $event.target.checked)"
                                                    wire:loading.attr="disabled" wire:target="toggleKit" />
                                            </div>
                                        </td>
                                        <td class="px-3 py-2 align-top">
                                            <div class="grid justify-items-center">
                                                <x-app.switch-schedule :label="__('Cred.')" :key="'accredited-' . $registration['id']"
                                                    :checked="$registration['accredited']"
                                                    wire:change="toggleAccredited({{ $registration['id'] }}, $event.target.checked)"
                                                    wire:loading.attr="disabled" wire:target="toggleAccredited" />
                                            </div>
                                        </td>
                                        <td class="px-3 py-2 text-center">
                                            <button type="button"
                                                class="inline-flex items-center justify-center rounded-lg border border-red-200 bg-red-50 px-2 py-1 text-xs font-semibold text-red-700 transition hover:bg-red-100 cursor-pointer"
                                                x-on:click.prevent="if (window.confirm('{{ __('Deseja realmente remover este inscrito deste evento?') }}')) { $wire.removeRegistration({{ $registration['id'] }}) }"
                                                wire:loading.attr="disabled" wire:target="removeRegistration">
                                                {{ __('Remover') }}
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </article>
        @empty
            <article
                class="rounded-2xl border border-slate-300 bg-linear-to-br from-slate-100 via-white to-slate-200 p-6 text-sm text-slate-700">
                <h2 class="text-base font-semibold text-slate-900">{{ __('Nenhum inscrito encontrado') }}</h2>
                <p class="mt-2">
                    {{ __('Ainda nao existem participantes vinculados a este evento. Quando houver inscrições, voce podera atualizar comprovante, credenciamento e kit nesta tela.') }}
                </p>
            </article>
        @endforelse
    </section>

    <flux:modal name="training-payment-receipt-modal" wire:model="showReceiptModal" class="max-w-4xl">
        <div class="space-y-4">
            <div>
                <flux:heading size="lg">{{ __('Comprovante de pagamento') }}</flux:heading>
                <flux:subheading>{{ $selectedRegistrationName }}</flux:subheading>
            </div>

            @if ($selectedHasPaymentReceipt && $selectedPaymentReceiptUrl)
                <div class="max-h-[75vh] overflow-auto rounded-xl border border-slate-200 bg-slate-50 p-2">
                    @if ($selectedPaymentReceiptIsImage)
                        <img src="{{ $selectedPaymentReceiptUrl }}" alt="{{ __('Comprovante de pagamento') }}"
                            class="mx-auto h-auto max-h-[70vh] w-auto rounded-lg object-contain">
                    @elseif ($selectedPaymentReceiptIsPdf)
                        <iframe src="{{ $selectedPaymentReceiptUrl }}" class="h-[70vh] w-full rounded-lg bg-white"
                            title="{{ __('Comprovante em PDF') }}"></iframe>
                    @else
                        <iframe src="{{ $selectedPaymentReceiptUrl }}" class="h-[70vh] w-full rounded-lg bg-white"
                            title="{{ __('Comprovante de pagamento') }}"></iframe>
                    @endif
                </div>

                <div class="flex justify-end">
                    <a href="{{ $selectedPaymentReceiptUrl }}" target="_blank" rel="noopener noreferrer"
                        class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100">
                        {{ __('Abrir em nova aba') }}
                    </a>
                </div>
            @else
                <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                    {{ __('Este aluno ainda não enviou comprovante válido.') }}
                </div>
            @endif

            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                <div class="text-xs font-semibold text-slate-600">{{ __('Confirmacao do professor') }}</div>
                <div class="mt-2 flex items-center justify-between gap-3">
                    <div class="text-sm text-slate-700">
                        {{ __('Marque somente após validar o comprovante enviado.') }}
                    </div>

                    @if ($selectedRegistrationId)
                        <x-app.switch-schedule :label="__('Pago')" :key="'payment-modal-' . $selectedRegistrationId" :checked="$selectedPaymentConfirmed"
                            wire:change="togglePayment({{ $selectedRegistrationId }}, $event.target.checked)"
                            wire:loading.attr="disabled" wire:target="togglePayment" />
                    @endif
                </div>

                @error('paymentConfirmation')
                    <div class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</div>
                @enderror
            </div>

            <div class="flex justify-end">
                <flux:button type="button" variant="ghost" wire:click="closeReceiptModal">
                    {{ __('Fechar') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>

    <livewire:pages.app.teacher.training.church-temp-review-modal :training="$training"
        wire:key="church-temp-review-modal-{{ $training->id }}" />
    <livewire:pages.app.teacher.training.approve-church-temp-modal :training="$training"
        wire:key="approve-church-temp-modal-{{ $training->id }}" />
</div>
