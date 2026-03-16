<x-layouts.app :title="__('Estoque central')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <x-app.portal.page-header
            eyebrow="Portal Staff"
            title="Estoque central"
            description="Leitura institucional do acervo nacional dentro do portal Staff, reaproveitando o modulo central atual sem recriar a operacao."
            :breadcrumbs="[
                ['label' => 'Portais', 'url' => route('app.start')],
                ['label' => 'Staff / Governanca', 'url' => route('app.portal.staff.dashboard')],
                ['label' => 'Estoque central', 'current' => true],
            ]">
            @if ($inventory['legacy_index_route'])
                <flux:button variant="primary" :href="$inventory['legacy_index_route']" wire:navigate>
                    {{ __('Abrir modulo central atual') }}
                </flux:button>
            @endif
        </x-app.portal.page-header>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
            <x-app.portal.stat-card label="Estoques centrais" :value="$inventory['summary']['inventories_count']" hint="Estruturas centrais atualmente vinculadas ao acervo nacional." tone="sky" />
            <x-app.portal.stat-card label="Estoques ativos" :value="$inventory['summary']['active_inventory_count']" hint="Unidades com operacao ativa neste momento." tone="emerald" />
            <x-app.portal.stat-card label="Itens ativos" :value="$inventory['summary']['materials_count']" hint="Materiais com saldo ou historico consolidado no estoque central." />
            <x-app.portal.stat-card label="Alertas" :value="$inventory['summary']['low_stock_count']" hint="Itens abaixo do minimo configurado." tone="amber" />
            <x-app.portal.stat-card label="Movimentos recentes" :value="$inventory['summary']['movements_count']" hint="Ultimas movimentacoes registradas no acervo central." />
        </section>

        <section class="grid gap-4 xl:grid-cols-[minmax(0,1.15fr)_minmax(20rem,0.85fr)]">
            <div class="rounded-3xl border border-neutral-200 bg-white p-6 shadow-sm">
                <div class="mb-4">
                    <h2 class="text-lg font-semibold text-neutral-950">{{ __('Acervo central dentro do Staff') }}</h2>
                    <p class="text-sm text-neutral-600">{{ __('Esta area concentra a leitura institucional do estoque central e preserva o modulo operacional ja existente para movimentacoes detalhadas.') }}</p>
                </div>

                <div class="grid gap-3">
                    @forelse ($inventory['inventories'] as $centralInventory)
                        <div class="rounded-2xl border border-neutral-200 bg-neutral-50 p-4">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <div class="text-sm font-semibold text-neutral-950">{{ $centralInventory['name'] }}</div>
                                    <div class="text-sm text-neutral-600">{{ $centralInventory['responsible'] }} · {{ $centralInventory['location'] }}</div>
                                    @if ($centralInventory['church_name'])
                                        <div class="mt-1 text-xs text-neutral-500">{{ __('Vinculo institucional: :church', ['church' => $centralInventory['church_name']]) }}</div>
                                    @endif
                                </div>

                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="rounded-full bg-sky-100 px-2.5 py-1 text-xs font-semibold text-sky-800">{{ __('Central') }}</span>
                                    <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $centralInventory['status'] === 'Ativo' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-700' }}">{{ $centralInventory['status'] }}</span>
                                    <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-800">{{ trans_choice(':count alerta|:count alertas', $centralInventory['low_stock_count'], ['count' => $centralInventory['low_stock_count']]) }}</span>
                                </div>
                            </div>

                            <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                                <div class="text-xs font-semibold uppercase tracking-[0.18em] text-neutral-500">
                                    {{ __('Itens ativos: :count', ['count' => $centralInventory['materials_count']]) }}
                                </div>

                                @if ($centralInventory['legacy_route'])
                                    <a href="{{ $centralInventory['legacy_route'] }}" class="text-sm font-semibold text-sky-800" wire:navigate>
                                        {{ __('Abrir detalhe no modulo atual') }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-neutral-300 bg-neutral-50 p-5 text-sm text-neutral-600">
                            {{ __('Nenhum estoque central foi cadastrado ainda. Quando o acervo nacional for estruturado no modulo atual, esta area passa a refletir a leitura institucional automaticamente.') }}
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="rounded-3xl border border-neutral-200 bg-white p-6 shadow-sm">
                <div class="mb-4">
                    <h2 class="text-lg font-semibold text-neutral-950">{{ __('Guardrails desta sessao') }}</h2>
                    <p class="text-sm text-neutral-600">{{ __('A navegacao do Staff passa a cobrir governanca, acompanhamento e estoque central sem transformar o portal em tela operacional de evento.') }}</p>
                </div>

                <div class="grid gap-3">
                    <div class="rounded-2xl border border-neutral-200 bg-neutral-50 p-4 text-sm text-neutral-700">
                        {{ __('O modulo central atual continua sendo a referencia para cadastro, movimentacao, ajustes e auditoria fina.') }}
                    </div>
                    <div class="rounded-2xl border border-neutral-200 bg-neutral-50 p-4 text-sm text-neutral-700">
                        {{ __('Dentro do portal Staff, o foco fica em visibilidade institucional, alertas de reposicao e leitura consolidada do acervo nacional.') }}
                    </div>
                    <div class="rounded-2xl border border-neutral-200 bg-neutral-50 p-4 text-sm text-neutral-700">
                        {{ __('Assim, Conselho e governanca nao ficam misturados com a operacao de evento nem com rotinas locais da base.') }}
                    </div>
                </div>
            </div>
        </section>

        <section class="rounded-3xl border border-neutral-200 bg-white p-6 shadow-sm">
            <div class="mb-4">
                <h2 class="text-lg font-semibold text-neutral-950">{{ __('Itens centrais e saldo atual') }}</h2>
                <p class="text-sm text-neutral-600">{{ __('Resumo consolidado do que o acervo central recebeu, perdeu e ainda possui em cada estrutura nacional.') }}</p>
            </div>

            <div class="overflow-x-auto rounded-2xl border border-neutral-200">
                <table class="w-full text-left text-sm">
                    <thead class="bg-neutral-50 text-xs uppercase tracking-[0.18em] text-neutral-500">
                        <tr>
                            <th class="px-4 py-3">{{ __('Estoque') }}</th>
                            <th class="px-4 py-3">{{ __('Item') }}</th>
                            <th class="px-4 py-3 text-center">{{ __('Recebido') }}</th>
                            <th class="px-4 py-3 text-center">{{ __('Perdas') }}</th>
                            <th class="px-4 py-3 text-center">{{ __('Saldo') }}</th>
                            <th class="px-4 py-3 text-center">{{ __('Minimo') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($inventory['items'] as $item)
                            <tr class="border-t border-neutral-200 {{ $item['needs_restock'] ? 'bg-amber-50/60' : 'bg-white' }}">
                                <td class="px-4 py-3 font-medium text-neutral-900">{{ $item['inventory_name'] }}</td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-neutral-900">{{ $item['material_name'] }}</div>
                                    <div class="text-xs text-neutral-500">{{ $item['type'] === 'composite' ? __('Composto') : __('Simples') }}</div>
                                </td>
                                <td class="px-4 py-3 text-center">{{ $item['received_items'] }}</td>
                                <td class="px-4 py-3 text-center">{{ $item['lost_items'] }}</td>
                                <td class="px-4 py-3 text-center font-semibold text-neutral-950">{{ $item['current_quantity'] }}</td>
                                <td class="px-4 py-3 text-center">{{ $item['minimum_stock'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-6 text-center text-sm text-neutral-600">{{ __('Ainda nao ha itens consolidados no estoque central.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="grid gap-4 xl:grid-cols-2">
            <div class="rounded-3xl border border-neutral-200 bg-white p-6 shadow-sm">
                <div class="mb-4">
                    <h2 class="text-lg font-semibold text-neutral-950">{{ __('Alertas de reposicao') }}</h2>
                    <p class="text-sm text-neutral-600">{{ __('Itens centrais abaixo do minimo configurado e a lacuna estimada para recomposicao.') }}</p>
                </div>

                <div class="grid gap-3">
                    @forelse ($inventory['alerts'] as $alert)
                        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4">
                            <div class="text-sm font-semibold text-amber-950">{{ $alert['material_name'] }}</div>
                            <div class="text-sm text-amber-900">{{ $alert['inventory_name'] }}</div>
                            <div class="mt-2 text-xs font-semibold uppercase tracking-[0.18em] text-amber-800">
                                {{ __('Saldo :current · Minimo :minimum · Faltam :gap', ['current' => $alert['current_quantity'], 'minimum' => $alert['minimum_stock'], 'gap' => $alert['gap']]) }}
                            </div>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-neutral-300 bg-neutral-50 p-4 text-sm text-neutral-600">
                            {{ __('Nenhum alerta de reposicao aberto no acervo central neste momento.') }}
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="rounded-3xl border border-neutral-200 bg-white p-6 shadow-sm">
                <div class="mb-4">
                    <h2 class="text-lg font-semibold text-neutral-950">{{ __('Movimentacoes recentes') }}</h2>
                    <p class="text-sm text-neutral-600">{{ __('Ultimos movimentos auditaveis do estoque central reaproveitados do modulo atual.') }}</p>
                </div>

                <div class="grid gap-3">
                    @forelse ($inventory['recent_movements'] as $movement)
                        <div class="rounded-2xl border border-neutral-200 bg-neutral-50 p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="text-sm font-semibold text-neutral-950">{{ $movement['material_name'] }}</div>
                                    <div class="text-sm text-neutral-600">{{ $movement['inventory_name'] }}</div>
                                </div>
                                <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $movement['type_classes'] }}">
                                    {{ $movement['type_label'] }}
                                </span>
                            </div>

                            <div class="mt-3 text-sm text-neutral-700">
                                {{ __('Quantidade: :quantity · Saldo apos movimento: :balance', ['quantity' => $movement['quantity'], 'balance' => $movement['balance_after']]) }}
                            </div>
                            <div class="mt-1 text-xs text-neutral-500">
                                {{ __('Registrado por :actor em :date', ['actor' => $movement['actor'], 'date' => $movement['created_at']]) }}
                            </div>

                            @if ($movement['notes'])
                                <div class="mt-2 text-sm text-neutral-600">{{ $movement['notes'] }}</div>
                            @endif
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-neutral-300 bg-neutral-50 p-4 text-sm text-neutral-600">
                            {{ __('Nenhuma movimentacao recente foi encontrada no estoque central.') }}
                        </div>
                    @endforelse
                </div>
            </div>
        </section>
    </div>
</x-layouts.app>
