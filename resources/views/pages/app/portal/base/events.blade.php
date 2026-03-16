<x-layouts.app :title="__('Eventos da Base')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <x-app.portal.page-header eyebrow="Portal Base" title="Eventos da Base"
            description="Operacao local dos eventos sediados pela sua igreja-base, separada da lista de treinamentos em que voce apenas serve fora dela."
            :breadcrumbs="[
                ['label' => 'Portais', 'url' => route('app.start')],
                ['label' => 'Base e Treinamentos', 'url' => route('app.portal.base.dashboard')],
                ['label' => 'Eventos da Base', 'current' => true],
            ]" />

        <section class="grid gap-4 md:grid-cols-3">
            <x-app.portal.stat-card label="Eventos sediados" :value="$overview['counts']['hosted_events_total']" tone="sky" />
            <x-app.portal.stat-card label="Programacao pendente" :value="$overview['counts']['pending_programming']" tone="amber" />
            <x-app.portal.stat-card label="Sirvo fora da base" :value="$overview['counts']['serving_outside_base']" tone="emerald" />
        </section>

        <section class="rounded-3xl border border-sky-200 bg-linear-to-r from-sky-50 via-white to-emerald-50 p-5 shadow-sm sm:p-6">
            <div class="grid gap-4 lg:grid-cols-2">
                <div class="space-y-2">
                    <h2 class="text-lg font-semibold text-neutral-950">{{ __('Foco da igreja anfitria') }}</h2>
                    <p class="text-sm text-neutral-700">
                        {{ __('Esta area mostra somente os eventos pelos quais a sua base responde localmente: recepcao, preparo, materiais, agenda e operacao no local.') }}
                    </p>
                </div>
                <div class="space-y-2">
                    <h2 class="text-lg font-semibold text-neutral-950">{{ __('Separacao visual obrigatoria') }}</h2>
                    <p class="text-sm text-neutral-700">
                        {{ __('Se voce serve em um evento hospedado por outra base, ele continua aparecendo no Portal Base, mas fica destacado como contexto de servico, sem virar visao institucional completa da base anfitria.') }}
                    </p>
                </div>
            </div>
        </section>

        <section class="grid gap-4 xl:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
            <div class="rounded-3xl border border-neutral-200 bg-white p-6 shadow-sm">
                <div class="mb-4 space-y-1">
                    <h2 class="text-lg font-semibold text-neutral-950">{{ __('Eventos sediados pela minha base') }}</h2>
                    <p class="text-sm text-neutral-600">{{ __('Lista operacional completa dos eventos em que a igreja-base atual e a anfitria local.') }}</p>
                </div>

                <div class="grid gap-3">
                    @forelse ($overview['base_events']['all'] as $training)
                        <x-app.portal.training-list-item :training="$training" />
                    @empty
                        <div class="rounded-2xl border border-dashed border-neutral-300 bg-neutral-50 p-4 text-sm text-neutral-600">
                            {{ __('Nenhum evento sediado pela base foi encontrado no momento.') }}
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="space-y-4">
                <div class="rounded-3xl border border-neutral-200 bg-white p-6 shadow-sm">
                    <div class="mb-4 space-y-1">
                        <h2 class="text-lg font-semibold text-neutral-950">{{ __('Subareas da operacao local') }}</h2>
                        <p class="text-sm text-neutral-600">{{ __('Cada evento sediado abre uma navegacao contextual com frente local explicita.') }}</p>
                    </div>

                    <div class="grid gap-3 text-sm text-neutral-700">
                        <div class="rounded-2xl border border-neutral-200 bg-neutral-50 px-4 py-3">{{ __('Visao geral') }}</div>
                        <div class="rounded-2xl border border-neutral-200 bg-neutral-50 px-4 py-3">{{ __('Inscricoes locais') }}</div>
                        <div class="rounded-2xl border border-neutral-200 bg-neutral-50 px-4 py-3">{{ __('Preparacao local') }}</div>
                        <div class="rounded-2xl border border-neutral-200 bg-neutral-50 px-4 py-3">{{ __('Programacao') }}</div>
                        <div class="rounded-2xl border border-neutral-200 bg-neutral-50 px-4 py-3">{{ __('Materiais de apoio') }}</div>
                        <div class="rounded-2xl border border-dashed border-neutral-300 bg-neutral-50 px-4 py-3 text-neutral-500">{{ __('Relatorios da igreja (fase futura)') }}</div>
                    </div>
                </div>

                <div class="rounded-3xl border border-neutral-200 bg-white p-6 shadow-sm">
                    <div class="mb-4 space-y-1">
                        <h2 class="text-lg font-semibold text-neutral-950">{{ __('Quando eu sirvo fora da minha base') }}</h2>
                        <p class="text-sm text-neutral-600">{{ __('Esses eventos continuam acessiveis, mas claramente separados do papel de igreja anfitria.') }}</p>
                    </div>

                    <div class="grid gap-3">
                        @forelse ($overview['serving']['outside_base'] as $training)
                            <x-app.portal.training-list-item :training="$training" :show-status="false" />
                        @empty
                            <div class="rounded-2xl border border-dashed border-neutral-300 bg-neutral-50 p-4 text-sm text-neutral-600">
                                {{ __('Nenhum treinamento fora da base aparece no seu contexto atual.') }}
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </section>
    </div>
</x-layouts.app>
