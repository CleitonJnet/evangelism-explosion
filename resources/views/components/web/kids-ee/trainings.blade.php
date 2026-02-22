{{-- ========================= SECTION 3 — Workshop/Clínica + vídeo + materiais incluídos ========================= --}}
<section id="workshop" class="relative text-white bg-fixed bg-center bg-no-repeat bg-cover"
    style="background-image: url({{ asset('images/ee-kids/call-event-.png') }});">
    <div class="px-4 mx-auto max-w-8xl sm:px-6 lg:px-8 py-14 lg:py-20">
        <div class="grid items-start gap-10 lg:grid-cols-12">
            <div class="lg:col-span-7">
                <h2 class="text-3xl font-extrabold md:text-4xl lg:text-5xl font-averia-bold">
                    Workshop de Liderança EPC
                </h2>

                <p class="mt-5 text-lg leading-relaxed reveal">
                    Participe de um de nossos <strong>Workshops de Liderança Esperança Para Crianças</strong>,
                    um treinamento de <strong>20 horas</strong> ao longo de <strong>dois dias</strong>,
                    e veja seus líderes de ministérios infantis/pastores saírem com as habilidades e materiais
                    necessários para equipar crianças e equipe com o Evangelho.
                </p>

                <div class="p-6 mt-6 border reveal rounded-3xl border-slate-200 bg-slate-50">
                    <p class="font-extrabold text-slate-800">
                        O que acontece depois do Workshop?
                    </p>
                    <p class="mt-2 leading-relaxed text-sky-900">
                        Não só as crianças serão fundamentadas no Evangelho, como serão <strong>equipadas e
                            encorajadas</strong>
                        a compartilhar a Boa Nova com outras pessoas, com acompanhamento e implementação na igreja
                        local.
                    </p>
                </div>

                {{-- mini banner decor --}}
                <div class="mt-6 overflow-hidden border reveal rounded-3xl border-slate-200">
                    <div class="relative p-6 bg-slate-900">
                        <div class="absolute inset-0 opacity-20"
                            style="background-image:url('{{ asset('images/ee-kids/kids-bg-blue.png') }}'); background-size:contaobject-contain; background-position:center;">
                        </div>
                        <div class="relative">
                            <p class="text-lg font-extrabold">Clínica EE-Kids (1 a 3 líderes-chave)</p>
                            <p class="mt-2 text-white/85">
                                Treinamento focado em liderança e implementação, com materiais e suporte.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="mt-8 overflow-hidden border shadow-lg reveal rounded-3xl border-slate-200">
                    <figure class="w-full">
                        <img src="{{ asset('images/ee-kids/certificate-hope-for-kids-workshop.webp') }}"
                            alt="Foto do Congresso das Nações realizado em 2016" class="w-full h-auto"
                            style="box-shadow: 3px -3px 0 #c7a840" />
                        <figcaption
                            class="absolute inset-x-0 bottom-0 p-2 text-sm text-center text-white bg-linear-to-t from-black to-transparent">
                            Certificação no final do Treinamento
                        </figcaption>
                    </figure>

                </div>
            </div>

            <div class="lg:col-span-5">
                <div class="overflow-hidden border shadow-lg reveal rounded-3xl border-slate-200">
                    <div class="p-6 bg-linear-to-r from-amber-500 to-orange-500">
                        <h3 class="text-xl font-extrabold">Materiais usados no treinamento</h3>
                    </div>

                    <div class="p-6 bg-white">
                        <div class="grid grid-cols-2 gap-0.5">
                            <div class="relative overflow-hidden bg-bottom bg-no-repeat bg-cover border h-60 md:h-80 lg:h-96 rounded-2xl border-slate-200"
                                style="background-image: url({{ asset('images/ee-kids/handbook_hfk_activities-manual.png') }})">
                                <div
                                    class="absolute inset-x-0 bottom-0 w-full p-4 h-fit bg-linear-to-t from-black to-transparent">
                                    <p class="font-extrabold">Manual de Atividades</p>
                                    <p class="text-xs truncate" title="Aulas e atividades para as crianças.">Aulas e
                                        atividades para as crianças.
                                    </p>
                                </div>
                            </div>

                            <div class="relative overflow-hidden bg-bottom bg-no-repeat bg-cover border h-60 md:h-80 lg:h-96 rounded-2xl border-slate-200"
                                style="background-image: url({{ asset('images/ee-kids/handbook_hfk_teacher.png') }})">
                                <div
                                    class="absolute inset-x-0 bottom-0 w-full p-4 h-fit bg-linear-to-t from-black to-transparent">
                                    <p class="font-extrabold">Manual do Professor</p>
                                    <p class="text-xs truncate" title="Guia passo a passo de implementação.">Guia
                                        passo a passo de implementação.
                                    </p>
                                </div>
                            </div>

                        </div>

                        <div class="p-5 mt-6 border rounded-2xl bg-slate-50 border-slate-200">
                            <p class="font-extrabold text-slate-900">
                                Resultado esperado no final do Workshop
                            </p>
                            <ul class="mt-3 space-y-2 text-slate-700">
                                <li class="flex gap-3">
                                    <span class="w-2 h-2 mt-2 rounded-full bg-amber-500"></span>
                                    <span>Líderes entendem o EPC e conseguem treinar a equipe local.</span>
                                </li>
                                <li class="flex gap-3">
                                    <span class="w-2 h-2 mt-2 rounded-full bg-amber-500"></span>
                                    <span>Planejamento do primeiro ciclo de implementação na igreja.</span>
                                </li>
                                <li class="flex gap-3">
                                    <span class="w-2 h-2 mt-2 rounded-full bg-amber-500"></span>
                                    <span>Crianças serão ensinadas e encorajadas a compartilhar a Boa Nova.</span>
                                </li>
                            </ul>
                        </div>

                        <a href="{{ route('web.event.schedule-request') }}"
                            class="inline-flex items-center justify-center w-full px-5 py-3 mt-6 font-black transition bg-red-600 shine rounded-xl hover:bg-red-700">
                            Quero agendar um Workshop de Liderança
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="absolute inset-x-0 -bottom-0.5 z-0 w-full h-1" style="background-color: #082f49;"></div>
    {{-- divisor --}}
    <svg class="absolute inset-x-0 bottom-0 z-10 block w-full" viewBox="0 0 1440 80" preserveAspectRatio="none">
        <path fill="#082f49"
            d="M0,32L120,53.3C240,75,480,117,720,117.3C960,117,1200,75,1320,53.3L1440,32L1440,80L0,80Z"></path>
    </svg>
</section>
