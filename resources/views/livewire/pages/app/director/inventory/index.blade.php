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
            <div class="rounded-full bg-amber-100 px-3 py-1 text-sm font-semibold text-amber-800">
                {{ __('Total listado: :count', ['count' => $inventories->total()]) }}
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

        <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @forelse ($inventories as $inventory)
                @php
                    $isCentralInventory = $inventory->kind === 'central';
                    $typeBadgeClasses = $isCentralInventory
                        ? 'bg-amber-100 text-amber-800'
                        : 'bg-sky-100 text-sky-800';
                    $cardClasses = $isCentralInventory
                        ? 'border-2 border-amber-300 bg-linear-to-br from-amber-50 via-white to-amber-100/70 hover:border-amber-400 hover:bg-amber-50/80'
                        : 'border-2 border-sky-300 bg-linear-to-br from-sky-50 via-white to-cyan-100/70 hover:border-sky-400 hover:bg-sky-50/80';
                    $location = trim(implode(' / ', array_filter([$inventory->city, $inventory->state]))) ?: __('Não informado');
                    $responsibleName = $inventory->responsibleUser?->name ?: __('Não se aplica');
                @endphp

                <a href="{{ route('app.director.inventory.show', $inventory) }}"
                    wire:key="director-inventory-card-{{ $inventory->id }}"
                    class="flex flex-col gap-4 rounded-2xl border p-4 text-left shadow-xs transition {{ $cardClasses }}">
                    <div class="flex items-start justify-between gap-3">
                        <div class="space-y-2">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $typeBadgeClasses }}">
                                    {{ $isCentralInventory ? __('Central') : __('Professor') }}
                                </span>
                                <span
                                    class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $inventory->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-700' }}">
                                    {{ $inventory->is_active ? __('Ativo') : __('Inativo') }}
                                </span>
                            </div>

                            <div>
                                <div class="text-lg font-bold tracking-tight text-slate-950 sm:text-xl">{{ $inventory->name }}</div>
                                <div class="text-xs text-slate-500">
                                    {{ $inventory->email ?: __('Sem email') }} · {{ $inventory->phone ?: __('Sem telefone') }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-3 text-sm sm:grid-cols-2">
                        <div class="rounded-xl border border-slate-200/80 bg-white/80 p-3">
                            <div class="mb-1 text-xs font-semibold uppercase tracking-wide text-slate-500">
                                {{ __('Professor responsável') }}
                            </div>
                            <div class="font-medium text-slate-800">{{ $responsibleName }}</div>
                        </div>

                        <div class="rounded-xl border border-slate-200/80 bg-white/80 p-3">
                            <div class="mb-1 text-xs font-semibold uppercase tracking-wide text-slate-500">
                                {{ __('Cidade / Estado') }}
                            </div>
                            <div class="font-medium text-slate-800">{{ $location }}</div>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2 text-xs font-semibold">
                        <span class="rounded-full bg-slate-200 px-2.5 py-1 text-slate-700">
                            {{ __('SKUs com saldo: :count', ['count' => (int) $inventory->active_skus_count]) }}
                        </span>
                        @if ($isCentralInventory)
                            <span class="rounded-full bg-amber-200/70 px-2.5 py-1 text-amber-900">
                                {{ __('Distribuição central') }}
                            </span>
                        @else
                            <span class="rounded-full bg-sky-200/70 px-2.5 py-1 text-sky-900">
                                {{ __('Operação do professor') }}
                            </span>
                        @endif
                    </div>
                </a>
            @empty
                <div class="rounded-2xl border border-amber-200/60 bg-white px-4 py-6 text-sm text-slate-600 sm:col-span-2 xl:col-span-3">
                    <div class="mx-auto max-w-md space-y-2 text-center">
                        <div class="text-base font-semibold text-slate-700">
                            {{ __('Nenhum estoque encontrado') }}
                        </div>
                        <div>
                            {{ __('Ajuste os filtros ou cadastre um novo estoque para iniciar o controle operacional.') }}
                        </div>
                    </div>
                </div>
            @endforelse
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

    <flux:modal name="director-inventory-delete-modal" wire:model="showDeleteModal" class="max-w-md">
        <div class="space-y-4">
            <div>
                <flux:heading size="lg">{{ __('Excluir estoque') }}</flux:heading>
                <flux:text class="mt-2 text-sm text-slate-600">
                    @if ($selectedInventoryDeletionBlockedReason !== '')
                        {{ $selectedInventoryDeletionBlockedReason }}
                    @else
                        {{ __('Tem certeza que deseja excluir este estoque? Esta ação não pode ser desfeita.') }}
                    @endif
                </flux:text>
                <flux:text class="mt-1 text-sm font-semibold text-slate-900">
                    {{ $selectedInventoryName }}
                </flux:text>
            </div>

            <div class="flex justify-end gap-2">
                <flux:button type="button" variant="ghost" wire:click="closeDeleteModal">
                    {{ $selectedInventoryDeletionBlockedReason !== '' ? __('Fechar') : __('Cancelar') }}
                </flux:button>

                @if ($selectedInventoryDeletionBlockedReason === '')
                    <flux:button type="button" variant="danger" wire:click="deleteSelectedInventory"
                        wire:loading.attr="disabled" wire:target="deleteSelectedInventory">
                        {{ __('Confirmar exclusão') }}
                    </flux:button>
                @endif
            </div>
        </div>
    </flux:modal>
</div>
