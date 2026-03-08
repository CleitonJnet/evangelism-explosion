<div class="space-y-6">
    <section
        class="rounded-2xl border border-amber-300/20 bg-linear-to-br from-slate-100 via-white to-slate-200 p-4 shadow-lg sm:p-6">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-4 border-b-2 border-slate-200/80 pb-4">
            <div class="space-y-1">
                <h2 class="text-xl font-semibold text-slate-900" style="font-family: 'Cinzel', serif;">
                    {{ __('Estoques cadastrados') }}
                </h2>
                <p class="text-sm text-slate-600">
                    {{ __('Gerencie o estoque central e os estoques locais vinculados aos professores.') }}
                </p>
            </div>

        </div>

        <div class="flex flex-wrap gap-x-4 gap-y-8">
            <x-src.form.input name="director-inventory-search" wire:model.live.debounce.300ms="search"
                label="Buscar estoque" type="text" width_basic="280" />
            <x-src.form.select name="director-inventory-kind-filter" wire:model.live="kindFilter" label="Tipo"
                width_basic="180" :options="$kindOptions" />
            <x-src.form.select name="director-inventory-status-filter" wire:model.live="statusFilter" label="Status"
                width_basic="180" :options="$statusOptions" />
        </div>

        <div class="mt-6 overflow-x-auto rounded-2xl border border-slate-200 bg-white/95 shadow-sm">
            <table class="w-full min-w-5xl text-left text-sm">
                <thead class="bg-slate-100 text-xs uppercase tracking-wide text-slate-600">
                    <tr>
                        <th class="px-4 py-3">{{ __('Nome') }}</th>
                        <th class="px-4 py-3">{{ __('Tipo') }}</th>
                        <th class="px-4 py-3">{{ __('Professor responsável') }}</th>
                        <th class="px-4 py-3">{{ __('Cidade / Estado') }}</th>
                        <th class="px-4 py-3">{{ __('Status') }}</th>
                        <th class="px-4 py-3">{{ __('SKUs com saldo') }}</th>
                        <th class="px-4 py-3 text-right">{{ __('Ações') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($inventories as $inventory)
                        <tr wire:key="director-inventory-row-{{ $inventory->id }}"
                            class="border-t border-slate-200 odd:bg-white even:bg-slate-50">
                            <td class="px-4 py-4">
                                <div class="font-semibold text-slate-900">{{ $inventory->name }}</div>
                                <div class="text-xs text-slate-500">
                                    {{ $inventory->email ?: __('Sem email') }} · {{ $inventory->phone ?: __('Sem telefone') }}
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <span
                                    class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $inventory->kind === 'central' ? 'bg-amber-100 text-amber-800' : 'bg-sky-100 text-sky-800' }}">
                                    {{ $inventory->kind === 'central' ? __('Central') : __('Professor') }}
                                </span>
                            </td>
                            <td class="px-4 py-4 text-slate-700">
                                {{ $inventory->responsibleUser?->name ?: __('Não se aplica') }}
                            </td>
                            <td class="px-4 py-4 text-slate-700">
                                {{ trim(implode(' / ', array_filter([$inventory->city, $inventory->state]))) ?: __('Não informado') }}
                            </td>
                            <td class="px-4 py-4">
                                <span
                                    class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $inventory->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-700' }}">
                                    {{ $inventory->is_active ? __('Ativo') : __('Inativo') }}
                                </span>
                            </td>
                            <td class="px-4 py-4 text-slate-700">{{ (int) $inventory->active_skus_count }}</td>
                            <td class="px-4 py-4">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('app.director.inventory.show', $inventory) }}"
                                        class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 transition hover:border-sky-300 hover:text-sky-900">
                                        {{ __('Detalhes') }}
                                    </a>
                                    <button type="button"
                                        onclick="window.Livewire.dispatch('open-director-inventory-edit-modal', { inventoryId: {{ $inventory->id }} }); return false;"
                                        class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 transition hover:border-amber-300 hover:text-amber-900">
                                        {{ __('Editar') }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-sm text-slate-500">
                                <div class="mx-auto max-w-md space-y-2">
                                    <div class="text-base font-semibold text-slate-700">
                                        {{ __('Nenhum estoque encontrado') }}
                                    </div>
                                    <div>
                                        {{ __('Ajuste os filtros ou cadastre um novo estoque para iniciar o controle operacional.') }}
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($inventories->hasPages())
            <div class="mt-5">
                {{ $inventories->links(data: ['scrollTo' => false]) }}
            </div>
        @endif
    </section>

    <livewire:pages.app.director.inventory.create-inventory-modal wire:key="director-inventory-create-modal" />
    @foreach ($inventories as $inventory)
        <livewire:pages.app.director.inventory.edit-inventory-modal :inventory-id="$inventory->id"
            wire:key="director-inventory-index-edit-modal-{{ $inventory->id }}" />
    @endforeach
</div>
