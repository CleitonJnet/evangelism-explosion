<div class="space-y-6">
    <section
        class="rounded-2xl border border-amber-300/20 bg-linear-to-br from-slate-100 via-white to-slate-200 p-4 shadow-lg sm:p-6">
        <div class="flex flex-wrap items-start justify-between gap-4 border-b-2 border-slate-200/80 pb-4">
            @php
                $formattedAddress = trim(
                    implode(
                        ', ',
                        array_filter([
                            $inventory->street,
                            $inventory->number,
                            $inventory->complement,
                            $inventory->district,
                            $inventory->city,
                            $inventory->state,
                            $inventory->postal_code,
                        ]),
                    ),
                );

                $headerMeta = array_values(array_filter([$inventory->responsibleUser?->name]));

                $hasContact = filled($inventory->phone) || filled($inventory->email);
            @endphp

            <div class="space-y-2">
                <h2 class="text-2xl font-semibold text-slate-900" style="font-family: 'Cinzel', serif;">
                    {{ $inventory->name }}
                </h2>
                @if ($headerMeta !== [])
                    <p class="text-sm font-semibold text-slate-700">
                        {{ implode(' · ', $headerMeta) }}
                    </p>
                @endif
                @if ($formattedAddress !== '')
                    <p class="max-w-4xl text-sm text-slate-600">
                        {{ $formattedAddress }}
                    </p>
                @endif
            </div>

            @if ($hasContact)
                <div class="max-w-sm text-right">
                    <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                        {{ __('Contato') }}
                    </div>
                    <div class="mt-2 space-y-1 text-sm font-semibold text-slate-900">
                        @if ($inventory->phone)
                            <div>{{ $inventory->phone }}</div>
                        @endif
                        @if ($inventory->email)
                            <div>{{ $inventory->email }}</div>
                        @endif
                    </div>
                </div>
            @endif

        </div>

        <div class="mt-4 flex flex-wrap items-center gap-2">
            <span
                class="rounded-full px-3 py-1 text-xs font-semibold {{ $inventory->kind === 'central' ? 'bg-amber-100 text-amber-800' : 'bg-sky-100 text-sky-800' }}">
                {{ $inventory->kind === 'central' ? __('Estoque central') : __('Estoque local de professor') }}
            </span>
            <span
                class="rounded-full px-3 py-1 text-xs font-semibold {{ $inventory->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-700' }}">
                {{ $inventory->is_active ? __('Ativo') : __('Inativo') }}
            </span>
        </div>

    </section>

    @if ($lowStockItems->isNotEmpty())
        <section class="rounded-2xl border border-amber-200 bg-amber-50 p-5 shadow-sm">
            <div class="border-b border-amber-200 pb-3">
                <h3 class="text-lg font-semibold text-amber-900">{{ __('Alertas de estoque mínimo') }}</h3>
                <p class="text-sm text-amber-800">{{ __('Materiais com saldo abaixo do mínimo configurado.') }}</p>
            </div>

            <div class="mt-4 flex flex-wrap gap-3">
                @foreach ($lowStockItems as $alertItem)
                    <div class="rounded-xl border border-amber-200 bg-white px-4 py-3 text-sm text-amber-900">
                        <span class="font-semibold">{{ $alertItem->name }}</span>
                        <span class="block text-xs opacity-80">
                            {{ __('Saldo: :current / Mínimo: :minimum', ['current' => $alertItem->current_quantity, 'minimum' => $alertItem->minimum_stock]) }}
                        </span>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    <div class="space-y-6">
        <section class="rounded-2xl border border-slate-200 bg-white/95 p-5 shadow-sm">
            <div class="border-b border-slate-200 pb-3 mb-10">
                <h3 class="text-lg font-semibold text-slate-900">{{ __('Saldo atual por produto') }}</h3>
                <p class="text-sm text-slate-600">
                    {{ __('Use a busca abaixo para localizar rapidamente produtos compostos e itens simples neste estoque.') }}
                </p>
            </div>

            <div class="mt-5 flex flex-wrap gap-x-4 gap-y-8">
                <x-src.form.input name="director-inventory-material-search"
                    wire:model.live.debounce.300ms="materialSearch" label="Buscar produto" type="text"
                    width_basic="320" autofocus />
            </div>

            <div class="mt-6 space-y-8">
                <section>
                    <div class="pb-3">
                        <h4 class="text-base font-semibold text-slate-900">{{ __('Produtos compostos') }}</h4>
                        <p class="text-sm text-slate-600">
                            {{ __('Aqui aparecem os kits e produtos compostos já montados, com seu saldo atual e a quantidade de componentes vinculados.') }}
                        </p>
                    </div>

                    <div class="mt-3 overflow-x-auto rounded-xl border border-slate-200">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-slate-50 text-xs uppercase text-slate-600">
                                <tr>
                                    <th class="px-4 py-3">{{ __('Produto composto') }}</th>
                                    <th class="px-4 py-3">{{ __('Componentes') }}</th>
                                    <th class="px-4 py-3">{{ __('Saldo') }}</th>
                                    <th class="px-4 py-3">{{ __('Mínimo') }}</th>
                                    <th class="px-4 py-3">{{ __('Alerta') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white">
                                @forelse ($compositeBalances as $balance)
                                    <tr class="cursor-pointer border-t border-slate-200 transition hover:bg-slate-50 {{ $balance->is_active ? 'text-slate-900' : 'text-slate-400' }}"
                                        onclick="window.Livewire.dispatch('open-director-material-edit-modal', { materialId: {{ $balance->id }}, tab: 'entry' }); return false;">
                                        <td class="px-4 py-3 font-medium">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <span>{{ $balance->name }}</span>
                                                @if (!$balance->is_active)
                                                    <span
                                                        class="rounded-full bg-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-700">
                                                        {{ __('Inativo') }}
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">{{ $balance->components_count }}</td>
                                        <td class="px-4 py-3 font-semibold">{{ $balance->current_quantity }}</td>
                                        <td class="px-4 py-3">{{ $balance->minimum_stock }}</td>
                                        <td class="px-4 py-3">
                                            @if ($balance->minimum_stock > 0 && $balance->current_quantity < $balance->minimum_stock)
                                                <span
                                                    class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-800">
                                                    {{ __('Abaixo do mínimo') }}
                                                </span>
                                            @else
                                                <span class="text-slate-400">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-6 text-center text-sm text-slate-500">
                                            <div class="mx-auto max-w-sm space-y-2">
                                                <div class="text-base font-semibold text-slate-700">
                                                    {{ __('Nenhum produto composto encontrado') }}
                                                </div>
                                                <div>
                                                    {{ __('Este estoque ainda não possui produtos compostos com saldo para a busca atual.') }}
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($compositeBalances->hasPages())
                        <div class="mt-4">
                            {{ $compositeBalances->links(data: ['scrollTo' => false]) }}
                        </div>
                    @endif
                </section>

                <section class="border-t border-slate-200 pt-6">
                    <div class="pb-3">
                        <h4 class="text-base font-semibold text-slate-900">{{ __('Itens simples') }}</h4>
                        <p class="text-sm text-slate-600">
                            {{ __('Esta tabela lista apenas os itens simples cadastrados no estoque, usados tanto individualmente quanto na composição de produtos compostos.') }}
                        </p>
                    </div>

                    <div class="mt-3 overflow-x-auto rounded-xl border border-slate-200">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-slate-50 text-xs uppercase text-slate-600">
                                <tr>
                                    <th class="px-4 py-3">{{ __('Item simples') }}</th>
                                    <th class="px-4 py-3">{{ __('Saldo') }}</th>
                                    <th class="px-4 py-3">{{ __('Mínimo') }}</th>
                                    <th class="px-4 py-3">{{ __('Alerta') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white">
                                @forelse ($simpleBalances as $balance)
                                    <tr @if ($balance->is_active) data-simple-material-row-active @endif
                                        class="cursor-pointer border-t border-slate-200 transition hover:bg-slate-50 {{ $balance->is_active ? 'text-slate-900' : 'text-slate-400' }}"
                                        onclick="window.Livewire.dispatch('open-director-material-edit-modal', { materialId: {{ $balance->id }}, tab: 'entry' }); return false;">
                                        <td class="px-4 py-3 font-medium">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <span>{{ $balance->name }}</span>
                                                @if (!$balance->is_active)
                                                    <span
                                                        class="rounded-full bg-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-700">
                                                        {{ __('Inativo') }}
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 font-semibold">{{ $balance->current_quantity }}</td>
                                        <td class="px-4 py-3">{{ $balance->minimum_stock }}</td>
                                        <td class="px-4 py-3">
                                            @if ($balance->minimum_stock > 0 && $balance->current_quantity < $balance->minimum_stock)
                                                <span
                                                    class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-800">
                                                    {{ __('Abaixo do mínimo') }}
                                                </span>
                                            @else
                                                <span class="text-slate-400">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-6 text-center text-sm text-slate-500">
                                            <div class="mx-auto max-w-sm space-y-2">
                                                <div class="text-base font-semibold text-slate-700">
                                                    {{ __('Nenhum item simples encontrado') }}
                                                </div>
                                                <div>
                                                    {{ __('Este estoque ainda não possui itens simples com saldo para a busca atual.') }}
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($simpleBalances->hasPages())
                        <div class="mt-4">
                            {{ $simpleBalances->links(data: ['scrollTo' => false]) }}
                        </div>
                    @endif
                </section>
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white/95 p-5 shadow-sm">
            <div class="border-b border-slate-200 pb-3">
                <h3 class="text-lg font-semibold text-slate-900">{{ __('Histórico auditável') }}</h3>
                <p class="text-sm text-slate-600">
                    {{ __('Todas as movimentações manuais e transferências registradas neste estoque.') }}
                </p>
            </div>

            <div class="mt-5 flex flex-wrap gap-x-4 gap-y-8">
                <x-src.form.select name="director-inventory-movement-type-filter" wire:model.live="movementTypeFilter"
                    label="Tipo de movimento" width_basic="240" :value="$movementTypeFilter" :options="$movementTypeOptions" />
            </div>

            <div class="mt-5 overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase text-slate-600">
                        <tr>
                            <th class="px-4 py-3">{{ __('Data / Hora') }}</th>
                            <th class="px-4 py-3">{{ __('Material') }}</th>
                            <th class="px-4 py-3">{{ __('Tipo') }}</th>
                            <th class="px-4 py-3">{{ __('Qtd.') }}</th>
                            <th class="px-4 py-3">{{ __('Saldo após') }}</th>
                            <th class="px-4 py-3">{{ __('Usuário') }}</th>
                            <th class="px-4 py-3">{{ __('Observação') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($movements as $movement)
                            <tr class="border-t border-slate-200">
                                <td class="px-4 py-3 text-slate-700">{{ $movement->created_at?->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-4 py-3 font-medium text-slate-900">
                                    {{ $movement->material?->name ?: __('Material removido') }}
                                </td>
                                <td class="px-4 py-3 text-slate-700">
                                    <span
                                        class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $movement->typeBadgeClasses() }}">
                                        {{ $movement->typeLabel() }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 font-semibold text-slate-700">{{ $movement->quantity }}</td>
                                <td class="px-4 py-3 text-slate-700">{{ $movement->balance_after ?? __('-') }}</td>
                                <td class="px-4 py-3 text-slate-700">{{ $movement->user?->name ?: __('Sistema') }}
                                </td>
                                <td class="px-4 py-3 text-slate-700">{{ $movement->notes ?: __('-') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-6 text-center text-sm text-slate-500">
                                    <div class="mx-auto max-w-sm space-y-2">
                                        <div class="text-base font-semibold text-slate-700">
                                            {{ __('Nenhuma movimentação encontrada') }}
                                        </div>
                                        <div>
                                            {{ __('Registre uma entrada, saída, ajuste, perda ou transferência para iniciar o histórico auditável deste estoque.') }}
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($movements->hasPages())
                <div class="mt-4">
                    {{ $movements->links(data: ['scrollTo' => false]) }}
                </div>
            @endif
        </section>
    </div>

    <livewire:pages.app.director.inventory.edit-inventory-modal :inventory-id="$inventory->id"
        wire:key="director-inventory-edit-modal-{{ $inventory->id }}" />
    <livewire:pages.app.director.inventory.stock-action-modal :inventory-id="$inventory->id"
        wire:key="director-inventory-stock-action-modal-{{ $inventory->id }}" />
    <livewire:pages.app.director.inventory.create-modal
        wire:key="director-material-create-modal-from-stock-{{ $inventory->id }}" />
    @foreach ($compositeBalances->getCollection() as $balance)
        <livewire:pages.app.director.inventory.edit-modal :material-id="$balance->id" :inventory-id="$inventory->id"
            wire:key="director-material-edit-modal-composite-{{ $balance->id }}" />
    @endforeach
    @foreach ($simpleBalances->getCollection() as $balance)
        <livewire:pages.app.director.inventory.edit-modal :material-id="$balance->id" :inventory-id="$inventory->id"
            wire:key="director-material-edit-modal-simple-{{ $balance->id }}" />
    @endforeach

    @if ($inventory->notes)
        <section class="rounded-2xl border border-slate-200 bg-white/95 p-5 shadow-sm">
            <div class="border-b border-slate-200 pb-3">
                <h3 class="text-lg font-semibold text-slate-900">{{ __('Observações do estoque') }}</h3>
                <p class="text-sm text-slate-600">
                    {{ __('Notas operacionais ou administrativas cadastradas para este local de estoque.') }}
                </p>
            </div>

            <div class="mt-4 text-sm leading-6 text-slate-700">
                {{ $inventory->notes }}
            </div>
        </section>
    @endif
</div>
