@props([
    'course_name' => null,
    'course_type' => null,
    'start_time' => null,
    'end_time' => null,
    'date' => null,
    'banner' => null,
    'route' => null,
    'label' => 'inscrever-se',
])

{{-- CTA fixo (sempre disponível) --}}
<div class="fixed inset-x-6 z-50 max-w-5xl mx-auto bottom-2">
    <div
        class="flex items-center gap-3 p-3 border shadow-lg md:justify-between rounded-2xl bg-sky-950/90 backdrop-blur-md border-amber-300/20 ring-1 ring-white/10">
        <div class="items-center hidden min-w-0 gap-3 md:flex">
            <img src="{{ asset('images/logo/ee-gold.webp') }}" class="w-8 h-8" alt="EE">
            <div class="min-w-0">
                <p class="text-sm font-semibold truncate text-amber-200">{{ $course_type }} •
                    {{ $course_name }}
                </p>
                <p class="text-xs truncate text-white/80">
                    Começa dia {{ date('d/m', strtotime($date)) }} às
                    {{ date('H', strtotime($start_time)) }}h • Horário de Brasília
                </p>
            </div>
        </div>

        <div class="flex items-center gap-4">
            {{-- Countdown --}}
            <img src="{{ asset('images/logo/ee-gold.webp') }}" class="w-8 h-8 md:hidden" alt="EE">

            @php
                $datetime_start =
                    date('Y-m-d', strtotime($date)) . 'T' . date('H:i:s', strtotime($start_time)) . '-03:00';
                $datetime_end = date('Y-m-d', strtotime($date)) . 'T' . date('H:i:s', strtotime($end_time)) . '-03:00';
            @endphp
            {{-- COUNTDOWN + STATUS DO EVENTO --}}
            <div class="max-w-sm" data-countdown>
                {{-- 1) Antes do início: contador --}}
                <div data-countdown-timer class="grid grid-cols-4 gap-1">
                    <div class="p-0.5 text-center rounded-s-md bg-white/10 ring-1 ring-white/10">
                        <div class="text-sm font-bold text-white" data-dd>--</div>
                        <div class="text-[9px] text-white/80">Dias</div>
                    </div>

                    <div class="p-0.5 text-center bg-white/10 ring-1 ring-white/10">
                        <div class="text-sm font-bold text-white" data-hh>--</div>
                        <div class="text-[9px] text-white/80">Horas</div>
                    </div>

                    <div class="p-0.5 text-center bg-white/10 ring-1 ring-white/10">
                        <div class="text-sm font-bold text-white" data-mm>--</div>
                        <div class="text-[9px] text-white/80">Min</div>
                    </div>

                    <div class="p-0.5 text-center rounded-e-md bg-white/10 ring-1 ring-white/10">
                        <div class="text-sm font-bold text-white" data-ss>--</div>
                        <div class="text-[9px] text-white/80">Seg</div>
                    </div>
                </div>

                {{-- 2) Durante o evento: status + tempo até encerrar --}}
                <div data-countdown-live
                    class="hidden px-2 py-0.5 text-center rounded-lg bg-amber-400/15 ring-1 ring-amber-300/30">
                    <div class="text-sm font-semibold text-amber-200" style="text-shadow: 1px 1px 1px black;">
                        Evento em andamento
                    </div>

                    <div class="text-sm text-white/85" style="text-shadow: 1px 1px 1px black;">
                        Encerra em:
                        <span class="font-semibold text-white" data-live-remaining>--:--:--:--</span>
                    </div>
                </div>

                {{-- 3) Após o término --}}
                <div data-countdown-ended
                    class="hidden px-5 py-2 text-center rounded-2xl bg-white/10 ring-1 ring-white/10">
                    <div class="text-base font-semibold text-white/90" style="text-shadow: 1px 1px 1px black;">
                        Evento encerrado
                    </div>
                </div>
            </div>
        </div>

        <div id="button-fixed-subscribe" class="flex items-center gap-2 shrink-0">
            @if (filled($banner))
                <a href="{{ $banner }}" download
                    class="items-center justify-center hidden px-4 py-2 text-sm font-semibold text-white transition border shine sm:inline-flex rounded-xl border-white/10 bg-white/10 hover:bg-white/15 focus:outline-none focus:ring-2 focus:ring-amber-300/50">
                    Baixar cartaz
                </a>
            @endif
            @if ($route !== null)
                <x-src.btn-gold :label="$label" class="py-0" :route="$route" class="py-2!" />
            @endif
        </div>

        <script>
            (() => {
                const boxes = document.querySelectorAll('[data-countdown]');
                const btn_subscribe = document.getElementById('button-fixed-subscribe');


                if (!boxes.length) return;

                const pad2 = (n) => String(n).padStart(2, '0');

                // Formato: DD:HH:MM:SS (dias sem pad para não ficar 0003 dias, por exemplo)
                const formatDHMS = (ms) => {
                    ms = Math.max(0, ms);

                    const days = Math.floor(ms / (1000 * 60 * 60 * 24));
                    const hours = Math.floor((ms / (1000 * 60 * 60)) % 24);
                    const mins = Math.floor((ms / (1000 * 60)) % 60);
                    const secs = Math.floor((ms / 1000) % 60);

                    return `${days}:${pad2(hours)}:${pad2(mins)}:${pad2(secs)}`;
                };

                const showOnly = (box, which) => {
                    const timerEl = box.querySelector('[data-countdown-timer]');
                    const liveEl = box.querySelector('[data-countdown-live]');
                    const endEl = box.querySelector('[data-countdown-ended]');

                    if (timerEl) timerEl.classList.toggle('hidden', which !== 'timer');
                    if (liveEl) liveEl.classList.toggle('hidden', which !== 'live');
                    if (endEl) endEl.classList.toggle('hidden', which !== 'ended');
                };

                boxes.forEach((box) => {
                    const startIso = @json($datetime_start);
                    const endIso = @json($datetime_end);
                    if (!startIso || !endIso) return;

                    const startDate = new Date(startIso);
                    const endDate = new Date(endIso);

                    if (isNaN(startDate.getTime()) || isNaN(endDate.getTime())) return;

                    // Se o término estiver antes/igual ao início, encerramos por segurança.
                    if (endDate <= startDate) {
                        showOnly(box, 'ended');
                        return;
                    }

                    const dd = box.querySelector('[data-dd]');
                    const hh = box.querySelector('[data-hh]');
                    const mm = box.querySelector('[data-mm]');
                    const ss = box.querySelector('[data-ss]');
                    const liveRemaining = box.querySelector('[data-live-remaining]');

                    function renderCountdown(diffMs) {
                        diffMs = Math.max(0, diffMs);

                        const days = Math.floor(diffMs / (1000 * 60 * 60 * 24));
                        const hours = Math.floor((diffMs / (1000 * 60 * 60)) % 24);
                        const mins = Math.floor((diffMs / (1000 * 60)) % 60);
                        const secs = Math.floor((diffMs / 1000) % 60);

                        if (dd) dd.textContent = days;
                        if (hh) hh.textContent = pad2(hours);
                        if (mm) mm.textContent = pad2(mins);
                        if (ss) ss.textContent = pad2(secs);
                    }

                    function tick() {
                        const now = new Date();

                        // 3) Depois do fim
                        if (now >= endDate) {
                            // Opcional: manter números coerentes (zera)
                            renderCountdown(0);

                            if (liveRemaining) liveRemaining.textContent = '0:00:00:00';
                            showOnly(box, 'ended');

                            // 👉 Esconde o botão ao finalizar o countdown
                            // const btn = document.getElementById('btn_subscribe');
                            if (btn_subscribe) {
                                btn_subscribe.classList.add(
                                    'hidden'); // ou 'display-none', conforme seu CSS
                            }

                            return true; // pode parar o interval
                        }

                        // 2) Durante o evento
                        if (now >= startDate && now < endDate) {
                            const remaining = endDate - now;
                            if (liveRemaining) liveRemaining.textContent = formatDHMS(remaining);
                            showOnly(box, 'live');
                            return false;
                        }

                        // 1) Antes do início (contagem regressiva)
                        const diff = startDate - now;
                        renderCountdown(diff);
                        showOnly(box, 'timer');
                        return false;
                    }

                    // Atualiza imediatamente
                    const done = tick();
                    if (done) return;

                    // Atualiza a cada segundo e encerra quando terminar
                    const id = setInterval(() => {
                        const finished = tick();
                        if (finished) clearInterval(id);
                    }, 1000);
                });
            })();
        </script>

    </div>
</div>
