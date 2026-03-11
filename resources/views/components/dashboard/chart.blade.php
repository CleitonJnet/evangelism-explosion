@props(['chart'])

@php
    $tone = match ($chart['id']) {
        'director-trainings-month' => [
            'card' => 'border-sky-300 bg-[linear-gradient(180deg,rgba(224,242,254,0.72),rgba(255,255,255,1))]',
            'title' => 'text-sky-950',
        ],
        'director-registrations-month' => [
            'card' => 'border-lime-300 bg-[linear-gradient(180deg,rgba(236,252,203,0.7),rgba(255,255,255,1))]',
            'title' => 'text-lime-950',
        ],
        'director-decisions-month' => [
            'card' => 'border-emerald-300 bg-[linear-gradient(180deg,rgba(220,252,231,0.68),rgba(255,255,255,1))]',
            'title' => 'text-emerald-950',
        ],
        'director-new-churches-month' => [
            'card' => 'border-violet-300 bg-[linear-gradient(180deg,rgba(237,233,254,0.7),rgba(255,255,255,1))]',
            'title' => 'text-violet-950',
        ],
        'director-distribution-course' => [
            'card' => 'border-amber-300 bg-[linear-gradient(180deg,rgba(254,243,199,0.66),rgba(255,255,255,1))]',
            'title' => 'text-amber-950',
        ],
        'director-distribution-state' => [
            'card' => 'border-indigo-300 bg-[linear-gradient(180deg,rgba(224,231,255,0.66),rgba(255,255,255,1))]',
            'title' => 'text-indigo-950',
        ],
        'director-ranking-teachers' => [
            'card' => 'border-rose-300 bg-[linear-gradient(180deg,rgba(255,228,230,0.66),rgba(255,255,255,1))]',
            'title' => 'text-rose-950',
        ],
        'director-ranking-churches' => [
            'card' => 'border-cyan-300 bg-[linear-gradient(180deg,rgba(207,250,254,0.66),rgba(255,255,255,1))]',
            'title' => 'text-cyan-950',
        ],
        'teacher-registrations-line' => [
            'card' => 'border-sky-300 bg-[linear-gradient(180deg,rgba(224,242,254,0.68),rgba(255,255,255,1))]',
            'title' => 'text-sky-950',
        ],
        'teacher-trainings-status' => [
            'card' => 'border-lime-300 bg-[linear-gradient(180deg,rgba(236,252,203,0.68),rgba(255,255,255,1))]',
            'title' => 'text-lime-950',
        ],
        'teacher-financial-status' => [
            'card' => 'border-amber-300 bg-[linear-gradient(180deg,rgba(254,243,199,0.64),rgba(255,255,255,1))]',
            'title' => 'text-amber-950',
        ],
        'teacher-stp-results' => [
            'card' => 'border-orange-300 bg-[linear-gradient(180deg,rgba(255,237,213,0.64),rgba(255,255,255,1))]',
            'title' => 'text-orange-950',
        ],
        'teacher-discipleship-results' => [
            'card' => 'border-cyan-300 bg-[linear-gradient(180deg,rgba(207,250,254,0.64),rgba(255,255,255,1))]',
            'title' => 'text-cyan-950',
        ],
        'teacher-church-ranking' => [
            'card' => 'border-violet-300 bg-[linear-gradient(180deg,rgba(237,233,254,0.68),rgba(255,255,255,1))]',
            'title' => 'text-violet-950',
        ],
        default => [
            'card' => 'border-slate-200 bg-white',
            'title' => 'text-sky-950',
        ],
    };
@endphp

<article class="h-full rounded-2xl border p-5 shadow-sm {{ $tone['card'] }}">
    <h2 class="mb-4 text-lg font-semibold {{ $tone['title'] }}">{{ $chart['title'] }}</h2>

    <div data-dashboard-chart data-chart-id="{{ $chart['id'] }}"
        data-chart-signature="{{ md5(json_encode($chart, JSON_THROW_ON_ERROR)) }}" class="relative"
        style="height: {{ $chart['height'] ?? 320 }}px;">
        <canvas data-dashboard-chart-canvas aria-label="{{ $chart['title'] }}" role="img"></canvas>
        <script type="application/json" data-dashboard-chart-payload>@json($chart)</script>
    </div>
</article>
