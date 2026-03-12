@props(['chart'])

@php
    $help = match ($chart['id']) {
        'director-trainings-month' => [
            'what' => 'Mostra a evolucao dos eventos na janela escolhida, separando os volumes por status para revelar o ritmo real da operacao.',
            'how' => 'Leia da esquerda para a direita para ver quando houve aceleracao ou queda. Compare as cores para entender se o crescimento veio de eventos planejados, agendados, concluidos ou cancelados.',
        ],
        'director-registrations-month' => [
            'what' => 'Mostra quantas inscricoes entraram ao longo do tempo dentro do periodo filtrado.',
            'how' => 'Use os picos para identificar meses ou semanas de maior adesao. Se os eventos crescerem e as inscricoes nao acompanharem, isso indica necessidade de revisar mobilizacao e comunicacao.',
        ],
        'director-decisions-month' => [
            'what' => 'Mostra quantas decisoes espirituais foram registradas em cada etapa da janela analisada.',
            'how' => 'Compare os picos com os demais graficos para entender se o aumento veio de mais eventos ou de melhor efetividade ministerial. Se houver muitos inscritos e poucas decisoes, vale investigar a execucao do STP.',
        ],
        'director-new-churches-month' => [
            'what' => 'Mostra em quais momentos surgiram novas igrejas ligadas aos treinamentos realizados.',
            'how' => 'Observe a distribuicao no tempo para perceber se a expansao esta constante ou concentrada em poucos momentos. Isso ajuda a avaliar a consistencia do crescimento.',
        ],
        'director-distribution-course' => [
            'what' => 'Mostra como os treinamentos da janela se distribuem entre ministerios e cursos.',
            'how' => 'Cada fatia representa a participacao de um curso no total. Fatias muito grandes mostram concentracao; fatias pequenas mostram frentes com baixa presenca e possivel espaco para fortalecer.',
        ],
        'director-distribution-state' => [
            'what' => 'Mostra onde os treinamentos estao acontecendo por estado e como cada curso contribui nessa presenca.',
            'how' => 'Compare a altura total das barras para ver os estados mais ativos e as cores dentro de cada barra para entender quais cursos estao sustentando essa cobertura.',
        ],
        'director-ranking-teachers' => [
            'what' => 'Mostra os professores com maior volume de atuacoes no periodo analisado.',
            'how' => 'As barras maiores indicam mais carga operacional. Use esse ranking para identificar liderancas-chave, concentracao excessiva e necessidade de distribuir melhor os treinamentos.',
        ],
        'director-ranking-churches' => [
            'what' => 'Mostra quais igrejas mais enviaram inscritos na janela atual.',
            'how' => 'As primeiras posicoes revelam igrejas com maior mobilizacao. Compare com as menores participacoes para decidir onde reforcar relacionamento, acompanhamento e divulgacao.',
        ],
        'teacher-registrations-line' => [
            'what' => 'Mostra a evolucao das inscricoes nos treinamentos em que voce atua dentro da janela escolhida.',
            'how' => 'Leia a curva ao longo do tempo para ver quando as inscricoes aceleraram ou esfriaram. Isso ajuda a decidir quando intensificar convite, acompanhamento e confirmacao dos alunos.',
        ],
        'teacher-trainings-status' => [
            'what' => 'Mostra quantos treinamentos do seu escopo estao em cada status operacional.',
            'how' => 'Compare as barras para ver se sua agenda esta mais carregada de planejamentos, agendamentos, concluidos ou cancelados. Isso ajuda a priorizar o que precisa sair do papel e o que precisa ser fechado.',
        ],
        'teacher-financial-status' => [
            'what' => 'Mostra como as inscricoes do seu escopo se dividem entre pagantes, comprovantes pendentes e pendencias financeiras.',
            'how' => 'Use a proporcao das fatias para identificar onde esta o gargalo financeiro. Se a area de pendencias crescer, o melhor uso do grafico e agir no follow-up de comprovantes e pagamentos.',
        ],
        'teacher-stp-results' => [
            'what' => 'Mostra os principais resultados registrados no STP durante a janela escolhida.',
            'how' => 'Compare a altura das barras para ver onde o trabalho de campo esta produzindo mais fruto ou onde ha baixa movimentacao. Isso ajuda a corrigir foco e acompanhamento das saidas.',
        ],
        'teacher-discipleship-results' => [
            'what' => 'Mostra como esta o funil do discipulado: pessoas em acompanhamento, concluidas, encaminhadas e pendentes.',
            'how' => 'Leia cada barra como uma etapa da jornada. Muitas pessoas pendentes ou poucas concluidas indicam que o acompanhamento precisa de mais ritmo, mentoria ou repasse para a igreja local.',
        ],
        'teacher-church-ranking' => [
            'what' => 'Mostra quais igrejas mais enviaram alunos para os treinamentos do seu escopo.',
            'how' => 'As barras mais altas revelam igrejas com maior engajamento. Use isso para reconhecer parceiros fortes e para perceber quais igrejas ainda precisam de mais ativacao.',
        ],
        default => [
            'what' => 'Mostra um recorte visual dos dados mais relevantes da janela selecionada.',
            'how' => 'Compare tamanhos, volumes e variacoes ao longo do tempo para identificar rapidamente crescimento, queda, concentracao ou gargalos.',
        ],
    };

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
    <div class="mb-4 flex items-start justify-between gap-3">
        <h2 class="text-lg font-semibold {{ $tone['title'] }}">{{ $chart['title'] }}</h2>

        <x-dashboard.help-tooltip :title="'Como ler: '.$chart['title']" :what="$help['what']" :how="$help['how']"
            class="shrink-0" />
    </div>

    <div data-dashboard-chart data-chart-id="{{ $chart['id'] }}"
        data-chart-signature="{{ md5(json_encode($chart, JSON_THROW_ON_ERROR)) }}" class="relative"
        style="height: {{ $chart['height'] ?? 320 }}px;">
        <canvas data-dashboard-chart-canvas aria-label="{{ $chart['title'] }}" role="img"></canvas>
        <script type="application/json" data-dashboard-chart-payload>@json($chart)</script>
    </div>
</article>
