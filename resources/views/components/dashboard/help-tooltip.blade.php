@props([
    'title',
    'what',
    'how',
])

@php
    $modalName = 'dashboard-help-'.md5($title.'|'.$what.'|'.$how);
@endphp

<div {{ $attributes->class('inline-flex') }}>
    <div class="group/help relative inline-flex">
        <button type="button"
            class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-slate-300 bg-white text-sm font-bold text-slate-600 shadow-sm transition hover:border-sky-300 hover:text-sky-900 focus:border-sky-400 focus:text-sky-900 focus:outline-none"
            x-data="" x-on:click.prevent="$flux.modal('{{ $modalName }}').show()" aria-label="{{ $title }}"
            title="Ajuda">
            ?
        </button>

        <span
            class="pointer-events-none absolute top-full left-1/2 z-10 mt-2 -translate-x-1/2 rounded-full bg-slate-950 px-2.5 py-1 text-[11px] font-semibold tracking-[0.12em] text-white uppercase opacity-0 shadow-sm transition duration-150 group-hover/help:opacity-100">
            Ajuda
        </span>
    </div>

    <flux:modal name="{{ $modalName }}" class="max-w-2xl">
        <div class="space-y-6">
            <div class="space-y-2">
                <flux:heading size="lg">{{ $title }}</flux:heading>
                <flux:text class="text-sm text-(--ee-app-muted)">
                    {{ __('Abra esta ajuda sempre que quiser entender rapidamente como ler este bloco.') }}
                </flux:text>
            </div>

            <div class="grid gap-4">
                <section class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">O que informa</p>
                    <p class="mt-2 text-sm leading-6 text-slate-700">{{ $what }}</p>
                </section>

                <section class="rounded-2xl border border-sky-200 bg-sky-50/70 p-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-sky-800">Como aproveitar</p>
                    <p class="mt-2 text-sm leading-6 text-slate-700">{{ $how }}</p>
                </section>
            </div>

            <div class="flex justify-end">
                <flux:modal.close>
                    <flux:button variant="filled">Fechar</flux:button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>
</div>
