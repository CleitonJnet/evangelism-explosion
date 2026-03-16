<x-layouts.app :title="$portal->label()">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <section class="rounded-3xl border border-neutral-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-3">
                <div class="flex items-center gap-3">
                    <span class="rounded-full bg-sky-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.24em] text-sky-800">
                        {{ $portal->label() }}
                    </span>

                    @if ($suggestedPortal === $portal)
                        <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">
                            {{ __('Portal sugerido') }}
                        </span>
                    @endif
                </div>

                <div class="flex flex-col gap-2">
                    <h1 class="text-2xl font-semibold text-neutral-900">
                        {{ $portalContext['headline'] }}
                    </h1>
                    <p class="max-w-3xl text-sm text-neutral-600">
                        {{ $portalContext['description'] }}
                    </p>
                </div>
            </div>
        </section>

        <section class="grid gap-4 xl:grid-cols-[minmax(0,1.35fr)_minmax(20rem,0.9fr)]">
            <div class="flex flex-col gap-4 rounded-3xl border border-neutral-200 bg-white p-6 shadow-sm">
                <div class="flex flex-col gap-2">
                    <h2 class="text-lg font-semibold text-neutral-900">{{ __('Estrutura inicial') }}</h2>
                    <p class="text-sm text-neutral-600">
                        {{ __('Este dashboard e um placeholder para a camada de experiencia do portal. O conteudo legado continua disponivel pelos links abaixo.') }}
                    </p>
                </div>

                <div class="grid gap-3 md:grid-cols-3">
                    @foreach ($portalContext['focusAreas'] as $focusArea)
                        <div class="rounded-2xl border border-dashed border-neutral-300 bg-neutral-50 p-4 text-sm font-medium text-neutral-700">
                            {{ $focusArea }}
                        </div>
                    @endforeach
                </div>

                <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                    {{ __('Proxima evolucao: migrar navegacao e modulos para o portal sem remover as rotas por role.') }}
                </div>
            </div>

            <div class="flex flex-col gap-4 rounded-3xl border border-neutral-200 bg-white p-6 shadow-sm">
                <div class="flex flex-col gap-2">
                    <h2 class="text-lg font-semibold text-neutral-900">{{ __('Contexto do usuario') }}</h2>
                    <p class="text-sm text-neutral-600">
                        {{ __('Roles detectadas para este portal e portais atualmente disponiveis para o usuario autenticado.') }}
                    </p>
                </div>

                <div class="flex flex-wrap gap-2">
                    @forelse ($portalContext['roleHints'] as $roleHint)
                        <span class="rounded-full bg-neutral-100 px-3 py-1 text-xs font-medium text-neutral-700">
                            {{ $roleHint }}
                        </span>
                    @empty
                        <span class="text-sm text-neutral-500">{{ __('Nenhuma role ligada diretamente a este portal.') }}</span>
                    @endforelse
                </div>

                <div class="flex flex-col gap-3">
                    @foreach ($resolvedPortals as $resolvedPortal)
                        <div class="rounded-2xl border border-neutral-200 p-4">
                            <div class="flex items-center justify-between gap-4">
                                <div class="flex flex-col gap-1">
                                    <span class="text-sm font-semibold text-neutral-900">{{ $resolvedPortal['label'] }}</span>
                                    <span class="text-xs text-neutral-500">{{ $resolvedPortal['description'] }}</span>
                                </div>

                                @if ($resolvedPortal['isSuggestedDefault'])
                                    <span class="rounded-full bg-emerald-100 px-3 py-1 text-[11px] font-semibold text-emerald-700">
                                        {{ __('Padrao') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="grid gap-4 lg:grid-cols-2">
            @foreach ($menuSections as $section)
                <div class="rounded-3xl border border-neutral-200 bg-white p-6 shadow-sm">
                    <div class="flex flex-col gap-4">
                        <div>
                            <h2 class="text-lg font-semibold text-neutral-900">{{ $section['title'] }}</h2>
                        </div>

                        <div class="flex flex-col gap-3">
                            @foreach ($section['items'] as $item)
                                <a href="{{ route($item['route']) }}"
                                    class="flex items-center justify-between gap-4 rounded-2xl border border-neutral-200 px-4 py-3 transition hover:border-sky-300 hover:bg-sky-50">
                                    <div class="flex flex-col gap-1">
                                        <span class="text-sm font-semibold text-neutral-900">{{ $item['label'] }}</span>
                                        @if ($item['description'] !== null)
                                            <span class="text-xs text-neutral-500">{{ $item['description'] }}</span>
                                        @endif
                                    </div>

                                    <span class="text-xs font-medium uppercase tracking-[0.18em] text-neutral-400">
                                        {{ $item['icon'] }}
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </section>
    </div>
</x-layouts.app>
