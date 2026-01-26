<div class="flex flex-col gap-8">
    @teleport('#app-toolbar')
        <div class="flex w-full flex-wrap items-center justify-between gap-4">
            <div class="flex flex-wrap items-center gap-2 text-sm text-slate-700">
                <x-src.toolbar.button :href="route('app.teacher.training.create')" :label="__('Novo')" icon="plus" :tooltip="__('Novo treinamento')" />

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
                    @endphp
                    <x-src.toolbar.button :href="$status['route']" :label="$status['label']" :icon="$icon" :active="$statusKey === $status['key']"
                        :tooltip="__(':label', ['label' => $status['label']])" />
                @endforeach

            </div>

            @if ($groups->isNotEmpty())
                <div class="flex flex-wrap items-center gap-2 text-sm text-slate-700">
                    @foreach ($groups as $group)
                        @php
                            $course = $group['course'];
                            $courseLabel = $course?->initials ?? __('Curso');
                            $courseId = $course?->id ?? 'curso';
                            $courseName = $course?->name ?? $courseLabel;
                        @endphp
                        <x-src.toolbar.course-button :href="'#course-' . $courseId" :label="$courseLabel" :tooltip="$courseName" />
                    @endforeach
                </div>
            @endif
        </div>
    @endteleport

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

                        <x-src.training-carousel :items="$group['items']" role="teacher" />
                    </div>
                @endforeach
            </div>
        @endif
    </section>
</div>
