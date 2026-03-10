@php
    use Illuminate\Support\Facades\Storage;
@endphp

<div class="space-y-6">
    <section
        class="rounded-2xl border border-amber-300/20 bg-linear-to-br from-slate-100 via-white to-slate-200 p-4 shadow-lg sm:p-6">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3 border-b-2 border-slate-200/80 pb-3">
            <div class="flex flex-col gap-1">
                <h2 class="text-xl font-semibold text-slate-900" style="font-family: 'Cinzel', serif;">
                    {{ __('Ministérios cadastrados') }}
                </h2>
                <p class="text-sm text-slate-600">
                    {{ __('Clique em um ministério para abrir os detalhes e cursos relacionados.') }}
                </p>
            </div>
            <div class="rounded-full bg-amber-100 px-3 py-1 text-sm font-semibold text-amber-800">
                {{ __('Total listado: :count', ['count' => $ministries->total()]) }}
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($ministries as $ministry)
                @php
                    $logoValue = trim((string) $ministry->logo);
                    $logoUrl = $logoValue;
                    $leadershipCourses = $ministry->courses->where('execution', 0)->values();
                    $localImplementationCourses = $ministry->courses->where('execution', 1)->values();
                    $themeColor = trim((string) $ministry->color);
                    $normalizedThemeColor = preg_match('/^#?[0-9a-fA-F]{6}$/', $themeColor) === 1
                        ? '#'.ltrim($themeColor, '#')
                        : null;
                    $rgb = $normalizedThemeColor
                        ? sscanf($normalizedThemeColor, '#%02x%02x%02x')
                        : null;
                    $rgbString = $rgb
                        ? implode(', ', [(int) $rgb[0], (int) $rgb[1], (int) $rgb[2]])
                        : null;
                    $cardBackground = $rgb
                        ? 'linear-gradient(135deg, rgba('.$rgbString.', 0.12), rgba(255, 255, 255, 0.96) 42%, rgba('.$rgbString.', 0.18))'
                        : null;
                    $cardHoverBackground = $rgb
                        ? 'linear-gradient(135deg, rgba('.$rgbString.', 0.18), rgba(255, 255, 255, 0.98) 42%, rgba('.$rgbString.', 0.24))'
                        : null;
                    $cardBorderColor = $normalizedThemeColor ?: '#cbd5e1';
                    $initialsBackgroundColor = $normalizedThemeColor ?: '#e2e8f0';

                    if ($logoValue !== '' && !str_starts_with($logoValue, 'http')) {
                        $normalizedLogo = ltrim($logoValue, '/');
                        $logoUrl = Storage::disk('public')->exists($normalizedLogo)
                            ? Storage::disk('public')->url($normalizedLogo)
                            : null;
                    }

                    if ($logoValue === '') {
                        $logoUrl = null;
                    }
                @endphp

                <a href="{{ route('app.director.ministry.show', $ministry) }}"
                    wire:key="ministry-card-{{ $ministry->id }}"
                    class="flex flex-col gap-4 rounded-2xl border-2 p-4 text-left shadow-xs transition"
                    style="border-color: {{ $cardBorderColor }}; background: {{ $cardBackground ?: 'rgba(255, 255, 255, 0.95)' }};"
                    onmouseover="this.style.background='{{ $cardHoverBackground ?: 'rgba(241, 245, 249, 0.95)' }}'"
                    onmouseout="this.style.background='{{ $cardBackground ?: 'rgba(255, 255, 255, 0.95)' }}'">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3">
                            @if ($logoUrl)
                                <img src="{{ $logoUrl }}" alt="{{ __('Logo do ministério') }}"
                                    class="h-12 w-12 rounded-xl border border-slate-200 bg-white object-cover">
                            @else
                                <div
                                    class="inline-flex h-12 w-12 items-center justify-center rounded-xl text-xs font-bold uppercase text-white"
                                    style="background-color: {{ $initialsBackgroundColor }};">
                                    {{ str($ministry->initials)->limit(3, '') ?: 'MIN' }}
                                </div>
                            @endif
                            <div>
                                <div class="text-lg font-bold tracking-tight text-slate-950 sm:text-xl">{{ $ministry->name }}</div>
                                <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    {{ $ministry->initials ?: __('Sem sigla') }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <p class="line-clamp-2 text-sm text-slate-600">
                        {{ $ministry->description ?: __('Sem descrição cadastrada.') }}
                    </p>

                    <div class="flex flex-wrap gap-2 text-xs font-semibold">
                        <span class="rounded-full bg-amber-100 px-2.5 py-1 text-amber-800">
                            {{ __('Cursos: :count', ['count' => $ministry->courses_count]) }}
                        </span>
                        <span class="rounded-full bg-slate-200 px-2.5 py-1 text-slate-700">
                            {{ __('Liderança: :count', ['count' => $ministry->launcher_courses_count]) }}
                        </span>
                        <span class="rounded-full bg-sky-100 px-2.5 py-1 text-sky-800">
                            {{ __('Implementação local: :count', ['count' => $ministry->implementation_courses_count]) }}
                        </span>
                    </div>

                    <div class="grid gap-3 text-xs sm:grid-cols-2">
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                            <div class="mb-1 font-semibold uppercase tracking-wide text-slate-600">
                                {{ __('Curso de Liderança') }}
                            </div>
                            @if ($leadershipCourses->isEmpty())
                                <div class="text-slate-500">{{ __('Nenhum curso cadastrado.') }}</div>
                            @else
                                <ul class="space-y-1 text-slate-700">
                                    @foreach ($leadershipCourses as $course)
                                        <li>{{ $course->name }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>

                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                            <div class="mb-1 font-semibold uppercase tracking-wide text-slate-600">
                                {{ __('Implementação Local') }}
                            </div>
                            @if ($localImplementationCourses->isEmpty())
                                <div class="text-slate-500">{{ __('Nenhum curso cadastrado.') }}</div>
                            @else
                                <ul class="space-y-1 text-slate-700">
                                    @foreach ($localImplementationCourses as $course)
                                        <li>{{ $course->name }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                </a>
            @empty
                <div
                    class="rounded-2xl border border-amber-200/60 bg-white px-4 py-6 text-sm text-slate-600 sm:col-span-2 lg:col-span-3">
                    {{ __('Nenhum ministério cadastrado.') }}
                </div>
            @endforelse
        </div>

        @if ($ministries->hasPages())
            <div class="mt-5">
                {{ $ministries->links(data: ['scrollTo' => false]) }}
            </div>
        @endif
    </section>
</div>
