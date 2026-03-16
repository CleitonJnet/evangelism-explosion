<x-layouts.app :title="__('Historico do Aluno')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <x-app.portal.page-header
            eyebrow="Portal do aluno"
            title="Historico"
            description="Treinamentos concluidos e principais marcos da sua participacao."
            :breadcrumbs="[
                ['label' => 'Portais', 'url' => route('app.start')],
                ['label' => 'Aluno', 'url' => route('app.portal.student.dashboard')],
                ['label' => 'Historico', 'current' => true],
            ]" />

        <section class="grid gap-4 md:grid-cols-3">
            <x-app.portal.stat-card label="Treinamentos concluidos" :value="$overview['counts']['history']" />
            <x-app.portal.stat-card label="Credenciados" :value="collect($overview['history_full'])->where('accredited', true)->count()" tone="emerald" />
            <x-app.portal.stat-card label="Kits entregues" :value="collect($overview['history_full'])->where('kit', true)->count()" tone="sky" />
        </section>

        <section class="grid gap-4">
            @forelse ($overview['history_full'] as $training)
                <x-app.portal.training-list-item :training="$training" />
            @empty
                <div class="rounded-3xl border border-dashed border-neutral-300 bg-white p-8 text-sm text-neutral-600 shadow-sm">
                    {{ __('Nenhum treinamento concluido ainda. Assim que sua jornada avancar, o historico aparecera aqui.') }}
                </div>
            @endforelse
        </section>
    </div>
</x-layouts.app>
