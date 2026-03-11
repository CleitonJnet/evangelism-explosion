@props(['chart'])

<article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
    <h2 class="mb-4 text-lg font-semibold text-sky-950">{{ $chart['title'] }}</h2>

    <div data-dashboard-chart data-chart-id="{{ $chart['id'] }}"
        data-chart-signature="{{ md5(json_encode($chart, JSON_THROW_ON_ERROR)) }}" class="relative"
        style="height: {{ $chart['height'] ?? 320 }}px;">
        <canvas data-dashboard-chart-canvas aria-label="{{ $chart['title'] }}" role="img"></canvas>
        <script type="application/json" data-dashboard-chart-payload>@json($chart)</script>
    </div>
</article>
