<x-layouts.app :title="__('Materiais de apoio do evento')">
    <x-app.portal.training-shell :training="$training" :tabs="$tabs" :active-tab="$activeTab" :capabilities="$capabilities" :portal-capabilities="$portalCapabilities" :assignments="$assignments" :training-context="$trainingContext" :portal-label="$portalLabel" :portal-roles="$portalRoles" :area-cards="$areaCards" :report-summary="$reportSummary">
        <div class="grid gap-4 xl:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
            <section class="rounded-3xl border border-neutral-200 bg-white p-6 shadow-sm">
                <div class="space-y-2">
                    <h3 class="text-lg font-semibold text-neutral-950">{{ __('Materiais de apoio da base') }}</h3>
                    <p class="text-sm text-neutral-600">{{ __('Leitura operacional para sustentar o evento sediado com acervo, kits e recursos locais.') }}</p>
                </div>

                <div class="mt-5 grid gap-3">
                    @forelse ($overview['my_base']['inventory_alerts'] as $inventory)
                        <a href="{{ $inventory['route'] }}"
                            class="rounded-2xl border border-neutral-200 bg-neutral-50 p-4 transition hover:border-sky-300">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <div class="text-sm font-semibold text-neutral-950">{{ $inventory['name'] }}</div>
                                    <div class="text-sm text-neutral-600">{{ $inventory['responsible'] ?: __('Responsavel nao informado') }}</div>
                                </div>
                                <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-800">
                                    {{ trans_choice(':count alerta|:count alertas', $inventory['low_stock_count'], ['count' => $inventory['low_stock_count']]) }}
                                </span>
                            </div>
                        </a>
                    @empty
                        <div class="rounded-2xl border border-dashed border-neutral-300 bg-neutral-50 p-4 text-sm text-neutral-600">
                            {{ __('Nenhum alerta de acervo foi encontrado para a base neste momento.') }}
                        </div>
                    @endforelse
                </div>
            </section>

            <section class="rounded-3xl border border-neutral-200 bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-neutral-950">{{ __('Conexoes rapidas') }}</h3>

                <div class="mt-4 grid gap-3 text-sm text-neutral-700">
                    @if ($portalCapabilities['manageTrainingRegistrations'] ?? false)
                        <a href="{{ route('app.portal.base.trainings.registrations', $training) }}" class="rounded-2xl border border-neutral-200 bg-neutral-50 px-4 py-3 transition hover:border-sky-300">
                            {{ __('Revisar participantes e necessidade de kits.') }}
                        </a>
                    @endif
                    <a href="{{ route('app.portal.base.trainings.preparation', $training) }}" class="rounded-2xl border border-neutral-200 bg-neutral-50 px-4 py-3 transition hover:border-sky-300">
                        {{ __('Voltar ao checklist de preparacao local.') }}
                    </a>
                    <a href="{{ route('app.portal.base.my-base') }}" class="rounded-2xl border border-neutral-200 bg-neutral-50 px-4 py-3 transition hover:border-sky-300">
                        {{ __('Abrir Minha Base para contexto de igreja anfitria.') }}
                    </a>
                    @if ($navigation['canViewBaseInventory'] ?? false)
                        <a href="{{ route('app.portal.base.inventory') }}" class="rounded-2xl border border-neutral-200 bg-neutral-50 px-4 py-3 transition hover:border-sky-300">
                            {{ __('Abrir Acervo da Base com saldo e historico.') }}
                        </a>
                    @endif
                    <div class="rounded-2xl border border-dashed border-neutral-300 bg-neutral-50 px-4 py-3 text-neutral-500">
                        {{ __('A visualizacao e a operacao de materiais agora seguem a mesma matriz de capability do portal.') }}
                    </div>
                </div>
            </section>
        </div>
    </x-app.portal.training-shell>
</x-layouts.app>
