@php
    use App\Helpers\MoneyHelper;
    use Illuminate\Support\Facades\Storage;

    $resolveCourseLogoUrl = static function ($course): ?string {
        $logoValue = trim((string) $course->logo);

        if ($logoValue === '') {
            return null;
        }

        if (str_starts_with($logoValue, 'http')) {
            return $logoValue;
        }

        $normalizedLogo = ltrim($logoValue, '/');

        return Storage::disk('public')->exists($normalizedLogo) ? Storage::disk('public')->url($normalizedLogo) : null;
    };

    $courseBadgeTextColor = static function (?string $hexColor): string {
        $color = trim((string) $hexColor);

        if (!preg_match('/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/', $color)) {
            return '#0f172a';
        }

        $normalized = ltrim($color, '#');

        if (strlen($normalized) === 3) {
            $normalized = collect(str_split($normalized))->map(fn(string $char): string => $char . $char)->implode('');
        }

        $red = hexdec(substr($normalized, 0, 2));
        $green = hexdec(substr($normalized, 2, 2));
        $blue = hexdec(substr($normalized, 4, 2));
        $luminance = ($red * 299 + $green * 587 + $blue * 114) / 1000;

        return $luminance >= 160 ? '#0f172a' : '#f8fafc';
    };
@endphp

