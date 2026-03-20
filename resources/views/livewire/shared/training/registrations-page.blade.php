<div wire:loading.class="pointer-events-none"
    wire:target="togglePayment,toggleAccredited,toggleKit,removeRegistration,openReceiptModal,uploadSelectedPaymentReceipt,clearSelectedPaymentReceiptUpload">
    @php
        $genericReceiptThumbnail = asset('images/svg/qr-code-icon.svg');
    @endphp

    <x-src.toolbar.nav :title="__('Gerenciamento de inscrições')" :description="__('Atualize comprovante, credenciamento e entrega de kit com poucos cliques.')" justify="justify-between">
        <div class="flex flex-wrap gap-2 items-center">
            <x-src.toolbar.button :href="route($this->contextRoute('show'), $training)" :label="__('Detalhes do Evento')" icon="eye" :tooltip="__('Voltar para o treinamento')"
                class="bg-sky-900! text-slate-100! border-sky-700! hover:bg-sky-800!" />
            @if ($this->contextComponent('createParticipantRegistrationModal') && ($capabilities['canEdit'] ?? false))
                <x-src.toolbar.button href="#" :label="__('Novo inscrito')" icon="plus" :tooltip="__('Abrir o fluxo de acesso e inscrição do evento para registrar um aluno')"
                    x-on:click.prevent="$dispatch('open-create-participant-registration-modal', { trainingId: {{ $training->id }} })"
                    class="!bg-emerald-100 !text-emerald-900 !border-emerald-300 hover:!bg-emerald-200" />
            @endif
            @if ($this->usesManualMaterialDelivery())
                <x-src.toolbar.button href="#" :label="__('Entrega manual')" icon="box" :tooltip="__('Registrar saída física de material para este treinamento')"
                    x-on:click.prevent="$dispatch('open-training-material-delivery-modal', { trainingId: {{ $training->id }} })"
                    class="!bg-amber-100 !text-amber-900 !border-amber-300 hover:!bg-amber-200" />
            @endif
        </div>

        <div class="flex items-center gap-2 min-w-72 w-full max-w-md">
            <label for="registrations-search" class="sr-only">
                {{ __('Buscar inscrito, igreja/região ou e-mail') }}
            </label>
            <input id="registrations-search" type="text"
                placeholder="{{ __('Buscar inscrito, igreja/região ou e-mail...') }}"
                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-sky-500 focus:ring-2 focus:ring-sky-200"
                autofocus wire:model.live.debounce.350ms="search">
        </div>
    </x-src.toolbar.nav>

    @if ($this->usesManualMaterialDelivery())
        <div class="mt-4 rounded-2xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-900">
            {{ __('Pagamento continua sendo controle financeiro. A entrega física de kit/material agora deve ser registrada pelo fluxo de estoque para gerar baixa real e histórico auditável.') }}
        </div>
    @endif

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
        @if ($this->trainingCourseIsAccreditable())
            <article
                class="rounded-2xl border border-sky-950/45 bg-linear-to-br from-slate-100 via-white to-slate-200 px-4 py-3 flex-auto min-w-fit">
                <div class="text-xs text-slate-500">{{ __('Credenciados') }}</div>
                <div class="text-3xl font-bold text-slate-900">{{ $totalAccredited }}</div>
            </article>
        @endif
    </section>

    <section class="mt-4 grid gap-5">
        @if ($pendingChurchTempsCount > 0)
            <article
                class="rounded-2xl border border-amber-300 bg-amber-50 px-4 py-3 text-amber-900 flex flex-wrap items-center justify-between gap-3">
                <div class="text-sm font-semibold">
                    {{ $pendingChurchTempsCount }}
                    {{ __('igreja(s) pendente(s) de validação para este treinamento.') }}
                </div>
                <x-src.btn-gold type="button" wire:click="$dispatch('open-church-temp-review-modal')"
                    class="px-4 py-2">
                    {{ __('Validate Churches') }}
                </x-src.btn-gold>
            </article>
        @endif

        @forelse ($churchGroups as $churchGroup)
            <article x-data="{ open: true }"
                class="rounded-2xl border bg-white shadow-sm overflow-hidden {{ $churchGroup['has_church_issue'] ? 'border-red-400' : 'border-slate-300' }}"
                wire:key="church-group-{{ $churchGroup['key'] }}">
                <header
                    class="flex flex-wrap items-center justify-between gap-3 border-b bg-linear-to-r px-4 py-3 {{ $churchGroup['has_church_issue'] ? 'border-red-200 from-red-50 to-white' : 'border-slate-200 from-slate-100 to-white' }}">
                    <div class="min-w-0">
                        <h2
                            class="truncate text-base font-semibold {{ $churchGroup['has_church_issue'] ? 'text-red-900' : 'text-slate-900' }}">
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
                                    <th class="px-3 py-2 w-40 text-center">
                                        {{ $this->usesManualMaterialDelivery() ? __('Entrega física') : __('Kit') }}
                                    </th>
                                    @if ($this->trainingCourseIsAccreditable())
                                        <th class="px-3 py-2 w-32 text-center">{{ __('Credenciado') }}</th>
                                    @endif
                                    <th class="px-3 py-2 w-20 text-center">{{ __('Ação') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                @foreach ($churchGroup['registrations'] as $registration)
                                    <tr class="{{ $registration['has_church_issue'] ? 'odd:bg-red-50/40 even:bg-red-50/60 hover:bg-red-100/50' : 'odd:bg-white even:bg-slate-50/50 hover:bg-sky-50/40' }}"
                                        wire:key="registration-{{ $registration['id'] }}">
                                        <td
                                            class="px-3 py-2 {{ $registration['has_church_issue'] ? 'border-l-4 border-red-500' : '' }}">
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
                                            @if ($this->canToggleRegistrationKit())
                                                <div class="grid justify-items-center">
                                                    <x-app.switch-schedule :label="__('Kit')" :key="'kit-' . $registration['id']"
                                                        :checked="$registration['kit']"
                                                        wire:change="toggleKit({{ $registration['id'] }}, $event.target.checked)"
                                                        wire:loading.attr="disabled" wire:target="toggleKit" />
                                                </div>
                                            @else
                                                <div class="grid justify-items-center gap-2">
                                                    <span
                                                        class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $registration['kit'] ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                                                        {{ $registration['kit'] ? __('Kit entregue') : __('Sem kit entregue') }}
                                                    </span>

                                                    <button type="button"
                                                        class="inline-flex items-center justify-center rounded-lg border border-amber-300 bg-amber-50 px-2 py-1 text-[11px] font-semibold text-amber-800 transition hover:bg-amber-100"
                                                        x-on:click.prevent="$dispatch('open-training-material-delivery-modal', { trainingId: {{ $training->id }}, participantId: {{ $registration['id'] }} })">
                                                        {{ __('Registrar entrega') }}
                                                    </button>
                                                </div>
                                            @endif
                                        </td>
                                        @if ($this->trainingCourseIsAccreditable())
                                            <td class="px-3 py-2 align-top">
                                                <div class="grid justify-items-center">
                                                    <x-app.switch-schedule :label="__('Cred.')" :key="'accredited-' . $registration['id']"
                                                        :checked="$registration['accredited']"
                                                        wire:change="toggleAccredited({{ $registration['id'] }}, $event.target.checked)"
                                                        wire:loading.attr="disabled" wire:target="toggleAccredited" />
                                                </div>
                                            </td>
                                        @endif
                                        <td class="px-3 py-2 text-center">
                                            <flux:button variant="danger" size="sm" icon="trash"
                                                icon:variant="outline"
                                                class="cursor-pointer"
                                                aria-label="{{ __('Remover inscrito') }}"
                                                title="{{ __('Remover inscrito') }}"
                                                x-on:click.prevent="if (window.confirm('{{ __('Deseja realmente remover este inscrito deste evento?') }}')) { $wire.removeRegistration({{ $registration['id'] }}) }"
                                                wire:loading.attr="disabled" wire:target="removeRegistration">
                                            </flux:button>
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
                    {{ __('Ainda não existem participantes vinculados a este evento. Quando houver inscrições, você podera atualizar comprovante, credenciamento e kit nesta tela.') }}
                </p>
            </article>
        @endforelse
    </section>

    <flux:modal name="training-payment-receipt-modal" wire:model="showReceiptModal"
        class="max-w-4xl w-full bg-sky-950! p-0!">
        <div class="flex max-h-[90vh] flex-col overflow-hidden rounded-2xl">
            <header class="sticky top-0 z-20 border-b border-sky-800 bg-sky-950 px-6 py-4 text-sky-50">
                <h3 class="text-lg font-semibold">{{ __('Comprovante de pagamento') }}</h3>
                <div class="mt-3 rounded-xl border border-sky-700 bg-sky-900/80 px-4 py-3">
                    <div class="text-[11px] font-semibold uppercase tracking-wide text-sky-200/90">
                        {{ __('Aluno selecionado') }}
                    </div>
                    <p class="mt-1 text-xl font-bold leading-tight text-white">
                        {{ $selectedRegistrationName }}
                    </p>
                </div>
            </header>

            <div class="min-h-0 flex-1 overflow-y-auto bg-white px-6 py-6">
                <div class="space-y-4">
                    @php
                        $selectedReceiptName = null;
                        $selectedReceiptIsImage = false;
                        $selectedReceiptIsPdf = false;

                        if ($paymentReceiptUpload) {
                            $selectedReceiptExtension = strtolower(
                                (string) $paymentReceiptUpload->getClientOriginalExtension(),
                            );
                            $selectedReceiptName = (string) $paymentReceiptUpload->getClientOriginalName();
                            $selectedReceiptIsImage = in_array(
                                $selectedReceiptExtension,
                                ['webp', 'jpeg', 'png'],
                                true,
                            );
                            $selectedReceiptIsPdf = $selectedReceiptExtension === 'pdf';
                        }
                    @endphp

                    <div class="grid gap-4 lg:grid-cols-[minmax(0,1.6fr)_minmax(320px,1fr)]">
                        <section
                            class="rounded-2xl border border-neutral-200 bg-neutral-50 p-5 text-sm text-neutral-700">
                            <form wire:submit="uploadSelectedPaymentReceipt" class="flex flex-col gap-4">
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div class="text-xs font-semibold uppercase text-neutral-500">
                                        {{ __('Passo 1: comprovante do pagamento') }}
                                    </div>

                                    @if ($selectedPaymentReceiptUrl)
                                        <a href="{{ $selectedPaymentReceiptUrl }}" target="_blank"
                                            rel="noopener noreferrer"
                                            class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-100">
                                            {{ __('Abrir em nova aba') }}
                                        </a>
                                    @endif
                                </div>

                                <input id="training-payment-receipt-upload" type="file"
                                    wire:model="paymentReceiptUpload" accept=".webp,.jpeg,.png,.pdf"
                                    class="hidden" />

                                {{-- <div class="text-[11px] text-neutral-500">
                                    {{ __('Formatos aceitos: webp, PNG, WEBP ou PDF (até 5MB).') }}
                                </div> --}}

                                @error('paymentReceiptUpload')
                                    <div class="text-xs text-red-600">{{ $message }}</div>
                                @enderror

                                @if ($paymentReceiptUpload)
                                    <label for="training-payment-receipt-upload"
                                        class="cursor-pointer rounded-xl border border-neutral-200 bg-white p-3">
                                        <div class="mb-2 text-xs font-semibold uppercase text-neutral-500">
                                            {{ __('Arquivo selecionado') }}
                                        </div>

                                        @if ($selectedReceiptIsImage)
                                            <img src="{{ $paymentReceiptUpload->temporaryUrl() }}"
                                                alt="{{ __('Pré-visualização do comprovante') }}"
                                                class="h-auto max-h-80 w-full rounded-lg border border-neutral-200 object-contain" />
                                        @elseif ($selectedReceiptIsPdf)
                                            <div
                                                class="flex items-center gap-3 rounded-lg border border-slate-200 bg-slate-50 p-4">
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
                                @elseif ($selectedPaymentReceiptUrl)
                                    <label for="training-payment-receipt-upload"
                                        class="cursor-pointer rounded-xl border border-emerald-200 bg-white p-3">
                                        <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                                            <div class="text-xs font-semibold uppercase text-emerald-700">
                                                {{ __('Comprovante anexado') }}
                                            </div>
                                            <span
                                                class="inline-flex rounded-full bg-emerald-100 px-2.5 py-1 text-[11px] font-semibold text-emerald-800">
                                                {{ __('Toque para substituir') }}
                                            </span>
                                        </div>

                                        @if ($selectedPaymentReceiptIsImage)
                                            <img src="{{ $selectedPaymentReceiptUrl }}"
                                                alt="{{ __('Comprovante de pagamento') }}"
                                                class="h-auto max-h-80 w-full rounded-lg border border-neutral-200 object-contain" />
                                        @elseif ($selectedPaymentReceiptIsPdf)
                                            <div
                                                class="flex flex-wrap items-center gap-3 rounded-lg border border-slate-200 bg-slate-50 p-4">
                                                <span
                                                    class="inline-flex rounded-md border border-slate-300 bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-700">
                                                    PDF
                                                </span>
                                                <span class="text-xs text-slate-600">
                                                    {{ __('O comprovante foi salvo e esta pronto para validacao.') }}
                                                </span>
                                            </div>
                                        @endif
                                    </label>
                                @elseif ($selectedHasPaymentReceipt)
                                    <div
                                        class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                                        {{ __('Comprovante registrado, mas indisponível no momento.') }}
                                    </div>
                                @else
                                    <label for="training-payment-receipt-upload"
                                        class="group relative block cursor-pointer overflow-hidden rounded-xl border border-neutral-200 bg-white">
                                        <img src="{{ asset('images/paymentPIX.webp') }}"
                                            alt="{{ __('Clique aqui para enviar o comprovante de pagamento') }}"
                                            class="h-auto max-h-72 w-full object-contain transition duration-300 group-hover:scale-[1.01]" />
                                        <div
                                            class="absolute inset-0 flex items-end justify-center bg-gradient-to-t from-black/55 via-black/25 to-transparent p-4 text-center text-sm font-semibold text-white">
                                            {{ __('Clique aqui para enviar o comprovante de pagamento') }}
                                        </div>
                                    </label>
                                @endif

                                <div class="flex flex-wrap items-center gap-3">
                                    <flux:button variant="primary" type="submit" wire:loading.attr="disabled"
                                        wire:target="uploadSelectedPaymentReceipt,paymentReceiptUpload"
                                        :disabled="!$paymentReceiptUpload">
                                        {{ __('Salvar comprovante') }}
                                    </flux:button>

                                    @if ($paymentReceiptUpload)
                                        <x-src.btn-silver label="Remover arquivo selecionado"
                                            wire:click="clearSelectedPaymentReceiptUpload" class="py-1.5! text-xs" />
                                    @elseif ($selectedPaymentReceiptUrl)
                                        <label for="training-payment-receipt-upload"
                                            class="inline-flex cursor-pointer items-center justify-center rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-100">
                                            {{ __('Trocar comprovante') }}
                                        </label>
                                    @endif
                                </div>
                            </form>
                        </section>

                        <aside class="space-y-4">
                            @if ($showPaymentReadyHint)
                                <div
                                    class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900">
                                    <div class="text-xs font-semibold uppercase text-emerald-700">
                                        {{ __('Comprovante salvo') }}
                                    </div>
                                    <p class="mt-1 font-medium">
                                        {{ __('Agora voce ja pode marcar este aluno como pago.') }}
                                    </p>
                                </div>
                            @elseif (!$selectedHasPaymentReceipt)
                                <div
                                    class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                                    <div class="text-xs font-semibold uppercase text-amber-700">
                                        {{ __('Aguardando comprovante') }}
                                    </div>
                                    <p class="mt-1">
                                        {{ __('Este aluno ainda não enviou comprovante válido.') }}
                                    </p>
                                </div>
                            @endif

                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <div class="text-xs font-semibold uppercase text-slate-500">
                                    {{ __('Passo 2: confirmar pagamento') }}
                                </div>
                                <div class="mt-2 text-sm text-slate-700">
                                    {{ __('Depois de revisar o comprovante, marque o status abaixo para concluir a validacao financeira.') }}
                                </div>

                                <div
                                    class="mt-4 flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3">
                                    <div>
                                        <div class="text-sm font-semibold text-slate-900">
                                            {{ __('Pagamento confirmado') }}</div>
                                        <div class="text-xs text-slate-500">
                                            {{ __('Ative somente apos validar o comprovante.') }}
                                        </div>
                                    </div>

                                    @if ($selectedRegistrationId)
                                        <x-app.switch-schedule :label="__('Pago')" :key="'payment-modal-' . $selectedRegistrationId" :checked="$selectedPaymentConfirmed"
                                            wire:change="togglePayment({{ $selectedRegistrationId }}, $event.target.checked)"
                                            wire:loading.attr="disabled" wire:target="togglePayment" />
                                    @endif
                                </div>

                                @error('paymentConfirmation')
                                    <div class="mt-3 text-xs font-semibold text-red-600">{{ $message }}</div>
                                @enderror
                            </div>
                        </aside>
                    </div>
                </div>
            </div>

            <footer class="sticky bottom-0 z-20 border-t border-sky-800 bg-sky-950 px-6 py-4 text-sky-50">
                <div class="flex justify-end gap-3">
                    <x-src.btn-silver type="button" wire:click="closeReceiptModal" wire:loading.attr="disabled"
                        wire:target="uploadSelectedPaymentReceipt,paymentReceiptUpload,togglePayment">
                        {{ __('Fechar') }}
                    </x-src.btn-silver>
                </div>
            </footer>
        </div>
    </flux:modal>

    @livewire($this->contextComponent('churchTempReviewModal'), ['training' => $training], key('church-temp-review-modal-' . $training->id . '-' . $this->contextRoute('show')))
    @livewire($this->contextComponent('approveChurchTempModal'), ['training' => $training], key('approve-church-temp-modal-' . $training->id . '-' . $this->contextRoute('show')))
    @if ($this->contextComponent('deliverMaterialModal'))
        @livewire($this->contextComponent('deliverMaterialModal'), ['trainingId' => $training->id], key('deliver-material-modal-' . $training->id))
    @endif
    @if ($this->contextComponent('createParticipantRegistrationModal'))
        @livewire($this->contextComponent('createParticipantRegistrationModal'), ['trainingId' => $training->id], key('create-participant-registration-modal-' . $training->id))
    @endif
</div>
