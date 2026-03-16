<x-layouts.app :title="__('Portal do Aluno')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <x-app.portal.page-header
            eyebrow="Portal do aluno"
            :title="$portalContext['headline']"
            :description="'Tudo o que o aluno precisa em um unico lugar: agenda, comprovantes, historico e proximos passos.'"
            :breadcrumbs="[
                ['label' => 'Portais', 'url' => route('app.start')],
                ['label' => 'Aluno', 'current' => true],
            ]">
            <flux:button variant="primary" :href="route('app.portal.student.trainings.index')" wire:navigate>
                {{ __('Ver meus treinamentos') }}
            </flux:button>
        </x-app.portal.page-header>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <x-app.portal.stat-card label="Proximos treinamentos" :value="$overview['counts']['upcoming']" hint="Eventos futuros ja inscritos." tone="sky" />
            <x-app.portal.stat-card label="Em andamento" :value="$overview['counts']['in_progress']" hint="Treinamentos que ja estao acontecendo." tone="emerald" />
            <x-app.portal.stat-card label="Pendencias de comprovante" :value="$overview['counts']['pending_receipts']" hint="Pagamentos que ainda precisam de comprovante." tone="amber" />
            <x-app.portal.stat-card label="Historico" :value="$overview['counts']['history']" hint="Participacoes concluidas no portal." />
        </section>

        <section class="grid gap-4 xl:grid-cols-[minmax(0,1.3fr)_minmax(22rem,0.9fr)]">
            <div class="flex flex-col gap-4 rounded-3xl border border-neutral-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-semibold text-neutral-950">{{ __('Proximos treinamentos') }}</h2>
                        <p class="text-sm text-neutral-600">{{ __('Uma visao rapida do que vem a seguir na sua jornada.') }}</p>
                    </div>

                    <a href="{{ route('app.portal.student.trainings.index') }}" class="text-sm font-semibold text-sky-800">
                        {{ __('Ver tudo') }}
                    </a>
                </div>

                <div class="grid gap-4">
                    @forelse ($overview['upcoming'] as $training)
                        <x-app.portal.training-list-item :training="$training" />
                    @empty
                        <div class="rounded-2xl border border-dashed border-neutral-300 bg-neutral-50 p-5 text-sm text-neutral-600">
                            {{ __('Voce nao possui novos treinamentos agendados no momento.') }}
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="flex flex-col gap-4">
                <div class="rounded-3xl border border-neutral-200 bg-white p-6 shadow-sm">
                    <div class="flex flex-col gap-3">
                        <div>
                            <h2 class="text-lg font-semibold text-neutral-950">{{ __('Treinamentos em andamento') }}</h2>
                            <p class="text-sm text-neutral-600">{{ __('Acesse rapidamente o que precisa acompanhar agora.') }}</p>
                        </div>

                        <div class="grid gap-3">
                            @forelse ($overview['in_progress'] as $training)
                                <x-app.portal.training-list-item :training="$training" />
                            @empty
                                <div class="rounded-2xl border border-dashed border-neutral-300 bg-neutral-50 p-4 text-sm text-neutral-600">
                                    {{ __('Nenhum treinamento esta em andamento hoje.') }}
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl border border-neutral-200 bg-white p-6 shadow-sm">
                    <div class="flex flex-col gap-3">
                        <div>
                            <h2 class="text-lg font-semibold text-neutral-950">{{ __('Atalhos uteis') }}</h2>
                            <p class="text-sm text-neutral-600">{{ __('Entradas rapidas para as tarefas mais importantes do portal.') }}</p>
                        </div>

                        <div class="grid gap-3">
                            @foreach ($overview['shortcuts'] as $shortcut)
                                <a href="{{ $shortcut['route'] }}"
                                    class="rounded-2xl border border-neutral-200 bg-neutral-50 px-4 py-3 transition hover:border-sky-300 hover:bg-sky-50">
                                    <div class="text-sm font-semibold text-neutral-950">{{ $shortcut['label'] }}</div>
                                    <div class="text-xs text-neutral-500">{{ $shortcut['description'] }}</div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="grid gap-4 xl:grid-cols-2">
            <div class="rounded-3xl border border-neutral-200 bg-white p-6 shadow-sm">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-semibold text-neutral-950">{{ __('Pendencias de comprovante') }}</h2>
                        <p class="text-sm text-neutral-600">{{ __('Treinamentos que ainda precisam de acao sua ou estao em validacao.') }}</p>
                    </div>

                    <a href="{{ route('app.portal.student.receipts') }}" class="text-sm font-semibold text-sky-800">
                        {{ __('Abrir area') }}
                    </a>
                </div>

                <div class="grid gap-3">
                    @forelse ($overview['receipt_pendencies'] as $training)
                        <x-app.portal.training-list-item :training="$training" />
                    @empty
                        <div class="rounded-2xl border border-dashed border-neutral-300 bg-neutral-50 p-4 text-sm text-neutral-600">
                            {{ __('Nenhuma pendencia de comprovante aberta.') }}
                        </div>
                    @endforelse

                    @foreach ($overview['receipt_in_review'] as $training)
                        <x-app.portal.training-list-item :training="$training" />
                    @endforeach
                </div>
            </div>

            <div class="rounded-3xl border border-neutral-200 bg-white p-6 shadow-sm">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-semibold text-neutral-950">{{ __('Historico resumido') }}</h2>
                        <p class="text-sm text-neutral-600">{{ __('Um resumo recente das suas participacoes concluidas.') }}</p>
                    </div>

                    <a href="{{ route('app.portal.student.history') }}" class="text-sm font-semibold text-sky-800">
                        {{ __('Ver historico') }}
                    </a>
                </div>

                <div class="grid gap-3">
                    @forelse ($overview['history'] as $training)
                        <x-app.portal.training-list-item :training="$training" />
                    @empty
                        <div class="rounded-2xl border border-dashed border-neutral-300 bg-neutral-50 p-4 text-sm text-neutral-600">
                            {{ __('Seu historico ainda comeca aqui. Quando concluir treinamentos, eles aparecerao nesta secao.') }}
                        </div>
                    @endforelse
                </div>
            </div>
        </section>
    </div>
</x-layouts.app>
