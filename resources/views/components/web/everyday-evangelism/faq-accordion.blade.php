@php
    $faqs = [
        [
            'question' => 'O que é o Evangelismo Eficaz?',
            'answer' =>
                'Evangelismo Eficaz é uma metodologia do Evangelismo Explosivo que capacita a igreja local a compartilhar o Evangelho como <strong>estilo de vida</strong>. Une ensino bíblico claro, prática supervisionada e acompanhamento por mentores, formando discípulos que evangelizam de maneira natural, intencional e multiplicadora.',
        ],
        [
            'question' => 'Em que o Evangelismo Eficaz é diferente de outras abordagens de evangelismo?',
            'answer' =>
                'O Evangelismo Eficaz não se limita a eventos ou campanhas pontuais. Ele desenvolve uma cultura permanente de testemunho, onde os participantes aprendem fazendo, sempre acompanhados por mentores capacitados. O foco está na <strong>formação de discípulos que se multiplicam</strong>, promovendo crescimento saudável e exponencial.',
        ],
        [
            'question' => 'O Evangelismo Eficaz é apenas para líderes ou pastores?',
            'answer' =>
                'Não. O Evangelismo Eficaz foi desenvolvido para capacitar <strong>todos os membros da igreja</strong>. Pastores e líderes são preparados como facilitadores, mas cada participante é treinado para viver o evangelismo como parte de sua vida cristã cotidiana.',
        ],
        [
            'question' => 'O que significa “evangelizar como estilo de vida”?',
            'answer' =>
                'Significa integrar amizade, testemunho, discipulado e acompanhamento no cotidiano. No Evangelismo Eficaz, cada cristão aprende a compartilhar sua fé de forma natural e bíblica, aproveitando as oportunidades que Deus cria, com o objetivo de alcançar pessoas <strong>até que todos ouçam</strong>.',
        ],
        [
            'question' => 'Como funciona o treinamento no Evangelismo Eficaz?',
            'answer' =>
                'O treinamento combina <strong>ensino em sala</strong> com <strong>Saídas de Treinamento Prático (STP)</strong>. Os alunos nunca saem sozinhos: são sempre acompanhados por mentores previamente capacitados, que demonstram na prática como conduzir uma conversa evangelística, discipular novos convertidos e formar novos multiplicadores.',
        ],
        [
            'question' => 'O Evangelismo Eficaz substitui outros ministérios da igreja?',
            'answer' =>
                'Não. O Evangelismo Eficaz foi desenvolvido para <strong>fortalecer a igreja no cumprimento da Grande Comissão</strong>, complementando os demais ministérios. Ele integra evangelismo, discipulado e crescimento saudável à vida da igreja, sem competir com outras áreas.',
        ],
        [
            'question' => 'Minha igreja pode implementar o Evangelismo Eficaz?',
            'answer' =>
                'Sim. O Evangelismo Eficaz foi desenvolvido para ser implementado na igreja local, respeitando sua liderança, doutrina e contexto. O ministério oferece clínicas, treinamentos e acompanhamento para capacitar facilitadores e apoiar a implementação de forma organizada e sustentável.',
        ],
        [
            'question' => 'Como posso participar de uma Clínica de Evangelismo Eficaz?',
            'answer' =>
                'Você pode participar por meio das <strong>Clínicas de Evangelismo Eficaz</strong>, onde líderes e membros são treinados no método e preparados para implementar o Evangelismo Eficaz em sua igreja. Consulte a agenda de eventos ou entre em contato para saber sobre as próximas clínicas disponíveis.',
        ],
    ];
@endphp

<div class="py-12 ee-metal-section">

    {{-- Conteúdo central com largura estável --}}
    <div class="w-full max-w-4xl mx-auto">

        {{-- Título --}}
        <header class="py-8 mb-8 text-center">
            <h2 class="relative flex justify-center text-2xl sm:text-3xl lg:text-4xl text-slate-900"
                style="font-family:'Cinzel', serif;">
                Perguntas frequentes
                {{-- Linha temática (dourado metálico) --}}
                <span
                    class="absolute -bottom-2 h-[2px] w-[min(28rem,100%)]
                           bg-linear-to-r from-[#f1d57a] via-[#c7a840] to-[#8a7424] opacity-90">
                </span>
            </h2>

            <p class="max-w-2xl mx-auto mt-3 text-sm sm:text-base text-slate-600">
                Respostas objetivas sobre o Evangelismo Explosivo, treinamentos e como sua igreja pode participar.
            </p>
        </header>

        {{-- Accordion: largura NÃO varia (tudo fica dentro do max-w-4xl) --}}
        <div class="space-y-4">

            @foreach ($faqs as $i => $faq)
                <article x-data="{ open: false }"
                    class="w-full overflow-hidden bg-white border shadow-sm rounded-2xl border-amber-500/20 ring-1 ring-black/5 shadow-black/5">

                    {{-- Pergunta (botão) --}}
                    <button type="button" @click="open = !open"
                        class="flex items-start justify-between w-full gap-4 px-5 py-5 text-left transition hover:bg-amber-50/40 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-400/40">

                        {{-- Texto pergunta com largura controlada --}}
                        <span class="block text-base font-semibold leading-snug sm:text-lg text-slate-900">
                            {{ $faq['question'] }}
                        </span>

                        {{-- Ícone (não “empurra” largura) --}}
                        <span
                            class="shrink-0 mt-0.5 flex items-center justify-center w-9 h-9 rounded-full
                                   border border-amber-500/25 bg-white text-amber-700
                                   shadow-sm shadow-black/5
                                   transition-transform duration-300"
                            :class="open ? 'rotate-180 bg-amber-50' : ''" aria-hidden="true">

                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </span>
                    </button>

                    {{-- Resposta --}}
                    <div x-show="open" x-collapse class="px-5 pb-6">

                        <div class="pt-4 border-t border-amber-500/15">
                            <div
                                class="prose prose-slate max-w-none prose-p:leading-relaxed prose-p:my-3 prose-a:text-amber-700 prose-a:font-semibold hover:prose-a:text-amber-800">
                                {!! $faq['answer'] !!}
                            </div>
                        </div>
                    </div>

                </article>
            @endforeach

        </div>

        {{-- Rodapé discreto --}}
        <div class="mt-10 text-center text-sky-950">
            Se sua dúvida não estiver aqui, fale conosco. Será um prazer ajudar.
        </div>

    </div>

</div>
