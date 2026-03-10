<div>
    <x-src.toolbar.header :title="__('Gerenciamento de Treinamentos e Eventos')" :description="__('Controle os treinamentos do Evangelismo Explosivo, organizando status e cursos em um só lugar.')" />
    <x-src.toolbar.nav>
        <x-src.toolbar.button :href="route('app.teacher.trainings.create')" :label="__('Novo')" icon="plus" :tooltip="__('Novo treinamento')" />

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

        <div class="flex flex-1 items-center justify-end gap-2">
            @if ($groups->isNotEmpty())
                @foreach ($groups as $group)
                    @foreach ($group['courses'] as $courseGroup)
                        @php
                            $course = $courseGroup['course'];
                            $courseLabel = $course?->initials ?? __('Curso');
                            $courseId = $course?->id ?? 'curso';
                            $courseName = $course?->name ?? $courseLabel;
                        @endphp
                        <x-src.toolbar.course-button :href="'#course-' . $courseId" :label="$courseLabel" :tooltip="$courseName" />
                    @endforeach
                @endforeach
            @endif
        </div>
    </x-src.toolbar.nav>

    <section
        class="rounded-2xl border border-amber-300/20 bg-linear-to-br from-slate-100 via-white to-slate-200 p-4 shadow-lg sm:p-5 md:p-6">
        <div class="mb-8 flex items-center justify-between gap-4 border-b-2 border-slate-200/80 pb-1">
            <div>
                <h2 class="text-xl font-semibold text-slate-900" style="font-family: 'Cinzel', serif;">
                    {{ collect(value: $statuses)->firstWhere('key', $statusKey)['label'] ?? __('Treinamentos') }}
                </h2>
                <p class="text-sm text-slate-600">
                    {{ __('Treinamentos registrados neste status.') }}
                </p>
            </div>
            <div class="rounded-full bg-amber-100 px-3 py-1 text-sm font-semibold text-amber-800">
                {{ __('Total de eventos:') . ' ' . $groups->sum(fn($group) => $group['courses']->sum(fn($courseGroup) => $courseGroup['items']->count())) }}
            </div>
        </div>

        @if ($groups->isEmpty())
            <div class="rounded-2xl border border-amber-200/60 bg-white p-6 text-sm text-slate-600">
                {{ __('Sem eventos para este status.') }}
            </div>
        @else
            <div class="flex flex-col gap-8">
                @foreach ($groups as $group)
                    @php
                        $ministry = $group['ministry'];
                        $ministryName = $ministry?->name ?? __('Sem ministério');
                    @endphp

                    <div class="rounded-2xl border border-slate-200/80 bg-white/60 p-3 shadow-sm sm:p-4">
                        <div class="mb-4 flex items-center justify-between gap-3 border-b border-slate-200/80 pb-3">
                            <h3 class="text-lg font-semibold text-slate-900" style="font-family: 'Cinzel', serif;">
                                {{ $ministryName }}
                            </h3>
                            <span class="inline-flex items-center rounded bg-slate-100 px-2.5 py-0.5 text-xs text-slate-700">
                                {{ __('Cursos:') }} {{ $group['courses']->count() }}
                            </span>
                        </div>

                        <div class="flex flex-col gap-6">
                            @foreach ($group['courses'] as $courseGroup)
                                @php
                                    $course = $courseGroup['course'];
                                    $courseType = $course?->type ?? __('Treinamento');
                                    $courseName = $course?->name ?? __('Curso não informado');
                                    $courseId = $course?->id ?? 'curso';
                                @endphp

                                <div id="course-{{ $courseId }}" class="rounded-xl border border-slate-200/70 bg-white/70 p-3 sm:p-4">
                                    <h4 class="mb-3 flex items-center justify-between gap-1.5 border-b border-slate-200/80 pb-2 text-lg text-slate-900"
                                        style="font-family: 'Cinzel', serif;">
                                        <span>{{ $courseType }}: <span class="font-semibold">{{ $courseName }}</span></span>
                                        <span class="ml-2 inline-flex items-center rounded bg-amber-100 px-2.5 py-0.5 text-xs text-amber-800">
                                            {{ __('Eventos:') }} {{ $courseGroup['items']->count() }}
                                        </span>
                                    </h4>

                                    <x-src.training-carousel :items="$courseGroup['items']" role="teacher" />
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>
</div>
