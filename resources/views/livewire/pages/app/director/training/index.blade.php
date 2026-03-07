<div>
    <x-src.toolbar.header :title="__('Gerenciamento de Treinamentos e Eventos')" :description="__('Controle os treinamentos do Evangelismo Explosivo, organizando status e cursos em um só lugar.')" />
    <x-src.toolbar.nav>
        <x-src.toolbar.button :href="route('app.director.training.create')" :label="__('Novo')" icon="plus" :tooltip="__('Novo treinamento')" />

        <span class="mx-1 h-7 w-px bg-slate-300/80"></span>

        @foreach ($statuses as $status)
            @php
                $icon = match ($status['key']) {
                    'planning' => 'hourglass',
                    'scheduled' => 'calendar',
                    'canceled' => 'x',
                    'completed' => 'check',
                    default => 'calendar',
                };

                $isActiveStatus = $statusKey === $status['key'];
                $statusButtonClasses = match ($status['key']) {
                    'planning' => $isActiveStatus
                        ? '!bg-amber-800 !text-amber-50 !border-amber-700 hover:!bg-amber-700'
                        : '!bg-amber-100/80 !text-amber-900 !border-amber-300 hover:!bg-amber-200/80',
                    'scheduled' => $isActiveStatus
                        ? '!bg-sky-900 !text-slate-100 !border-sky-700 hover:!bg-sky-800'
                        : '!bg-sky-100 !text-sky-900 !border-sky-300 hover:!bg-sky-200',
                    'canceled' => $isActiveStatus
                        ? '!bg-rose-800 !text-rose-50 !border-rose-700 hover:!bg-rose-700'
                        : '!bg-rose-100 !text-rose-900 !border-rose-300 hover:!bg-rose-200',
                    'completed' => $isActiveStatus
                        ? '!bg-emerald-800 !text-emerald-50 !border-emerald-700 hover:!bg-emerald-700'
                        : '!bg-emerald-100 !text-emerald-900 !border-emerald-300 hover:!bg-emerald-200',
                    default => null,
                };
            @endphp
            <x-src.toolbar.button :href="$status['route']" :label="$status['label']" :icon="$icon" :active="$statusKey === $status['key']"
                :tooltip="__(':label', ['label' => $status['label']])" :class="$statusButtonClasses" />
        @endforeach

        <span class="mx-1 h-7 w-px bg-slate-300/80"></span>

        <div class="flex flex-1 items-center gap-2">
            <div x-data x-init="$nextTick(() => $refs.trainingSearch?.focus())" class="relative mx-auto w-full min-w-64 max-w-md">
                <input x-ref="trainingSearch" type="text" wire:model.live.debounce.300ms="searchTerm"
                    x-on:keydown.escape="$wire.set('searchTerm', '')"
                    placeholder="{{ __('Buscar por igreja, professor, cidade ou estado') }}"
                    class="w-full rounded-xl border border-slate-300 bg-white/95 px-3 py-2 pr-10 text-sm text-slate-900 shadow-xs outline-none transition placeholder:text-slate-400 focus:border-sky-500 focus:ring-2 focus:ring-sky-200" />
                @if (trim($searchTerm) !== '')
                    <button type="button" wire:click="$set('searchTerm', '')"
                        class="absolute inset-y-0 right-2 my-auto inline-flex h-7 w-7 items-center justify-center rounded-md text-slate-500 transition hover:bg-slate-100 hover:text-slate-700"
                        aria-label="{{ __('Limpar busca') }}">
                        <span class="text-base leading-none">&times;</span>
                    </button>
                @endif
            </div>

            @if ($groups->isNotEmpty())
                @foreach ($groups as $group)
                    @php
                        $course = $group['course'];
                        $courseLabel = $course?->initials ?? __('Curso');
                        $courseId = $course?->id ?? 'curso';
                        $courseName = $course?->name ?? $courseLabel;
                    @endphp
                    <x-src.toolbar.course-button :href="'#course-' . $courseId" :label="$courseLabel" :tooltip="$courseName" />
                @endforeach
            @endif
        </div>
    </x-src.toolbar.nav>

    <section
        class="rounded-2xl border border-amber-300/20 bg-linear-to-br from-slate-100 via-white to-slate-200 p-6 shadow-lg">
        <div class="flex items-center justify-between gap-4 border-b-2 border-slate-200/80 pb-1 mb-10">
            <div>
                <h2 class="text-xl font-semibold text-slate-900" style="font-family: 'Cinzel', serif;">
                    {{ collect(value: $statuses)->firstWhere('key', $statusKey)['label'] ?? __('Treinamentos') }}
                </h2>
                <p class="text-sm text-slate-600">
                    {{ __('Treinamentos registrados neste status.') }}
                </p>
            </div>
            <div class="rounded-full bg-amber-100 px-3 py-1 text-sm font-semibold text-amber-800">
                {{ __('Total de eventos:') . ' ' . $groups->sum(fn($group) => $group['items']->count()) }}
            </div>
        </div>

        @if ($groups->isEmpty())
            <div class="rounded-2xl border border-amber-200/60 bg-white p-6 text-sm text-slate-600">
                {{ __('Sem eventos para este status.') }}
            </div>
        @else
            <div class="flex flex-col gap-12">
                @foreach ($groups as $group)
                    @php
                        $course = $group['course'];
                        $courseType = $course?->type ?? __('Treinamento');
                        $courseName = $course?->name ?? __('Curso não informado');
                        $courseId = $course?->id ?? 'curso';
                    @endphp

                    <div id="course-{{ $courseId }}" class="">
                        <h3 class="text-lg text-slate-900 flex gap-1.5 items-center justify-between mb-4 border-b border-slate-200/80 px-2 py-0.5 rounded-lg bg-white"
                            style="font-family: 'Cinzel', serif;">
                            <span>{{ $courseType }}: <span class="font-semibold">{{ $courseName }}</span></span>
                            <span
                                class="ml-2 inline-flex items-center rounded bg-amber-100 px-2.5 py-0.5 text-xs text-amber-800">
                                {{ __('Eventos:') }} {{ $group['items']->count() }}
                            </span>
                        </h3>

                        <x-src.training-carousel :items="$group['items']" role="director" />
                    </div>
                @endforeach
            </div>
        @endif
    </section>
</div>
