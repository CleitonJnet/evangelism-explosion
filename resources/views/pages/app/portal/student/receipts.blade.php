<x-layouts.app :title="__('Comprovantes do Aluno')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <x-app.portal.page-header
            eyebrow="Portal do aluno"
            title="Comprovantes"
            description="Acompanhe o que ainda precisa ser enviado e o que ja esta em validacao."
            :breadcrumbs="[
                ['label' => 'Portais', 'url' => route('app.start')],
                ['label' => 'Aluno', 'url' => route('app.portal.student.dashboard')],
                ['label' => 'Comprovantes', 'current' => true],
            ]" />

        <section class="grid gap-4 md:grid-cols-3">
            <x-app.portal.stat-card label="Pendentes" :value="$overview['counts']['pending_receipts']" tone="amber" />
            <x-app.portal.stat-card label="Em analise" :value="$overview['counts']['receipts_in_review']" tone="sky" />
            <x-app.portal.stat-card label="Pagamentos confirmados" :value="collect($overview['history_full'])->where('payment_confirmed', true)->count() + collect($overview['upcoming'])->where('payment_confirmed', true)->count() + collect($overview['in_progress'])->where('payment_confirmed', true)->count()" tone="emerald" />
        </section>

        <section class="grid gap-4 xl:grid-cols-2">
            <div class="rounded-3xl border border-neutral-200 bg-white p-6 shadow-sm">
                <div class="mb-4">
                    <h2 class="text-lg font-semibold text-neutral-950">{{ __('Acoes pendentes') }}</h2>
                    <p class="text-sm text-neutral-600">{{ __('Treinamentos que ainda esperam o envio do comprovante.') }}</p>
                </div>

                <div class="grid gap-3">
                    @forelse ($overview['receipt_pendencies'] as $training)
                        <x-app.portal.training-list-item :training="$training" />
                    @empty
                        <div class="rounded-2xl border border-dashed border-neutral-300 bg-neutral-50 p-4 text-sm text-neutral-600">
                            {{ __('Nenhum comprovante pendente neste momento.') }}
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="rounded-3xl border border-neutral-200 bg-white p-6 shadow-sm">
                <div class="mb-4">
                    <h2 class="text-lg font-semibold text-neutral-950">{{ __('Em validacao') }}</h2>
                    <p class="text-sm text-neutral-600">{{ __('Comprovantes enviados aguardando confirmacao da coordenacao.') }}</p>
                </div>

                <div class="grid gap-3">
                    @forelse ($overview['receipt_in_review'] as $training)
                        <x-app.portal.training-list-item :training="$training" />
                    @empty
                        <div class="rounded-2xl border border-dashed border-neutral-300 bg-neutral-50 p-4 text-sm text-neutral-600">
                            {{ __('Nenhum comprovante em analise no momento.') }}
                        </div>
                    @endforelse
                </div>
            </div>
        </section>
    </div>
</x-layouts.app>
