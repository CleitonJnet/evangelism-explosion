<div class="space-y-6">
    <section
        class="rounded-2xl border border-amber-300/20 bg-linear-to-br from-slate-100 via-white to-slate-200 p-4 shadow-lg sm:p-6">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-4 border-b-2 border-slate-200/80 pb-4">
            <div class="space-y-1">
                <h2 class="text-xl font-semibold text-slate-900" style="font-family: 'Cinzel', serif;">
                    {{ __('Estoques cadastrados') }}
                </h2>
                <p class="text-sm text-slate-600">
                    {{ __('Gerencie o estoque central e os estoques locais vinculados a professores e bases.') }}
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
                    $presentation = match ($inventory->kind) {
                        'central' => [
                            'badge' => __('Central'),
                            'badge_classes' => 'bg-amber-100 text-amber-800',
                            'card_classes' => 'border-2 border-amber-300 bg-linear-to-br from-amber-50 via-white to-amber-100/70 hover:border-amber-400 hover:bg-amber-50/80',
                            'responsible_label' => __('Responsável'),
                            'responsible_name' => __('Não se aplica'),
                            'scope_label' => __('Distribuição central'),
                        ],
                        'base' => [
                            'badge' => __('Base'),
                            'badge_classes' => 'bg-sky-100 text-sky-800',
                            'card_classes' => 'border-2 border-sky-300 bg-linear-to-br from-sky-50 via-white to-cyan-100/70 hover:border-sky-400 hover:bg-sky-50/80',
                            'responsible_label' => __('Base vinculada'),
                            'responsible_name' => $inventory->church?->name ?: __('Base não informada'),
                            'scope_label' => __('Operação institucional da base'),
                        ],
                        default => [
                            'badge' => __('Professor'),
                            'badge_classes' => 'bg-indigo-100 text-indigo-800',
                            'card_classes' => 'border-2 border-indigo-300 bg-linear-to-br from-indigo-50 via-white to-sky-100/70 hover:border-indigo-400 hover:bg-indigo-50/80',
                            'responsible_label' => __('Professor responsável'),
                            'responsible_name' => $inventory->responsibleUser?->name ?: __('Não informado'),
                            'scope_label' => __('Operação do professor'),
                        ],
                    };
                    $location = trim(implode(' / ', array_filter([$inventory->city, $inventory->state]))) ?: __('Não informado');
                @endphp

                <a href="{{ route('app.director.inventory.show', $inventory) }}"
                    wire:key="director-inventory-card-{{ $inventory->id }}"
                    class="flex flex-col gap-4 rounded-2xl border p-4 text-left shadow-xs transition {{ $presentation['card_classes'] }}">
                    <div class="flex items-start justify-between gap-3">
                        <div class="space-y-2">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $presentation['badge_classes'] }}">
                                    {{ $presentation['badge'] }}
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
                                {{ $presentation['responsible_label'] }}
                            </div>
                            <div class="font-medium text-slate-800">{{ $presentation['responsible_name'] }}</div>
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
                        <span class="rounded-full bg-white/80 px-2.5 py-1 text-slate-800">
                            {{ $presentation['scope_label'] }}
                        </span>
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
