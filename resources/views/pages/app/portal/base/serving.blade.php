<x-layouts.app :title="__('Treinamentos em que Sirvo')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <x-app.portal.page-header eyebrow="Portal Base" title="Treinamentos em que Sirvo"
            description="Sua frente operacional no Portal Base: acompanhe os eventos onde voce atua sem confundir isso com a gestao institucional da base anfitria."
            :breadcrumbs="[
                ['label' => 'Portais', 'url' => route('app.start')],
                ['label' => 'Base e Treinamentos', 'url' => route('app.portal.base.dashboard')],
                ['label' => 'Treinamentos em que Sirvo', 'current' => true],
            ]" />

        <section class="grid gap-4 md:grid-cols-3">
            <x-app.portal.stat-card label="Proximos treinamentos" :value="$overview['counts']['serving_upcoming']" tone="sky" />
            <x-app.portal.stat-card label="Em andamento" :value="$overview['counts']['in_progress_serving']" tone="emerald" />
            <x-app.portal.stat-card label="Relatorios pendentes" :value="$overview['counts']['pending_reports']" tone="amber" />
        </section>

        <section class="rounded-3xl border border-sky-200 bg-linear-to-r from-sky-50 via-white to-amber-50 p-5 shadow-sm sm:p-6">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                <div class="max-w-3xl space-y-2">
                    <h2 class="text-lg font-semibold text-neutral-950">{{ __('Eventos onde sua atuacao esta vinculada') }}</h2>
                    <p class="text-sm text-neutral-700">
                        {{ __('A listagem abaixo reaproveita o fluxo operacional atual de treinamentos, mas dentro do contexto do Portal Base. Ela mostra somente eventos em que voce serve como professor titular, professor auxiliar ou mentor.') }}
                    </p>
                    <p class="text-sm text-neutral-600">
                        {{ __('Servir em um evento nao concede gestao completa da igreja-base anfitria. O acesso institucional continua separado nos fluxos proprios.') }}
                    </p>
                </div>

                <div class="rounded-2xl border border-white/80 bg-white/80 px-4 py-3 text-sm text-neutral-700 shadow-sm">
                    <div class="font-semibold text-neutral-950">{{ __('Filtros desta area') }}</div>
                    <div>{{ __('Status por abas, atuacao, igreja e periodo.') }}</div>
                </div>
            </div>
        </section>

        <section class="rounded-3xl border border-neutral-200 bg-white p-4 shadow-sm sm:p-6">
            <x-src.training-index
                role="portal-base-serving"
                :create-route="null"
                filter-mode="serving"
                :status-key="$statusKey"
                :statuses="$statuses"
                :groups="$groups"
                :filters="$filters"
            />
        </section>
    </div>
</x-layouts.app>
