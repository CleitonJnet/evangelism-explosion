@php
    $title = 'Como se tornar uma Base de Treinamentos';
    $description =
        'Passo a passo inspirado no manual SEMAD para que sua igreja implemente e multiplique o ministério Evangelismo Explosivo.';
    $keywords = 'base de treinamento, evangelismo explosivo, implementação, discipulado, mentoria';
    $ogImage = asset('images/leadership-meeting.webp');
@endphp

<x-layouts.guest :title="$title" :description="$description" :keywords="$keywords" :ogImage="$ogImage">
    <x-web.header title='Como se tornar uma <span class="text-nowrap">Base de Treinamentos</span>'
        subtitle='Descubra o caminho para que sua igreja implemente e multiplique o ministério de EE'
        :cover="asset('images/leadership-meeting.webp')" />

    <x-web.container>

        {{-- Conteúdo principal --}}
        <h2 class="relative mb-6 font-serif text-3xl text-blue-900">
            O evento é o meio, não o fim
            {{-- Linha temática (dourado metálico) --}}
            <span
                class="absolute left-0 -bottom-2 h-[2px] w-[min(28rem,100%)]
                           bg-linear-to-r from-[#f1d57a] via-[#c7a840] to-[#8a7424] opacity-90">
            </span>
        </h2>

        <p class="mb-4 leading-relaxed text-gray-700">
            Uma Clínica não é o ponto de chegada nem o fim da jornada; é
            o ponto de partida para uma grande e transformadora aventura.
        </p>

        <p class="mb-4 leading-relaxed text-gray-700">
            Desde 2017 o Evangelismo Explosivo ajustou seus processos para que os
            <strong>eventos de capacitação sejam um meio para formar discípulos e igrejas
                multiplicadoras</strong>. A missão continua a mesma: glorificar a Deus equipando
            igrejas para que se multipliquem por todo o mundo. Esse roteiro foi elaborado para ajudar
            pastores e líderes que ainda não conhecem o ministério a iniciar essa jornada.
        </p>

        <div aria-hidden="true"
            class="h-1 my-10 pointer-events-none bg-linear-to-r from-transparent via-amber-500/40 to-transparent">
        </div>


        {{-- Passo a passo --}}
        <h2 class="max-w-xl mx-auto mb-12 font-serif text-3xl text-center text-sky-950">
            6 passos para sua igreja se tornar uma <span class="font-bold text-nowrap text-sky-900">Base de
                Treinamentos</span>
        </h2>

        <div class="grid grid-cols-1 gap-5 pb-10 mt-10 xs:grid-cols-2 lg:grid-cols-3">
            <!-- PASSO 01 -->
            <a href="#step_1"
                class="group relative flex items-center gap-4 px-6 py-5 bg-white border border-gray-200 rounded-lg transition-all duration-300 hover:-translate-y-1 hover:border-[#c7a840] hover:shadow-[0_10px_25px_rgba(199,168,64,0.25)]">

                <!-- Barra dourada -->
                <span
                    class="absolute left-0 top-[12%] h-[76%] w-1 opacity-0 rounded-e transition-opacity duration-300
                       bg-linear-to-b from-[#f1d57a] via-[#c7a840] to-[#8a7424]
                       group-hover:opacity-100">
                </span>

                <!-- Número -->
                <span
                    class="font-[Cinzel] text-xl font-semibold
                       bg-linear-to-br from-[#f1d57a] via-[#c7a840] to-[#8a7424]
                       bg-clip-text text-transparent">
                    01
                </span>

                <!-- Texto -->
                <span class="font-medium text-gray-800">
                    Visão e Oração
                </span>
            </a>

            <!-- PASSO 02 -->
            <a href="#step_2"
                class="group relative flex items-center gap-4 px-6 py-5 bg-white border border-gray-200 rounded-lg transition-all duration-300 hover:-translate-y-1 hover:border-[#c7a840] hover:shadow-[0_10px_25px_rgba(199,168,64,0.25)]">

                <!-- Barra dourada -->
                <span
                    class="absolute left-0 top-[12%] h-[76%] w-1 opacity-0 rounded-e transition-opacity duration-300
                       bg-linear-to-b from-[#f1d57a] via-[#c7a840] to-[#8a7424]
                       group-hover:opacity-100">
                </span>

                <span
                    class="font-[Cinzel] text-xl font-semibold
                       bg-linear-to-br from-[#f1d57a] via-[#c7a840] to-[#8a7424]
                       bg-clip-text text-transparent">
                    02
                </span>

                <span class="font-medium text-gray-800">
                    Capacitação
                </span>
            </a>

            <!-- PASSO 03 -->
            <a href="#step_3"
                class="group relative flex items-center gap-4 px-6 py-5 bg-white border border-gray-200 rounded-lg transition-all duration-300 hover:-translate-y-1 hover:border-[#c7a840] hover:shadow-[0_10px_25px_rgba(199,168,64,0.25)]">

                <!-- Barra dourada -->
                <span
                    class="absolute left-0 top-[12%] h-[76%] w-1 opacity-0 rounded-e transition-opacity duration-300
                       bg-linear-to-b from-[#f1d57a] via-[#c7a840] to-[#8a7424]
                       group-hover:opacity-100">
                </span>

                <span
                    class="font-[Cinzel] text-xl font-semibold
                       bg-linear-to-br from-[#f1d57a] via-[#c7a840] to-[#8a7424]
                       bg-clip-text text-transparent">
                    03
                </span>

                <span class="font-medium text-gray-800">
                    Crie uma Equipe local
                </span>
            </a>

            <!-- PASSO 04 -->
            <a href="#step_4"
                class="group relative flex items-center gap-4 px-6 py-5 bg-white border border-gray-200 rounded-lg transition-all duration-300 hover:-translate-y-1 hover:border-[#c7a840] hover:shadow-[0_10px_25px_rgba(199,168,64,0.25)]">

                <!-- Barra dourada -->
                <span
                    class="absolute left-0 top-[12%] h-[76%] w-1 opacity-0 rounded-e transition-opacity duration-300
                       bg-linear-to-b from-[#f1d57a] via-[#c7a840] to-[#8a7424]
                       group-hover:opacity-100">
                </span>

                <span
                    class="font-[Cinzel] text-xl font-semibold
                       bg-linear-to-br from-[#f1d57a] via-[#c7a840] to-[#8a7424]
                       bg-clip-text text-transparent">
                    04
                </span>

                <span class="font-medium text-gray-800">
                    Faça o Evento de lançamento
                </span>
            </a>

            <!-- PASSO 05 -->
            <a href="#step_5"
                class="group relative flex items-center gap-4 px-6 py-5 bg-white border border-gray-200 rounded-lg transition-all duration-300 hover:-translate-y-1 hover:border-[#c7a840] hover:shadow-[0_10px_25px_rgba(199,168,64,0.25)]">

                <!-- Barra dourada -->
                <span
                    class="absolute left-0 top-[12%] h-[76%] w-1 opacity-0 rounded-e transition-opacity duration-300
                       bg-linear-to-b from-[#f1d57a] via-[#c7a840] to-[#8a7424]
                       group-hover:opacity-100">
                </span>

                <span
                    class="font-[Cinzel] text-xl font-semibold
                       bg-linear-to-br from-[#f1d57a] via-[#c7a840] to-[#8a7424]
                       bg-clip-text text-transparent">
                    05
                </span>

                <span class="font-medium text-gray-800">
                    Receba a Mentoria
                </span>
            </a>

            <!-- PASSO 06 -->
            <a href="#step_6"
                class="group relative flex items-center gap-4 px-6 py-5 bg-white border border-gray-200 rounded-lg transition-all duration-300 hover:-translate-y-1 hover:border-[#c7a840] hover:shadow-[0_10px_25px_rgba(199,168,64,0.25)]">

                <!-- Barra dourada -->
                <span
                    class="absolute left-0 top-[12%] h-[76%] w-1 opacity-0 rounded-e transition-opacity duration-300
                       bg-linear-to-b from-[#f1d57a] via-[#c7a840] to-[#8a7424]
                       group-hover:opacity-100">
                </span>

                <span
                    class="font-[Cinzel] text-xl font-semibold
                       bg-linear-to-br from-[#f1d57a] via-[#c7a840] to-[#8a7424]
                       bg-clip-text text-transparent">
                    06
                </span>

                <span class="font-medium text-gray-800">
                    Multiplicação
                </span>
            </a>
        </div>

        {{-- Passo 1 --}}
        <div id="step_1"
            class="relative flex flex-col items-center justify-between gap-10 py-10 lg:flex-row before:absolute before:top-0 before:w-full before:bg-linear-to-r before:from-transparent before:via-amber-500/15 before:to-transparent before:left-0 before:h-0.5">
            <img src="{{ asset('images/planner.webp') }}" alt="Ícone de oração" style="box-shadow: 5px -5px 0 #c7a840"
                class="object-cover border-t-4 border-r-4 border-white rounded-lg h-80 aspect-video lg:order-1">
            <div class="lg:order-0">
                <h3 class="mb-3 text-xl font-semibold text-amber-600">
                    Passo 1. Receba a visão e ore
                </h3>
                <p class="leading-relaxed text-gray-700">
                    Comece com oração e reflexão. Leia sobre a missão do Evangelismo Explosivo e peça
                    discernimento para formar uma equipe fiel. Como ensina o manual, a oração e a seleção
                    cuidadosa de pessoas comprometidas são indispensáveis.
                    Agende uma reunião inicial com a liderança da igreja para partilhar a visão e alinhar
                    expectativas.
                </p>
            </div>
        </div>

        {{-- Passo 2 --}}
        <div id="step_2"
            class="relative flex flex-col items-center justify-between gap-10 py-10 lg:flex-row before:absolute before:top-0 before:w-full before:bg-linear-to-r before:from-transparent before:via-amber-500/15 before:to-transparent before:left-0 before:h-0.5">
            <img src="{{ asset('images/clinic-ee.webp') }}" alt="Ícone de oração"
                style="box-shadow: -5px -5px 0 #c7a840"
                class="object-cover border-t-4 border-l-4 border-white rounded-lg h-80 aspect-video">
            <div>
                <h3 class="mb-3 text-xl font-semibold text-amber-600">
                    Passo 2. Participe de um Evento de Capacitação
                </h3>
                <p class="leading-relaxed text-gray-700">
                    O segundo passo é participar de uma <strong>Clínica de Evangelismo Explosivo</strong>.
                    Essa experiência começa com um Workshop e se estende por uma estrutura contínua de
                    evangelismo e
                    discipulado,
                    na qual sua equipe aprende fazendo, lado a lado com um mentor experiente.
                    O evento não se limita à transmissão de informações; ele modela um ministério vivo,
                    saudável com foco na <strong>multiplicação exponencial</strong>.
                </p>
            </div>
        </div>

        {{-- Passo 3 --}}
        <div id="step_3"
            class="relative flex flex-col items-center justify-between gap-10 py-10 lg:flex-row before:absolute before:top-0 before:w-full before:bg-linear-to-r before:from-transparent before:via-amber-500/15 before:to-transparent before:left-0 before:h-0.5">
            <img src="{{ asset('images/local-team.webp') }}" alt="Ícone de oração"
                style="box-shadow: 5px -5px 0 #c7a840"
                class="object-cover border-t-4 border-r-4 border-white rounded-lg h-80 aspect-video lg:order-1">
            <div class="lg:order-0">
                <h3 class="mb-3 text-xl font-semibold text-amber-600">
                    Passo 3. Forme sua equipe local
                </h3>
                <p class="leading-relaxed text-gray-700">
                    Conforme o manual, a execução de um evento requer uma equipe bem organizada com funções
                    específicas. O <strong>Líder do Evento</strong> coordena todas as áreas;
                    o <strong>Administrador</strong> supervisiona os detalhes logísticos; o
                    <strong>Facilitador</strong>
                    mantém a documentação e monta a equipe de protocolo; e os coordenadores cuidam de visitação,
                    alimentação, instalações, hospedagem, inscrições, materiais, finanças, planejamento e
                    protocolo.
                    Não se assuste com a lista – muitas igrejas começam com quatro a oito pessoas e crescem
                    conforme a
                    necessidade.
                </p>
            </div>
        </div>

        {{-- Passo 4 --}}
        <div id="step_4"
            class="relative flex flex-col items-center justify-between gap-10 py-10 lg:flex-row before:absolute before:top-0 before:w-full before:bg-linear-to-r before:from-transparent before:via-amber-500/15 before:to-transparent before:left-0 before:h-0.5">
            <img src="{{ asset('images/event.webp') }}" alt="Ícone de oração" style="box-shadow: -5px -5px 0 #c7a840"
                class="object-cover border-t-4 border-l-4 border-white rounded-lg h-80 aspect-video">
            <div>
                <h3 class="mb-3 text-xl font-semibold text-amber-600">
                    Passo 4. Planeje e realize o seu Evento de Lançamento
                </h3>
                <p class="leading-relaxed text-gray-700">
                    Com o comitê formado, estabeleça um cronograma de contagem regressiva: três meses antes,
                    organize a reunião de lançamento e defina responsabilidades; um mês antes, recrute alunos e
                    companheiros de oração; duas semanas antes, ajuste o local e os equipamentos; na véspera,
                    prepare os kits de visitação e a recepção.
                    Durante o evento, garanta saídas de treinamento prático (SEP), sessões de testemunhos e
                    acompanhamento estatístico. Lembre‑se de que um evento bem preparado é um testemunho de
                    excelência para a comunidade.
                </p>
            </div>
        </div>

        {{-- Passo 5 --}}
        <div id="step_5"
            class="relative flex flex-col items-center justify-between gap-10 py-10 lg:flex-row before:absolute before:top-0 before:w-full before:bg-linear-to-r before:from-transparent before:via-amber-500/15 before:to-transparent before:left-0 before:h-0.5">
            <img src="{{ asset('images/mentoring.webp') }}" alt="Ícone de oração" style="box-shadow: 5px -5px 0 #c7a840"
                class="object-cover border-t-4 border-r-4 border-white rounded-lg h-80 aspect-video lg:order-1">
            <div class="lg:order-0">
                <h3 class="mb-3 text-xl font-semibold text-amber-600">
                    Passo 5. Receba mentoria e implemente o ministério
                </h3>
                <p class="leading-relaxed text-gray-700">
                    Após o evento, a verdadeira jornada começa. A Igreja Base (IB) acompanhará sua igreja
                    nas fases de promoção, planejamento e implementação.
                    O objetivo é que a igreja com <strong>ministério de EE emergente (IE)</strong> avance para igreja
                    com <strong>ministério de EE em crescimento (IC)</strong> e depois se torne igreja com
                    <strong>ministério de EE multiplicador</strong> (IM). O processo de mentoria segue quatro passos:
                    <strong>Demonstração</strong> (a IM/IB modela o ministério),
                    <strong>Revisão</strong> (reuniões de feedback),
                    <strong>Fazer</strong> (sua equipe pratica em ambiente seguro) e
                    <strong>Soltar</strong> (quando estiver pronta, a igreja assume o ministério de forma
                    autônoma).
                    Igrejas acompanhadas implementam um ministério vibrante de discipulado e evangelização.
                </p>
            </div>
        </div>

        {{-- Passo 6 --}}
        <div id="step_6"
            class="relative flex flex-col items-center justify-between gap-10 py-10 lg:flex-row before:absolute before:top-0 before:w-full before:bg-linear-to-r before:from-transparent before:via-amber-500/15 before:to-transparent before:left-0 before:h-0.5">
            <img src="{{ asset('images/church-clinic-base.webp') }}" alt="Ícone de oração"
                style="box-shadow: -5px -5px 0 #c7a840"
                class="object-cover border-t-4 border-l-4 border-white rounded-lg h-80 aspect-video">
            <div>
                <h3 class="mb-3 text-xl font-semibold text-amber-600">
                    Passo 6. Multiplique e torne-se uma Igreja Base
                </h3>
                <p class="leading-relaxed text-gray-700">
                    Finalmente, quando sua igreja estiver discipulando e evangelizando de forma contínua, será
                    hora de multiplicar. Igrejas multiplicadoras adotam até cinco igrejas emergentes,
                    transferindo recursos e visão até que estas alcancem o próximo nível.
                    Ao se tornar uma Igreja Base, você realizará eventos regulares de capacitação e ajudará
                    congregações em toda a região. Assim, o ciclo continua e a Grande Comissão avança.
                </p>
            </div>
        </div>
        </div>


        {{-- Palavra de encorajamento --}}
        <div class="py-12 max-w-8xl mx-auto">
            <div class="px-6 py-20 mx-auto text-center text-white 2xl:rounded-xl bg-sky-950">
                <div class="max-w-4xl mx-auto">

                    <h2 class="mb-6 font-serif text-3xl text-amber-300">Não desanime!</h2>
                    <p class="mb-4 font-bold leading-relaxed text-sky-50">
                        “Pessoas bem mentoreadas compartilharão sua fé com clareza e amor.” <br> — Dr. D. James Kennedy.
                    </p>
                    <p class="leading-relaxed text-sky-200">
                        Se a caminhada parecer longa, lembre-se que o Senhor da seara está no controle. Ele usa pessoas
                        imperfeitas que confiam em Sua graça. Continue orando, treinando e multiplicando.
                    </p>
                    <p class="leading-relaxed text-sky-200">
                        Seus discipulos agradecerão.
                    </p>
                </div>
            </div>
        </div>

        {{-- Chamada à ação --}}
        <div class="py-16 bg-white">
            <div class="container px-6 mx-auto text-center">
                <h3 class="mb-4 font-serif text-2xl text-blue-900">
                    Pronto para começar?
                </h3>
                <p class="mb-6 text-gray-700">
                    Nossa equipe está pronta para orientar sua igreja em cada etapa. Entre em contato e marque uma
                    conversa sem compromisso.
                </p>

                {{-- Botão de WhatsApp: ao clicar neste botão deve acionar a caixa do WhatsApp --}}
                <button type="button" data-open-wa
                    class="inline-flex items-center gap-3 px-8 py-4 font-semibold text-white transition rounded shadow bg-amber-500 hover:bg-amber-600"
                    style="text-shadow: 1px 1px 0 #000">
                    Falar conosco
                </button>

            </div>
        </div>

        </x-webweb.container>

</x-layouts.guest>
