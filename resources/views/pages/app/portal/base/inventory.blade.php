<x-layouts.app :title="__('Acervo da Base')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <x-app.portal.page-header eyebrow="Portal Base" title="Acervo da Base"
            description="Patrimonio e materiais da base com saldo local, historico de entradas, uso por evento e alertas de reposicao."
            :breadcrumbs="[
                ['label' => 'Portais', 'url' => route('app.start')],
                ['label' => 'Base e Treinamentos', 'url' => route('app.portal.base.dashboard')],
                ['label' => 'Acervo da Base', 'current' => true],
            ]" />

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-6">
            <x-app.portal.stat-card label="Estoques da base" :value="$inventory['summary']['inventories_count']" hint="Estruturas locais vinculadas a sua base." tone="sky" />
            <x-app.portal.stat-card label="Itens ativos" :value="$inventory['summary']['materials_count']" hint="Materiais com saldo ou historico consolidado." tone="emerald" />
            <x-app.portal.stat-card label="Alertas" :value="$inventory['summary']['low_stock_count']" hint="Itens abaixo do minimo configurado." tone="amber" />
            <x-app.portal.stat-card label="Entradas recentes" :value="$inventory['summary']['recent_entries_count']" hint="Entradas e ajustes recentes do acervo." />
            <x-app.portal.stat-card label="Uso em eventos" :value="$inventory['summary']['event_usage_count']" hint="Eventos com consumo registrado." />
            <x-app.portal.stat-card label="Necessidades futuras" :value="$inventory['summary']['upcoming_needs_count']" hint="Eventos com material ainda faltando." tone="amber" />
        </section>

        <section class="grid gap-4 xl:grid-cols-[minmax(0,1.2fr)_minmax(20rem,0.8fr)]">
            <div class="rounded-3xl border border-neutral-200 bg-white p-6 shadow-sm">
                <div class="mb-4">
                    <h2 class="text-lg font-semibold text-neutral-950">{{ __('Estoques vinculados a esta base') }}</h2>
                    <p class="text-sm text-neutral-600">{{ __('A leitura abaixo considera apenas estoques do tipo base vinculados a sua igreja-base atual.') }}</p>
                </div>

                <div class="grid gap-3">
                    @forelse ($inventory['inventories'] as $baseInventory)
                        <div class="rounded-2xl border border-neutral-200 bg-neutral-50 p-4">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <div class="text-sm font-semibold text-neutral-950">{{ $baseInventory['name'] }}</div>
                                    <div class="text-sm text-neutral-600">{{ $baseInventory['responsible'] }} · {{ $baseInventory['location'] }}</div>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <span class="rounded-full bg-sky-100 px-2.5 py-1 text-xs font-semibold text-sky-800">{{ __('Base') }}</span>
                                    <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $baseInventory['status'] === 'Ativo' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-700' }}">{{ $baseInventory['status'] }}</span>
                                    <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-800">{{ trans_choice(':count alerta|:count alertas', $baseInventory['low_stock_count'], ['count' => $baseInventory['low_stock_count']]) }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-neutral-300 bg-neutral-50 p-5 text-sm text-neutral-600">
                            {{ __('Nenhum estoque da base foi vinculado ainda. O staff pode cadastrar um estoque do tipo base no modulo central de inventario sem quebrar a operacao atual.') }}
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="rounded-3xl border border-neutral-200 bg-white p-6 shadow-sm">
                <div class="mb-4">
                    <h2 class="text-lg font-semibold text-neutral-950">{{ __('Alertas de reposicao') }}</h2>
                    <p class="text-sm text-neutral-600">{{ __('Itens abaixo do minimo configurado e lacuna estimada para recomposicao.') }}</p>
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
                            {{ __('Nenhum alerta de reposicao aberto neste momento.') }}
                        </div>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="rounded-3xl border border-neutral-200 bg-white p-6 shadow-sm">
            <div class="mb-4">
                <h2 class="text-lg font-semibold text-neutral-950">{{ __('Itens da base e saldo atual') }}</h2>
                <p class="text-sm text-neutral-600">{{ __('Resumo do que a base adquiriu, recebeu, perdeu e ainda possui em cada estoque local.') }}</p>
            </div>

            <div class="overflow-x-auto rounded-2xl border border-neutral-200">
                <table class="w-full text-left text-sm">
                    <thead class="bg-neutral-50 text-xs uppercase tracking-[0.18em] text-neutral-500">
                        <tr>
                            <th class="px-4 py-3">{{ __('Estoque') }}</th>
                            <th class="px-4 py-3">{{ __('Item') }}</th>
                            <th class="px-4 py-3 text-center">{{ __('Recebido') }}</th>
                            <th class="px-4 py-3 text-center">{{ __('Usado / perdido') }}</th>
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
                                <td colspan="6" class="px-4 py-6 text-center text-sm text-neutral-600">{{ __('Ainda nao ha itens consolidados no acervo desta base.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="grid gap-4 xl:grid-cols-2">
            <div class="rounded-3xl border border-neutral-200 bg-white p-6 shadow-sm">
                <div class="mb-4">
                    <h2 class="text-lg font-semibold text-neutral-950">{{ __('Historico de entradas relevantes') }}</h2>
                    <p class="text-sm text-neutral-600">{{ __('Entradas, transferencias recebidas e ajustes mais recentes do acervo local.') }}</p>
                </div>

                <div class="grid gap-3">
                    @forelse ($inventory['recentEntries'] as $entry)
                        <div class="rounded-2xl border border-neutral-200 bg-neutral-50 p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="text-sm font-semibold text-neutral-950">{{ $entry['material_name'] }}</div>
                                    <div class="text-sm text-neutral-600">{{ $entry['inventory_name'] }} · {{ $entry['type_label'] }}</div>
                                </div>
                                <div class="text-right text-sm font-semibold text-emerald-700">+{{ $entry['quantity'] }}</div>
                            </div>
                            <div class="mt-2 text-xs text-neutral-500">{{ __('Saldo apos movimento: :balance · por :actor em :date', ['balance' => $entry['balance_after'], 'actor' => $entry['actor'], 'date' => $entry['created_at']]) }}</div>
                            @if ($entry['notes'])
                                <div class="mt-2 text-sm text-neutral-600">{{ $entry['notes'] }}</div>
                            @endif
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-neutral-300 bg-neutral-50 p-4 text-sm text-neutral-600">
                            {{ __('Nenhuma entrada relevante foi registrada ainda neste acervo.') }}
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="rounded-3xl border border-neutral-200 bg-white p-6 shadow-sm">
                <div class="mb-4">
                    <h2 class="text-lg font-semibold text-neutral-950">{{ __('Uso por evento') }}</h2>
                    <p class="text-sm text-neutral-600">{{ __('Saidas vinculadas a treinamentos sediados ou operados a partir desta base.') }}</p>
                </div>

                <div class="grid gap-3">
                    @forelse ($inventory['eventUsage'] as $usage)
                        <a href="{{ $usage['route'] }}" class="rounded-2xl border border-neutral-200 bg-neutral-50 p-4 transition hover:border-sky-300 hover:bg-sky-50">
                            <div class="text-sm font-semibold text-neutral-950">{{ $usage['title'] }}</div>
                            <div class="text-sm text-neutral-600">{{ $usage['church_name'] }}</div>
                            <div class="mt-2 flex flex-wrap gap-2 text-xs font-semibold">
                                <span class="rounded-full bg-sky-100 px-2.5 py-1 text-sky-800">{{ __(':count itens usados', ['count' => $usage['total_quantity']]) }}</span>
                                <span class="rounded-full bg-neutral-200 px-2.5 py-1 text-neutral-700">{{ __(':count materiais', ['count' => $usage['materials_count']]) }}</span>
                            </div>
                            <div class="mt-2 text-xs text-neutral-500">{{ __('Ultimo movimento em :date', ['date' => $usage['last_movement_at']]) }}</div>
                        </a>
                    @empty
                        <div class="rounded-2xl border border-dashed border-neutral-300 bg-neutral-50 p-4 text-sm text-neutral-600">
                            {{ __('Nenhum consumo por evento foi encontrado para este acervo.') }}
                        </div>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="rounded-3xl border border-neutral-200 bg-white p-6 shadow-sm">
            <div class="mb-4">
                <h2 class="text-lg font-semibold text-neutral-950">{{ __('Necessidades para proximos eventos') }}</h2>
                <p class="text-sm text-neutral-600">{{ __('Leitura inicial do que os cursos futuros exigem e ainda nao aparece com saldo disponivel no acervo da base.') }}</p>
            </div>

            <div class="grid gap-3">
                @forelse ($inventory['upcomingNeeds'] as $need)
                    <a href="{{ $need['route'] }}" class="rounded-2xl border border-neutral-200 bg-neutral-50 p-4 transition hover:border-sky-300 hover:bg-sky-50">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <div class="text-sm font-semibold text-neutral-950">{{ $need['title'] }}</div>
                                <div class="text-sm text-neutral-600">{{ __('Inicio previsto em :date', ['date' => $need['first_date'] ?? __('data nao informada')]) }}</div>
                            </div>
                            <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-800">
                                {{ trans_choice(':count item faltando|:count itens faltando', count($need['missing_materials']), ['count' => count($need['missing_materials'])]) }}
                            </span>
                        </div>
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach ($need['missing_materials'] as $material)
                                <span class="rounded-full bg-white px-2.5 py-1 text-xs font-semibold text-neutral-700">{{ $material['name'] }}</span>
                            @endforeach
                        </div>
                    </a>
                @empty
                    <div class="rounded-2xl border border-dashed border-neutral-300 bg-neutral-50 p-4 text-sm text-neutral-600">
                        {{ __('Nao identificamos necessidades abertas para os proximos eventos com base nos materiais vinculados aos cursos.') }}
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</x-layouts.app>
