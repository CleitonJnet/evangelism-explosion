<?php
    $title = __('Eventos & treinamentos');
    $description =
        'Confira ou agende eventos e treinamentos do ministério Evangelismo Explosivo no Brasil, participando da expansão do Evangelho.';
    $keywords = 'agenda, eventos, treinamentos, evangelismo, EE Brasil';
    $ogImage = asset('images/leadership-meeting.webp');
    $churchAddress = implode(', ', array_filter([
        $event->church->street ?? null,
        $event->church->number ?? null,
        $event->church->district ?? null,
        $event->church->city ?? null,
        $event->church->state ?? null,
    ]));
?>

<?php if (isset($component)) { $__componentOriginal7ddf49af801524849d67e38f92bf39c7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7ddf49af801524849d67e38f92bf39c7 = $attributes; } ?>
<?php $component = App\View\Components\Layouts\Guest::resolve(['title' => $title,'description' => $description,'keywords' => $keywords,'ogImage' => $ogImage] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.guest'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\Layouts\Guest::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>

    <?php if (isset($component)) { $__componentOriginal3c0808d0ccb9dfd11d29783234199f91 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c0808d0ccb9dfd11d29783234199f91 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.web.header','data' => ['title' => '<div>' . $event->course->type . ': </div><div class=`text-nowrap>' . $event->course->name . '</div>','subtitle' => 'Mais detalhes sobre o evento','cover' => asset('images/leadership-meeting.webp')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('web.header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('<div>' . $event->course->type . ': </div><div class=`text-nowrap>' . $event->course->name . '</div>'),'subtitle' => 'Mais detalhes sobre o evento','cover' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(asset('images/leadership-meeting.webp'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3c0808d0ccb9dfd11d29783234199f91)): ?>
<?php $attributes = $__attributesOriginal3c0808d0ccb9dfd11d29783234199f91; ?>
<?php unset($__attributesOriginal3c0808d0ccb9dfd11d29783234199f91); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3c0808d0ccb9dfd11d29783234199f91)): ?>
<?php $component = $__componentOriginal3c0808d0ccb9dfd11d29783234199f91; ?>
<?php unset($__componentOriginal3c0808d0ccb9dfd11d29783234199f91); ?>
<?php endif; ?>

    
    <div class="fixed inset-x-0 z-50 max-w-5xl mx-auto bottom-2">
        <div
            class="flex items-center gap-3 p-3 border shadow-lg md:justify-between rounded-2xl bg-sky-950/90 backdrop-blur-md border-amber-300/20 ring-1 ring-white/10">
            <div class="items-center hidden min-w-0 gap-3 md:flex">
                <img src="<?php echo e(asset('images/logo/ee-gold.webp')); ?>" class="w-8 h-8" alt="EE">
                <div class="min-w-0">
                    <p class="text-sm font-semibold truncate text-amber-200"><?php echo e($event->course->type); ?> •
                        <?php echo e($event->course->name); ?>

                    </p>
                    <p class="text-xs truncate text-white/80">
                        Começa dia <?php echo e(date('d/m', strtotime($event->date))); ?> às
                        <?php echo e(date('H', strtotime($event->start_time))); ?>h • Horário de Brasília
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-4">
                
                <img src="<?php echo e(asset('images/logo/ee-gold.webp')); ?>" class="w-8 h-8 md:hidden" alt="EE">

                <?php
                    $datetime_start =
                        date('Y-m-d', strtotime($event->eventDates()->first()->date)) .
                        'T' .
                        date('H:i:s', strtotime($event->eventDates()->first()->start_time)) .
                        '-03:00';
                    $datetime_end =
                        date('Y-m-d', strtotime($event->eventDates()->first()->date)) .
                        'T' .
                        date('H:i:s', strtotime($event->eventDates()->first()->end_time)) .
                        '-03:00';
                ?>
                
                <div class="max-w-sm" data-countdown>
                    
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

                    
                    <div data-countdown-ended
                        class="hidden px-5 py-2 text-center rounded-2xl bg-white/10 ring-1 ring-white/10">
                        <div class="text-base font-semibold text-white/90" style="text-shadow: 1px 1px 1px black;">
                            Evento encerrado
                        </div>
                    </div>
                </div>
            </div>

            <div id="button-fixed-subscribe" class="flex items-center gap-2 shrink-0">
                <a href="#" download
                    class="items-center justify-center hidden px-4 py-2 text-sm font-semibold text-white transition border shine sm:inline-flex rounded-xl border-white/10 bg-white/10 hover:bg-white/15 focus:outline-none focus:ring-2 focus:ring-amber-300/50">
                    Baixar cartaz
                </a>

                <a href="<?php echo e(route('web.event.registration', ['id' => $event->id])); ?>"
                    class="inline-flex items-center justify-center px-5 py-2.5 text-sm font-bold text-slate-950 rounded-xl
                      bg-linear-to-r from-[#f1d57a] via-[#c7a840] to-[#8a7424] shine
                      shadow-sm shadow-black/30 hover:brightness-110 focus:outline-none focus:ring-2 focus:ring-amber-300/70">
                    Inscrever-se
                </a>
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
                        const startIso = <?php echo json_encode($datetime_start, 15, 512) ?>;
                        const endIso = <?php echo json_encode($datetime_end, 15, 512) ?>;
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

    
    <section class="px-4 mx-auto max-w-8xl sm:px-6 lg:px-8">
        <div class="grid items-start gap-8 lg:grid-cols-12">

            
            <div class="lg:col-span-7" data-reveal>
                <div class="overflow-hidden bg-white border shadow-sm rounded-3xl ring-1 ring-slate-900/10">
                    <div class="relative">
                        
                        <img src="<?php echo e(asset('images/cover.jpg')); ?>" alt="Workshop O Evangelho Em Sua Mão"
                            class="object-cover w-full h-72 sm:h-80">
                        <div
                            class="absolute inset-x-0 bottom-0 h-2 bg-linear-to-r from-[#f1d57a] via-[#c7a840] to-[#8a7424]">
                        </div>
                    </div>

                    <div class="p-6 sm:p-8">
                        <div class="flex flex-wrap items-center gap-2">
                            <span
                                class="inline-flex items-center px-3 py-1 text-xs font-semibold border rounded-full bg-slate-50 text-slate-700 border-slate-200">
                                Carga horária: <span
                                    class="ml-1 font-bold text-slate-900"><?php echo e($workloadDuration ?? '00h'); ?></span>
                            </span>
                            <span
                                class="inline-flex items-center px-3 py-1 text-xs font-semibold border rounded-full bg-slate-50 text-slate-700 border-slate-200">
                                Investimento: <span class="ml-1 font-bold text-amber-900">R$
                                    <?php echo e(number_format($eventPrice, 2, ',', '.')); ?></span>
                            </span>
                        </div>

                        <h1 id="page-title"
                            class="mt-8 text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl lg:text-5xl">
                            <div class="text-2xl font-semibold">
                                <?php echo e($event->course->type); ?>

                            </div>
                            <?php echo e($event->course->name); ?>

                        </h1>

                        <p class="mt-2 text-slate-600">
                            Fácil de Aprender, agradável de compartilhar e quase impossível de esquecer
                        </p>

                        <div class="flex justify-end">
                            <a href="<?php echo e($event->course->learnMoreLink); ?>" target="_blanc"
                                class="inline-flex items-center justify-center px-6 py-3 text-sm font-semibold transition bg-white border rounded-2xl border-slate-200 hover:bg-slate-50">
                                Detalhes do ministério
                            </a>
                        </div>

                        
                        <div class="grid gap-3 mt-6 sm:grid-cols-2">

                            <?php $__currentLoopData = $event->eventDates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $date_event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="p-4 border rounded-2xl bg-slate-50 border-slate-200">
                                    <p class="font-semibold text-slate-900">
                                        <span class="mr-2">&#x1F4C5;</span>
                                        <?php echo e(\Illuminate\Support\Str::ucfirst(\Carbon\Carbon::parse($date_event->date)->locale('pt_BR')->isoFormat('dddd'))); ?>

                                        • <?php echo e(date('d/m', strtotime($date_event->date))); ?>:
                                        <span class="font-light text-amber-900">
                                            das <?php echo e(date('H:i', strtotime($date_event->start_time))); ?> às
                                            <?php echo e(date('H:i', strtotime($date_event->end_time))); ?>

                                        </span>
                                    </p>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                        </div>
                        <div class="mt-2 text-xs text-right text-slate-500">Horário de Brasília</div>
                        
                        <div class="p-5 mt-6 bg-white border rounded-2xl border-slate-200">
                            <h2 class="text-lg text-slate-900" style="font-family:'Cinzel', serif;">Descrição</h2>
                            <p class="mt-2 text-sm leading-relaxed text-slate-700">
                                <?php echo e($event->course->description); ?>

                            </p>
                        </div>

                        
                        <div class="p-5 mt-6 border rounded-2xl bg-slate-50 border-slate-200">
                            <h2 class="text-lg text-slate-900" style="font-family:'Cinzel', serif;">Público-alvo
                            </h2>
                            <div class="mt-2 space-y-2 text-sm list-disc list-inside text-slate-700">
                                <?php echo e($event->course->targetAudience); ?>

                            </div>
                        </div>

                        
                        <div class="flex flex-col gap-3 mt-6 sm:flex-row sm:items-center">
                            <a id="inscricao" href="<?php echo e(route('web.event.registration', ['id' => $event->id])); ?>"
                                class="inline-flex items-center justify-center shine px-6 py-3 text-sm font-bold text-slate-950 rounded-2xl
                                  bg-linear-to-r from-[#f1d57a] via-[#c7a840] to-[#8a7424]
                                  shadow-sm shadow-black/20 hover:brightness-110 focus:outline-none focus:ring-2 focus:ring-amber-300/70">
                                Fazer inscrição
                            </a>

                            <a href="#" download
                                class="inline-flex items-center justify-center px-6 py-3 text-sm font-semibold transition bg-white border rounded-2xl border-slate-200 hover:bg-slate-50">
                                Baixar cartaz
                            </a>
                            <a href="#" download
                                class="inline-flex items-center justify-center px-6 py-3 text-sm font-semibold transition bg-white border rounded-2xl border-slate-200 hover:bg-slate-50">
                                Programação completa
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            
            <aside class="space-y-6 lg:col-span-5" data-reveal>

                
                <div class="p-6 bg-white border shadow-sm rounded-3xl ring-1 ring-slate-900/10">
                    <h2 class="text-lg text-slate-900" style="font-family:'Cinzel', serif;">Local</h2>
                    <div class="mt-3 space-y-2">
                        <div class="p-4 border rounded-2xl bg-slate-50 border-slate-200">
                            <p class="mt-1 text-lg font-bold text-center text-slate-700"><?php echo e($event->church->name); ?></p>
                        </div>

                        <div class="p-4 border rounded-2xl bg-slate-50 border-slate-200">
                            <p class="text-sm font-semibold text-slate-900">
                                <span class="mr-2">&#x1F4CD;</span>
                                Endereço
                            </p>
                            <a class="mt-1 text-sm text-slate-700" target="_blank" rel="noopener"
                                href="https://www.google.com/maps/search/?api=1&query=<?php echo e(urlencode($churchAddress)); ?>">
                                <?php echo e($event->church->street); ?>, <?php echo e($event->church->number); ?>,
                                <?php echo e($event->church->district); ?>, <?php echo e($event->church->city); ?>,
                                <?php echo e($event->church->state); ?>

                            </a>
                        </div>
                    </div>

                    <div class="mt-4 overflow-hidden border rounded-2xl border-slate-200 bg-slate-50">
                        <iframe class="w-full h-72" loading="lazy" referrerpolicy="no-referrer-when-downgrade"
                            src="https://www.google.com/maps?q=<?php echo e(urlencode($churchAddress)); ?>&output=embed&hl=pt-BR"></iframe>
                    </div>

                    <a class="inline-flex items-center justify-center w-full px-4 py-2 mt-3 text-sm font-semibold bg-white border rounded-xl border-slate-200 hover:bg-slate-50"
                        target="_blank" rel="noopener"
                        href="https://www.google.com/maps/search/?api=1&query=<?php echo e(urlencode($churchAddress)); ?>">
                        Abrir no Google Maps
                    </a>
                </div>

                
                <div class="p-6 bg-white border shadow-sm rounded-3xl ring-1 ring-slate-900/10">
                    <h2 class="pb-2 text-lg text-slate-900" style="font-family:'Cinzel', serif;">Contato</h2>
                    <div class="grid gap-3">
                        <div class="p-4 border rounded-2xl bg-slate-50 border-slate-200">
                            <p class="text-sm font-semibold text-slate-900"><?php echo e($event->church->contact); ?></p>
                            <div class="mt-1 space-y-1 text-sm text-slate-700">
                                <p>
                                    Telefone:
                                    <?php echo e(\App\Helpers\PhoneHelper::format_phone($event->church->contact_phone)); ?>

                                </p>

                                <p>
                                    E-mail: <?php echo e($event->church->contact_email); ?>

                                </p>
                            </div>
                        </div>

                        
                        <div class="my-3 h-[2px] w-full mx-auto lg:mx-0"
                            style="border-radius: 100%; background: linear-gradient(135deg,
                        #c7a8401a,
                        #c7a8408c,
                        #c7a8401a);">
                        </div>

                        <div class="p-4 border rounded-2xl bg-amber-50 border-amber-200">
                            <p class="text-sm font-semibold text-amber-900">Investimento</p>
                            <p class="mt-1 text-sm text-amber-900">
                                <span class="font-extrabold">R$ <?php echo e(number_format($eventPrice, 2, ',', '.')); ?></span>
                                <span class="text-amber-800/80">por participante</span>
                            </p>
                        </div>
                    </div>
                </div>

                
                <div class="max-w-sm bg-sky-950">
                </div>

            </aside>
        </div>
    </section>

 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7ddf49af801524849d67e38f92bf39c7)): ?>
<?php $attributes = $__attributesOriginal7ddf49af801524849d67e38f92bf39c7; ?>
<?php unset($__attributesOriginal7ddf49af801524849d67e38f92bf39c7); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7ddf49af801524849d67e38f92bf39c7)): ?>
<?php $component = $__componentOriginal7ddf49af801524849d67e38f92bf39c7; ?>
<?php unset($__componentOriginal7ddf49af801524849d67e38f92bf39c7); ?>
<?php endif; ?>
<?php /**PATH /home/cleiton/workspaces/ee/resources/views/pages/web/events/show.blade.php ENDPATH**/ ?>