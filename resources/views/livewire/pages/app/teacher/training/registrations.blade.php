<div x-data wire:loading.class="pointer-events-none opacity-80"
    wire:target="togglePaymentReceipt,toggleAccredited,toggleKit,removeRegistration">
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
                                    <th class="px-3 py-2 w-32 text-center">{{ __('Comprovante') }}</th>
                                    <th class="px-3 py-2 w-28 text-center">{{ __('Kit') }}</th>
                                    <th class="px-3 py-2 w-32 text-center">{{ __('Credenciado') }}</th>
                                    <th class="px-3 py-2 w-20 text-center">{{ __('Acao') }}</th>
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
                                            <div class="font-semibold text-slate-900">
                                                {{ $registration['name'] }}
                                            </div>
                                        </td>
                                        <td class="px-3 py-2">
                                            <div class="grid gap-0.5 text-xs text-slate-600">
                                                <span>{{ $registration['email'] ?: __('Sem email') }}</span>
                                                <span>{{ $registration['phone'] ?: __('Sem telefone') }}</span>
                                            </div>
                                        </td>
                                        <td class="px-3 py-2 align-top">
                                            <div class="grid justify-items-center gap-1">
                                                <x-app.switch-schedule :label="__('Comp.')" :key="'receipt-' . $registration['id']"
                                                    :checked="$registration['has_payment_receipt']"
                                                    wire:change="togglePaymentReceipt({{ $registration['id'] }}, $event.target.checked)"
                                                    wire:loading.attr="disabled" wire:target="togglePaymentReceipt" />
                                                @if ($registration['payment_receipt_url'])
                                                    <a href="{{ $registration['payment_receipt_url'] }}"
                                                        class="text-[10px] font-semibold text-sky-700 underline"
                                                        target="_blank">{{ __('Abrir') }}</a>
                                                @endif
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
                    {{ __('Ainda nao existem participantes vinculados a este evento. Quando houver inscricoes, voce podera atualizar comprovante, credenciamento e kit nesta tela.') }}
                </p>
            </article>
        @endforelse
    </section>
</div>