<div class="space-y-6">
    <section
        class="rounded-2xl border border-amber-300/20 bg-linear-to-br from-slate-100 via-white to-slate-200 p-4 shadow-lg sm:p-6">
        <div class="flex flex-wrap items-start justify-between gap-4 border-b-2 border-slate-200/80 pb-4">
            <div class="flex items-start gap-4">
                @if ($logoUrl)
                    <img src="{{ $logoUrl }}" alt="{{ __('Logo do ministério') }}"
                        class="h-20 w-20 rounded-2xl border border-slate-200 bg-white object-cover shadow-sm">
                @else
                    <div class="inline-flex h-20 w-20 items-center justify-center rounded-2xl text-lg font-bold shadow-sm"
                        style="background-color: {{ $ministry->color ?: '#e2e8f0' }}; color: {{ $courseBadgeTextColor($ministry->color) }}">
                        {{ str($ministry->initials)->limit(4, '') ?: 'MIN' }}
                    </div>
                @endif

                <div class="space-y-2 pt-1">
                    <h2 class="text-2xl font-semibold text-slate-900" style="font-family: 'Cinzel', serif;">
                        {{ $ministry->name }} - {{ $ministry->initials ?: __('Sem sigla cadastrada') }}
                    </h2>

                    <p class="max-w-3xl text-sm leading-6 text-slate-600">
                        {{ $ministry->description ?: __('Este ministério ainda não possui uma descrição cadastrada.') }}
                    </p>
                </div>
            </div>

            <div
                class="flex items-center gap-3 rounded-full bg-white/80 px-4 py-2 text-sm font-semibold text-slate-800">
                <span class="h-3.5 w-3.5 rounded-full border border-slate-300"
                    style="background-color: {{ $ministry->color ?: '#e2e8f0' }}"></span>
                <span>{{ __('Cor temática: :color', ['color' => $ministry->color ?: __('Não informada')]) }}</span>
            </div>
        </div>
    </section>

    <div class="mt-4 flex flex-wrap gap-3">
        <div class="min-w-40 flex-1 rounded-xl border border-slate-200 bg-white p-4">
            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                {{ __('Cursos de liderança') }}
            </div>
            <div class="mt-2 text-2xl font-bold text-slate-900">{{ $leadershipCourses->count() }}</div>
        </div>

        <div class="min-w-40 flex-1 rounded-xl border border-slate-200 bg-white p-4">
            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                {{ __('Cursos de implementação') }}
            </div>
            <div class="mt-2 text-2xl font-bold text-slate-900">{{ $implementationCourses->count() }}</div>
        </div>

        <div class="min-w-40 flex-1 rounded-xl border border-slate-200 bg-white p-4">
            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                {{ __('Professores vinculados') }}
            </div>
            <div class="mt-2 text-2xl font-bold text-slate-900">{{ $teachersCount }}</div>
        </div>

        <div class="min-w-40 flex-1 rounded-xl border border-slate-200 bg-white p-4">
            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                {{ __('Facilitadores credenciados') }}
            </div>
            <div class="mt-2 text-2xl font-bold text-slate-900">{{ $implementationFacilitatorsCount }}</div>
        </div>
    </div>

    <section class="rounded-2xl border border-slate-200 bg-white/95 p-4 shadow-sm sm:p-5">
        <div class="flex items-start justify-between gap-4 border-b border-slate-200 pb-3">
            <div>
                <h3 class="text-lg font-semibold text-slate-900">{{ __('Cursos do ministério') }}</h3>
                <p class="text-sm text-slate-600">
                    {{ __('Consulte abaixo os cursos de liderança e implementação vinculados ao ministério selecionado.') }}
                </p>
            </div>
            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                {{ __(':count no total', ['count' => $coursesCount]) }}
            </span>
        </div>

        <div class="mt-6 space-y-6">
            <section>
                <div class="mt-4 overflow-x-auto">
                    <table class="w-full min-w-6xl text-left text-sm">
                        <colgroup>
                            <col class="w-18">
                            <col class="w-18">
                            <col>
                            <col class="w-20">
                            <col class="w-32">
                            <col class="w-24">
                            <col class="w-24">
                            <col class="w-32">
                        </colgroup>
                        <thead class="text-xs uppercase text-slate-50 bg-amber-700">
                            <tr class="border-b border-slate-200">
                                <th class="px-3 py-4">{{ __('Ordem') }}</th>
                                <th class="px-3 py-4 text-center">{{ __('Logo') }}</th>
                                <th class="px-3 py-4">{{ __('Curso para liderança') }}</th>
                                <th class="px-3 py-4 text-center">{{ __('Sigla') }}</th>
                                <th class="px-3 py-4 text-center">{{ __('Tipo') }}</th>
                                <th class="px-3 py-4 text-center">{{ __('Professores') }}</th>
                                <th class="px-3 py-4 text-center">{{ __('Seções') }}</th>
                                <th class="px-3 py-4 text-right">{{ __('Preço') }}</th>
                            </tr>
                        </thead>
                        <tbody class="js-ministry-course-list divide-y divide-slate-200" data-execution="0">
                            @forelse ($leadershipCourses as $course)
                                @php
                                    $courseLogoUrl = $resolveCourseLogoUrl($course);
                                @endphp
                                <tr wire:key="leadership-course-{{ $course->id }}"
                                    data-item-id="{{ $course->id }}"
                                    class="js-ministry-course-item cursor-pointer odd:bg-amber-50/50 even:bg-amber-50 hover:bg-amber-100/50"
                                    x-on:click="if (! $event.target.closest('.js-course-drag-handle')) { window.location = $el.dataset.rowLink }"
                                    data-row-link="{{ route('app.director.ministry.course.show', ['ministry' => $ministry->id, 'course' => $course->id]) }}">
                                    <td
                                        class="js-course-drag-handle cursor-grab px-3 py-3 text-slate-700 active:cursor-grabbing">
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs text-slate-400">::</span>
                                            <span>{{ $course->order ?: '-' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-3 py-3">
                                        <div class="flex justify-center">
                                            @if ($courseLogoUrl)
                                                <img src="{{ $courseLogoUrl }}" alt="{{ __('Logo do curso') }}"
                                                    class="h-10 w-10 rounded-lg backdrop-blur object-container">
                                            @else
                                                <div class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 text-[10px] font-bold"
                                                    style="background-color: {{ $course->color ?: '#e2e8f0' }}; color: {{ $courseBadgeTextColor($course->color) }}">
                                                    {{ str($course->initials)->limit(3, '') ?: 'CUR' }}
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-3 py-3">
                                        <div class="font-semibold text-slate-900">{{ $course->name }}</div>
                                        <div class="text-xs text-slate-500">
                                            {{ $course->targetAudience ?: __('Público não informado') }}
                                        </div>
                                    </td>
                                    <td class="px-3 py-3 text-center text-slate-700">
                                        <span
                                            class="inline-flex min-w-14 items-center justify-center rounded-full px-2.5 py-1 text-xs font-semibold"
                                            style="background-color: {{ $course->color ?: '#e2e8f0' }}; color: {{ $courseBadgeTextColor($course->color) }}">
                                            {{ $course->initials ?: __('-') }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-3 text-center text-slate-700">
                                        {{ $course->type ?: __('Não informado') }}
                                    </td>
                                    <td class="px-3 py-3 text-center text-slate-700">
                                        {{ $course->teachers->count() }}
                                    </td>
                                    <td class="px-3 py-3 text-center text-slate-700">
                                        {{ $course->sections->count() }}
                                    </td>
                                    <td class="px-3 py-3 text-right text-slate-700">
                                        {{ MoneyHelper::format_money($course->price) ?: __('Não informado') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-3 py-4 text-center text-slate-600">
                                        {{ __('Nenhum curso de liderança cadastrado para este ministério.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section>
                <div class="mt-4 overflow-x-auto">
                    <table class="w-full min-w-6xl text-left text-sm">
                        <colgroup>
                            <col class="w-18">
                            <col class="w-18">
                            <col>
                            <col class="w-20">
                            <col class="w-32">
                            <col class="w-24">
                            <col class="w-24">
                            <col class="w-32">
                        </colgroup>
                        <thead class="text-xs uppercase text-slate-50 bg-blue-700">
                            <tr class="border-b border-slate-200">
                                <th class="px-3 py-4">{{ __('Ordem') }}</th>
                                <th class="px-3 py-4 text-center">{{ __('Logo') }}</th>
                                <th class="px-3 py-4">{{ __('Curso para Implementação local') }}</th>
                                <th class="px-3 py-4 text-center">{{ __('Sigla') }}</th>
                                <th class="px-3 py-4 text-center">{{ __('Tipo') }}</th>
                                <th class="px-3 py-4 text-center">{{ __('Professores') }}</th>
                                <th class="px-3 py-4 text-center">{{ __('Seções') }}</th>
                                <th class="px-3 py-4 text-right">{{ __('Preço') }}</th>
                            </tr>
                        </thead>
                        <tbody class="js-ministry-course-list divide-y divide-slate-200" data-execution="1">
                            @forelse ($implementationCourses as $course)
                                @php
                                    $courseLogoUrl = $resolveCourseLogoUrl($course);
                                @endphp
                                <tr wire:key="implementation-course-{{ $course->id }}"
                                    data-item-id="{{ $course->id }}"
                                    class="js-ministry-course-item cursor-pointer odd:bg-blue-50/50 even:bg-blue-50 hover:bg-blue-100/50"
                                    data-row-link="{{ route('app.director.ministry.course.show', ['ministry' => $ministry->id, 'course' => $course->id]) }}"
                                    x-on:click="if (! $event.target.closest('.js-course-drag-handle')) { window.location = $el.dataset.rowLink }">
                                    <td
                                        class="js-course-drag-handle cursor-grab px-3 py-3 text-slate-700 active:cursor-grabbing">
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs text-slate-400">::</span>
                                            <span>{{ $course->order ?: '-' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-3 py-3">
                                        <div class="flex justify-center">
                                            @if ($courseLogoUrl)
                                                <img src="{{ $courseLogoUrl }}" alt="{{ __('Logo do curso') }}"
                                                    class="h-10 w-10 rounded-lg lg backdrop-blur object-contain">
                                            @else
                                                <div class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 text-[10px] font-bold"
                                                    style="background-color: {{ $course->color ?: '#e2e8f0' }}; color: {{ $courseBadgeTextColor($course->color) }}">
                                                    {{ str($course->initials)->limit(3, '') ?: 'CUR' }}
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-3 py-3">
                                        <div class="font-semibold text-slate-900">{{ $course->name }}</div>
                                        <div class="text-xs text-slate-500">
                                            {{ $course->slogan ?: __('Sem slogan cadastrado') }}
                                        </div>
                                    </td>
                                    <td class="px-3 py-3 text-center text-slate-700">
                                        <span
                                            class="inline-flex min-w-14 items-center justify-center rounded-full px-2.5 py-1 text-xs font-semibold"
                                            style="background-color: {{ $course->color ?: '#e2e8f0' }}; color: {{ $courseBadgeTextColor($course->color) }}">
                                            {{ $course->initials ?: __('-') }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-3 text-center text-slate-700">
                                        {{ $course->type ?: __('Não informado') }}
                                    </td>
                                    <td class="px-3 py-3 text-center text-slate-700">
                                        {{ $course->teachers->count() }}
                                    </td>
                                    <td class="px-3 py-3 text-center text-slate-700">
                                        {{ $course->sections->count() }}
                                    </td>
                                    <td class="px-3 py-3 text-right text-slate-700">
                                        {{ MoneyHelper::format_money($course->price) ?: __('Não informado') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-3 py-4 text-center text-slate-600">
                                        {{ __('Nenhum curso de implementação cadastrado para este ministério.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </section>
</div>
