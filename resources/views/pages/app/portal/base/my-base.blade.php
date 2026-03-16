<x-layouts.app :title="__('Minha Base')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <x-app.portal.page-header eyebrow="Portal Base" title="Minha Base"
            description="Base local, igreja anfitria, alertas de acervo e eventos ligados ao seu contexto ministerial."
            :breadcrumbs="[
                ['label' => 'Portais', 'url' => route('app.start')],
                ['label' => 'Base e Treinamentos', 'url' => route('app.portal.base.dashboard')],
                ['label' => 'Minha Base', 'current' => true],
            ]" />

        <section class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_minmax(20rem,0.9fr)]">
            <div class="rounded-3xl border border-neutral-200 bg-white p-6 shadow-sm">
                <div class="mb-4">
                    <h2 class="text-lg font-semibold text-neutral-950">{{ __('Contexto da igreja-base') }}</h2>
                    <p class="text-sm text-neutral-600">{{ __('Ponto de entrada para quem opera a vida local e os eventos sediados.') }}</p>
                </div>

                @if ($overview['my_base']['church'])
                    <div class="grid gap-3">
                        <div class="rounded-2xl border border-neutral-200 bg-neutral-50 p-5">
                            <div class="text-lg font-semibold text-neutral-950">{{ $overview['my_base']['church']['name'] }}</div>
                            <div class="text-sm text-neutral-600">{{ $overview['my_base']['church']['city'] ?: __('Cidade nao informada') }}{{ $overview['my_base']['church']['state'] ? ' - '.$overview['my_base']['church']['state'] : '' }}</div>
                            <div class="mt-3 text-xs font-semibold uppercase tracking-[0.18em] text-sky-700">{{ $overview['my_base']['church']['host_label'] }}</div>
                        </div>

                        @forelse ($overview['my_base']['hosted_events'] as $training)
                            <x-app.portal.training-list-item :training="$training" />
                        @empty
                            <div class="rounded-2xl border border-dashed border-neutral-300 bg-neutral-50 p-4 text-sm text-neutral-600">
                                {{ __('Nenhum evento futuro sediado pela sua base neste momento.') }}
                            </div>
                        @endforelse
                    </div>
                @else
                    <div class="rounded-2xl border border-dashed border-neutral-300 bg-neutral-50 p-5 text-sm text-neutral-600">
                        {{ __('Seu perfil ainda nao possui uma igreja-base vinculada. O portal continua funcional, mas esta frente depende desse contexto.') }}
                    </div>
                @endif
            </div>

            <div class="rounded-3xl border border-neutral-200 bg-white p-6 shadow-sm">
                <div class="mb-4 flex items-start justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-semibold text-neutral-950">{{ __('Acervo e materiais') }}</h2>
                        <p class="text-sm text-neutral-600">{{ __('Alertas de estoque que impactam o preparo da base e dos eventos.') }}</p>
                    </div>

                    @if ($navigation['canViewBaseInventory'] ?? false)
                        <a href="{{ route('app.portal.base.inventory') }}" class="text-sm font-semibold text-sky-800">{{ __('Abrir acervo') }}</a>
                    @endif
                </div>

                <div class="grid gap-3">
                    @forelse ($overview['my_base']['inventory_alerts'] as $inventory)
                        <a href="{{ $inventory['route'] }}" class="rounded-2xl border border-neutral-200 bg-neutral-50 p-4 transition hover:border-sky-300 hover:bg-sky-50">
                            <div class="text-sm font-semibold text-neutral-950">{{ $inventory['name'] }}</div>
                            <div class="text-xs text-neutral-500">{{ $inventory['responsible'] ?: __('Responsavel nao informado') }}</div>
                            <div class="mt-2 text-sm text-amber-800">{{ trans_choice(':count item abaixo do minimo', $inventory['low_stock_count'], ['count' => $inventory['low_stock_count']]) }}</div>
                        </a>
                    @empty
                        <div class="rounded-2xl border border-dashed border-neutral-300 bg-neutral-50 p-4 text-sm text-neutral-600">
                            {{ __('Nenhum alerta de acervo no momento.') }}
                        </div>
                    @endforelse
                </div>
            </div>
        </section>
    </div>
</x-layouts.app>
