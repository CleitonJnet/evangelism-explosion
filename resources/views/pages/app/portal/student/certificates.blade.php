<x-layouts.app :title="__('Certificados do Aluno')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <x-app.portal.page-header
            eyebrow="Portal do aluno"
            title="Certificados"
            description="Area preparada para crescer sem quebrar a navegacao atual. Quando a emissao estiver pronta, os certificados serao listados aqui."
            :breadcrumbs="[
                ['label' => 'Portais', 'url' => route('app.start')],
                ['label' => 'Aluno', 'url' => route('app.portal.student.dashboard')],
                ['label' => 'Certificados', 'current' => true],
            ]" />

        <section class="grid gap-4 md:grid-cols-3">
            <x-app.portal.stat-card label="Treinamentos concluidos" :value="$overview['counts']['history']" />
            <x-app.portal.stat-card label="Itens previstos" :value="$overview['counts']['certificates']" tone="sky" hint="Base inicial para futuras emissoes." />
            <x-app.portal.stat-card label="Disponiveis agora" :value="0" tone="amber" hint="Placeholder extensivel, sem quebrar a jornada do aluno." />
        </section>

        <section class="rounded-3xl border border-neutral-200 bg-white p-6 shadow-sm">
            <div class="mb-5 flex flex-col gap-2">
                <h2 class="text-lg font-semibold text-neutral-950">{{ __('Mapa de certificados') }}</h2>
                <p class="text-sm text-neutral-600">{{ __('Mantivemos a estrutura pronta para a proxima etapa sem inventar dados artificiais nem mudar o modelo atual.') }}</p>
            </div>

            <div class="grid gap-3">
                @forelse ($overview['certificates'] as $certificate)
                    <div class="rounded-2xl border border-neutral-200 bg-neutral-50 p-5">
                        <div class="flex flex-col gap-2 lg:flex-row lg:items-center lg:justify-between">
                            <div class="flex flex-col gap-1">
                                <h3 class="text-base font-semibold text-neutral-950">{{ $certificate['title'] }}</h3>
                                <p class="text-sm text-neutral-600">{{ $certificate['schedule_summary'] }}</p>
                            </div>

                            <span class="rounded-full bg-amber-100 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-amber-800">
                                {{ __('Em preparacao') }}
                            </span>
                        </div>

                        <div class="mt-4 grid gap-3 text-sm text-neutral-600 md:grid-cols-2">
                            <div>
                                <span class="text-xs font-semibold uppercase tracking-[0.18em] text-neutral-400">{{ __('Tipo previsto') }}</span>
                                <p>{{ $certificate['certificate_label'] }}</p>
                            </div>
                            <div>
                                <span class="text-xs font-semibold uppercase tracking-[0.18em] text-neutral-400">{{ __('Status') }}</span>
                                <p>{{ ucfirst($certificate['certificate_status']) }}</p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="rounded-2xl border border-dashed border-neutral-300 bg-neutral-50 p-5 text-sm text-neutral-600">
                        {{ __('Assim que houver treinamentos concluidos, esta area mostrara a trilha de certificados prevista para voce.') }}
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</x-layouts.app>
