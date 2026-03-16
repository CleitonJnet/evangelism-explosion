<x-layouts.app :title="__('Preparacao local do evento')">
    <x-app.portal.training-shell :training="$training" :tabs="$tabs" :active-tab="$activeTab" :capabilities="$capabilities" :portal-capabilities="$portalCapabilities" :assignments="$assignments" :training-context="$trainingContext" :portal-label="$portalLabel" :portal-roles="$portalRoles" :area-cards="$areaCards" :report-summary="$reportSummary">
        <div class="grid gap-4 xl:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
            <section class="rounded-3xl border border-neutral-200 bg-white p-6 shadow-sm">
                <div class="space-y-2">
                    <h3 class="text-lg font-semibold text-neutral-950">{{ __('Preparacao local da igreja anfitria') }}</h3>
                    <p class="text-sm text-neutral-600">{{ __('Use esta leitura para organizar o evento sediado pela base sem misturar isso com o contexto de servico em outras igrejas.') }}</p>
                </div>

                <div class="mt-5 grid gap-3 md:grid-cols-2">
                    @if ($portalCapabilities['manageTrainingRegistrations'] ?? false)
                        <a href="{{ route('app.portal.base.trainings.registrations', $training) }}"
                            class="rounded-2xl border border-neutral-200 bg-neutral-50 p-4 transition hover:border-sky-300">
                            <div class="text-sm font-semibold text-neutral-950">{{ __('Inscricoes locais') }}</div>
                            <p class="mt-2 text-sm text-neutral-600">{{ __('Confira comprovantes, pendencias e o volume de participantes esperados.') }}</p>
                        </a>
                    @endif
                    <a href="{{ route('app.portal.base.trainings.schedule', $training) }}"
                        class="rounded-2xl border border-neutral-200 bg-neutral-50 p-4 transition hover:border-sky-300">
                        <div class="text-sm font-semibold text-neutral-950">{{ __('Programacao do evento') }}</div>
                        <p class="mt-2 text-sm text-neutral-600">{{ __('Revise datas, blocos do dia e eventuais ajustes de agenda.') }}</p>
                    </a>
                    @if ($portalCapabilities['viewEventMaterials'] ?? false)
                        <a href="{{ route('app.portal.base.trainings.materials', $training) }}"
                            class="rounded-2xl border border-neutral-200 bg-neutral-50 p-4 transition hover:border-sky-300">
                            <div class="text-sm font-semibold text-neutral-950">{{ __('Materiais de apoio') }}</div>
                            <p class="mt-2 text-sm text-neutral-600">{{ __('Conecte acervo, kits e recursos da base para a execucao local.') }}</p>
                        </a>
                    @endif
                    <a href="{{ route('app.portal.base.my-base') }}"
                        class="rounded-2xl border border-neutral-200 bg-neutral-50 p-4 transition hover:border-sky-300">
                        <div class="text-sm font-semibold text-neutral-950">{{ __('Minha Base') }}</div>
                        <p class="mt-2 text-sm text-neutral-600">{{ __('Volte ao panorama da igreja-base, anfitria e alertas de acervo.') }}</p>
                    </a>
                </div>
            </section>

            <section class="rounded-3xl border border-neutral-200 bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-neutral-950">{{ __('Checklist de frente local') }}</h3>

                <div class="mt-4 grid gap-3 text-sm text-neutral-700">
                    <div class="rounded-2xl border border-neutral-200 bg-neutral-50 px-4 py-3">{{ __('Confirmar local, datas e janela do evento.') }}</div>
                    <div class="rounded-2xl border border-neutral-200 bg-neutral-50 px-4 py-3">{{ __('Alinhar volume de inscricoes com a recepcao da base.') }}</div>
                    <div class="rounded-2xl border border-neutral-200 bg-neutral-50 px-4 py-3">{{ __('Validar programacao e blocos operacionais do dia.') }}</div>
                    <div class="rounded-2xl border border-neutral-200 bg-neutral-50 px-4 py-3">{{ __('Separar materiais de apoio e kits necessarios.') }}</div>
                    <div class="rounded-2xl border border-dashed border-neutral-300 bg-neutral-50 px-4 py-3 text-neutral-500">{{ __('Relatorios da igreja e do professor foram previstos na matriz para a proxima fase.') }}</div>
                </div>
            </section>
        </div>
    </x-app.portal.training-shell>
</x-layouts.app>
