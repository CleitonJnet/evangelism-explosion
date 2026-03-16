@props([
    'createRoute' => null,
    'filterMode' => 'basic',
    'filterValue' => null,
    'filters' => [],
    'groups',
    'role',
    'statusKey',
    'statuses',
])

<div>
    @php
        $hexToRgb = static function (?string $hexColor): ?array {
            $color = trim((string) $hexColor);

            if (!preg_match('/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/', $color)) {
                return null;
            }

            $normalized = ltrim($color, '#');

            if (strlen($normalized) === 3) {
                $normalized = collect(str_split($normalized))
                    ->map(fn (string $char): string => $char . $char)
                    ->implode('');
            }

            return [
                hexdec(substr($normalized, 0, 2)),
                hexdec(substr($normalized, 2, 2)),
                hexdec(substr($normalized, 4, 2)),
            ];
        };

        $lightenHexColor = static function (?string $hexColor, float $mixWithWhite = 0.82) use ($hexToRgb): ?string {
            $rgb = $hexToRgb($hexColor);

            if ($rgb === null) {
                return null;
            }

            [$red, $green, $blue] = $rgb;
            $ratio = max(0, min(1, $mixWithWhite));

            $lightenedRed = (int) round($red + ((255 - $red) * $ratio));
            $lightenedGreen = (int) round($green + ((255 - $green) * $ratio));
            $lightenedBlue = (int) round($blue + ((255 - $blue) * $ratio));

            return sprintf('#%02X%02X%02X', $lightenedRed, $lightenedGreen, $lightenedBlue);
        };

        $ministryContainerStyles = static function (?string $hexColor) use ($hexToRgb, $lightenHexColor): string {
            $rgb = $hexToRgb($hexColor);

            if ($rgb === null) {
                return 'background-color: rgb(255 255 255 / 0.6); border-color: rgb(226 232 240 / 0.8);';
            }

            $backgroundColor = $lightenHexColor($hexColor, 0.82) ?? '#FFFFFF';
            $borderColor = $lightenHexColor($hexColor, 0.68) ?? '#E2E8F0';

            return sprintf(
                'background-color: %s; border-color: %s;',
                $backgroundColor,
                $borderColor,
            );
        };

        $currentStatus = collect($statuses)->firstWhere('key', $statusKey);
        $currentStatusTitle = $currentStatus['label'] ?? __('Treinamentos');
        $currentStatusDescription = match ($statusKey) {
            'planning' => __('Eventos em preparacao, com estrutura e agenda ainda em montagem.'),
            'scheduled' => __('Eventos confirmados e prontos para acompanhamento da execucao.'),
            'canceled' => __('Eventos cancelados, mantidos aqui para consulta e historico.'),
            'completed' => __('Eventos concluidos, com foco em acompanhamento e historico.'),
            default => __('Treinamentos organizados neste status.'),
        };
        $activeFilters = [
            'filter' => $filters['filter'] ?? $filterValue,
            'assignment' => $filters['assignment'] ?? 'all',
            'church' => $filters['church'] ?? null,
            'from' => $filters['from'] ?? null,
            'to' => $filters['to'] ?? null,
        ];
        $displayGroups = $groups
            ->map(function ($group) {
                $validCourses = $group['courses']
                    ->filter(fn ($courseGroup) => ($courseGroup['course']?->id) !== null)
                    ->values();

                return [
                    ...$group,
                    'courses' => $validCourses,
                ];
            })
            ->filter(fn ($group) => $group['courses']->isNotEmpty())
            ->values();
        $totalEvents = $displayGroups->sum(fn ($group) => $group['courses']->sum(fn ($courseGroup) => $courseGroup['items']->count()));
        $indexedCourses = $displayGroups
            ->flatMap(fn ($group) => $group['courses'])
            ->map(fn ($courseGroup) => $courseGroup['course'])
            ->filter(fn ($course) => $course !== null && $course->id !== null)
            ->unique('id')
            ->values();
    @endphp

    <x-src.toolbar.header :title="$currentStatusTitle" :description="$currentStatusDescription">
        <x-slot:right>
            <div class="rounded-full bg-amber-100 px-3 py-1 text-sm font-semibold text-amber-800">
                {{ __('Total de eventos:') . ' ' . $totalEvents }}
            </div>
        </x-slot:right>
    </x-src.toolbar.header>

    <x-src.toolbar.nav>
        @if (filled($createRoute))
            <x-src.toolbar.button :href="$createRoute" :label="__('Novo')" icon="plus" :tooltip="__('Novo treinamento')" />
            <span class="mx-1 h-7 w-px shrink-0 bg-slate-300/80"></span>
        @endif

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

            <x-src.toolbar.button :href="$status['route']" :label="$status['label']" :icon="$icon" :active="$isActiveStatus"
                :tooltip="__(':label', ['label' => $status['label']])" :class="$statusButtonClasses" />
        @endforeach

        <span class="mx-1 h-7 w-px shrink-0 bg-slate-300/80"></span>

        <div class="flex flex-1 items-center justify-center">
            <x-src.toolbar.training-filter
                :action="request()->url()"
                :value="$activeFilters['filter']"
                :mode="$filterMode"
                :assignment="$activeFilters['assignment']"
                :church="$activeFilters['church']"
                :from="$activeFilters['from']"
                :to="$activeFilters['to']"
            />
        </div>

        <div class="flex min-w-max items-center justify-end gap-2">
            @if ($indexedCourses->isNotEmpty())
                @foreach ($indexedCourses as $course)
                    @php
                        $courseLabel = $course->initials ?: $course->name;
                        $courseName = $course->name ?: __('Curso nao informado');
                    @endphp

                    <x-src.toolbar.course-button :href="'#course-' . $course->id" :label="$courseLabel" :tooltip="$courseName" />
                @endforeach
            @endif
        </div>
    </x-src.toolbar.nav>

    @if ($displayGroups->isEmpty())
        <div class="rounded-2xl border border-amber-200/60 bg-white p-6 text-sm text-slate-600">
            @if (filled($activeFilters['filter']) || filled($activeFilters['church']) || filled($activeFilters['from']) || filled($activeFilters['to']) || $activeFilters['assignment'] !== 'all')
                {{ __('Nenhum treinamento encontrado para os filtros informados.') }}
            @else
                {{ __('Sem eventos para este status.') }}
            @endif
        </div>
    @else
        <div class="flex flex-col gap-8">
            @foreach ($displayGroups as $group)
                @php
                    $ministry = $group['ministry'];
                    $ministryName = $ministry?->name ?? __('Sem ministerio');
                    $ministryCardStyle = $ministryContainerStyles($ministry?->color);
                @endphp

                <div class="rounded-2xl border p-3 shadow-sm sm:p-4" style="{{ $ministryCardStyle }}">
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
                                $courseName = $course?->name ?? __('Curso nao informado');
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

                                <x-src.training-carousel :items="$courseGroup['items']" :role="$role" />
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
