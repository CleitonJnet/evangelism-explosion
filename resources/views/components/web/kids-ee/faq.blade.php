{{-- ========================= SECTION 7 — FAQ + vídeo ao lado ========================= --}}
<section id="faq" class="-mb-20 bg-fixed bg-bottom bg-no-repeat bg-cover"
    style="background-image: url({{ asset('images/ee-kids/HFK-Graphic-yellow-crow.png') }})">
    <div class="px-4 mx-auto max-w-8xl sm:px-6 lg:px-8 py-14 lg:py-20">
        <div class="grid items-start gap-10 lg:grid-cols-12">
            <div class="lg:col-span-6">
                <h2 class="text-2xl font-extrabold text-white sm:text-3xl">
                    Perguntas e respostas
                </h2>

                <p class="mt-4 text-lg text-white reveal">
                    Respostas rápidas para líderes e igrejas que querem começar com segurança.
                </p>

                <div class="mt-8 space-y-3">
                    @php
                        $faq = [
                            [
                                'q' => 'O que é necessário para começar o EPC na igreja?',
                                'a' =>
                                    'Recomendamos 1–3 líderes-chave treinados no Workshop (12h/2 dias), organização da equipe do ministério infantil, planejamento do ciclo e materiais de implementação.',
                            ],
                            [
                                'q' => 'O EPC é só para crianças da igreja?',
                                'a' =>
                                    'Não. O EPC prepara crianças para viver e compartilhar o Evangelho, e assim naturalmente alcançando amigos, familiares e outras crianças ao seu redor.',
                            ],
                            [
                                'q' => 'Como o Workshop ajuda na prática?',
                                'a' =>
                                    'Os líderes saem com visão, plano e materiais para treinar professores, conduzir a implementação e acompanhar o progresso das crianças.',
                            ],
                            [
                                'q' => 'Tem suporte depois do treinamento?',
                                'a' =>
                                    'Sim. Além do Workshop/Clínica, orientamos o próximo passo da implementação e boas práticas de multiplicação.',
                            ],
                        ];
                    @endphp

                    @foreach ($faq as $idx => $f)
                        <div class="p-4 border reveal rounded-2xl border-slate-200 bg-slate-50" data-accordion
                            data-open="false">
                            <button type="button" class="flex items-center justify-between w-full gap-4" data-acc-btn>
                                <span class="font-extrabold text-left text-slate-900">{{ $f['q'] }}</span>
                                <svg data-acc-icon xmlns="http://www.w3.org/2000/svg"
                                    class="w-5 h-5 transition text-slate-500" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div class="mt-3 leading-relaxed text-slate-600" data-acc-panel>
                                {{ $f['a'] }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="lg:col-span-6">
                <div class="overflow-hidden border shadow-xl reveal rounded-3xl border-slate-200">
                    <div class="bg-black aspect-video">
                        <iframe class="w-full h-full" src="https://www.youtube.com/embed/anQGETkzAwQ"
                            title="Hope for Kids" frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                            allowfullscreen></iframe>
                    </div>
                    <div class="p-6 bg-white">
                        <p class="text-xl font-extrabold text-slate-900">
                            Quer ver o EPC em ação?
                        </p>
                        <p class="mt-2 text-slate-600">
                            Assista ao vídeo e, se desejar, solicite um Workshop de Liderança para sua igreja.
                        </p>
                        <a href="{{ route('web.event.schedule') }}"
                            class="inline-flex items-center justify-center w-full px-5 py-3 mt-5 font-black text-white transition shine rounded-xl bg-amber-500 hover:brightness-95">
                            Levar o Workshop para minha igreja
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>


@push('js')
    <script>
        /* ========================= FAQ accordion ========================= */
        const items = document.querySelectorAll('[data-accordion]');
        items.forEach(item => {
            const btn = item.querySelector('[data-acc-btn]');
            const panel = item.querySelector('[data-acc-panel]');
            if (!btn || !panel) return;

            panel.style.maxHeight = '0px';
            panel.style.overflow = 'hidden';
            panel.style.transition = 'max-height .35s ease';

            btn.addEventListener('click', () => {
                const isOpen = item.getAttribute('data-open') === 'true';

                // fecha todos
                items.forEach(i => {
                    i.setAttribute('data-open', 'false');
                    const p = i.querySelector('[data-acc-panel]');
                    const ic = i.querySelector('[data-acc-icon]');
                    if (p) p.style.maxHeight = '0px';
                    if (ic) ic.style.transform = 'rotate(0deg)';
                });

                if (!isOpen) {
                    item.setAttribute('data-open', 'true');
                    panel.style.maxHeight = panel.scrollHeight + 'px';
                    const icon = item.querySelector('[data-acc-icon]');
                    if (icon) icon.style.transform = 'rotate(180deg)';
                }
            });
        });
    </script>
@endpush
