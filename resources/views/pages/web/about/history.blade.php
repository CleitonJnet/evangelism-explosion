@php
    // Metadados para a página de história
    $title = 'A História do Evangelismo Explosivo';
    $description =
        'Conheça a história do Evangelismo Explosivo, iniciado em 1962 pelo pastor D. James Kennedy, um ministério que cresceu de uma pequena congregação para uma obra global que treina cristãos para evangelizar e discipular.';
    $keywords = 'história evangelismo explosivo, D. James Kennedy, testemunho, crescimento da igreja, evangelismo';
    $ogImage = asset('images/3rd_nations_congress_2016.webp'); // Ajuste o caminho conforme sua estrutura de imagens
@endphp

<x-layouts.guest :title="$title" :description="$description" :keywords="$keywords" :ogImage="$ogImage">
    <x-web.header title="A História do <span class='text-nowrap'>Evangelismo Explosivo</span>"
        subtitle='Da crise pastoral ao ministério global' :cover="asset('images/3rd_nations_congress_2016.webp')" />

    <x-web.container>
        <div class="space-y-16">
            {{-- Marco 1959–1960: A crise e o encontro transformador --}}
            <div class="flex flex-col items-start gap-8 lg:flex-row lg:items-center">
                <div class="order-2 lg:w-1/2 lg:order-1">
                    <h2 class="mb-3 text-2xl font-semibold text-yellow-700">1959–1960: Da crise pastoral ao encontro
                        transformador</h2>
                    <p class="mb-4 text-justify">
                        No final da década de 1950, o jovem pastor <strong>D. James Kennedy</strong>
                        assumiu um projeto missionário em Fort Lauderdale, na Flórida. Em
                        poucos meses, a congregação encolheu de cerca de <em>quarenta e cinco</em>
                        para apenas <em>dezessete</em> membros, e Kennedy confessaria que não tinha
                        confiança ou habilidade para abordar pessoas face a face.
                        Desanimado e buscando orientação, ele aceitou o convite para pregar em
                        Decatur, Geórgia. Ali, acompanhando o pastor local em visitas domiciliares,
                        testemunhou <em>cinquenta e quatro</em> pessoas entregando suas vidas a Cristo
                        em dez dias. Essa experiência o transformou:
                        Kennedy compreendeu que o evangelismo eficaz acontecia na vida real e
                        precisava ser aprendido no campo.
                    </p>
                </div>
                <div class="order-1 lg:w-1/2 lg:order-2">
                    <figure class="w-full">
                        <img src="{{ asset('images/photo3.jpg') }}"
                            alt="Pastor D. James Kennedy durante seus primeiros anos em Fort Lauderdale"
                            class="w-full h-auto border-t-4 border-r-4 border-white rounded-lg shadow-md"
                            style="box-shadow: 3px -3px 0 #c7a840" />
                        <figcaption class="mt-2 text-sm text-amber-800">
                            Pastor D. James Kennedy em Fort Lauderdale na época em que a igreja enfrentou a crise
                            inicial.
                        </figcaption>
                    </figure>
                </div>
            </div>

            {{-- Marco 1962: O nascimento do treinamento prático --}}
            <div class="flex flex-col items-start gap-8 lg:flex-row lg:items-center lg:flex-row-reverse">
                <div class="order-2 lg:w-1/2 lg:order-1">
                    <h2 class="mb-3 text-2xl font-semibold text-yellow-700">1962: Nasce o treinamento prático</h2>
                    <p class="mb-4 text-justify">
                        De volta à Flórida, Kennedy tentou por diversas vezes treinar toda a igreja
                        por meio de aulas teóricas de testemunho. O resultado foi decepcionante;
                        mesmo após 6, 12 ou 15 lições, ninguém evangelizava. Ele
                        percebeu que havia um “elo perdido”: assim como ele aprendera observando
                        alguém mais experiente, seus membros precisavam acompanhar um evangelista
                        em ação. Em 1962, passou a levar pequenos grupos em
                        visitas domiciliares; observavam, participavam e, por fim, lideravam a
                        apresentação do Evangelho. O método de <em>Treinamento Prático</em>
                        (On‑the‑Job Training) estava nascendo, e a ideia de <strong>multiplicação
                            espiritual</strong> substituía a simples adição de novos convertidos.
                    </p>
                </div>
                <div class="order-1 lg:w-1/2 lg:order-2">
                    <figure class="w-full">
                        <img src="{{ asset('images/photo2.jpg') }}"
                            alt="Grupo de leigos participando do treinamento prático em uma visita domiciliar"
                            class="w-full h-auto border-t-4 border-l-4 border-white rounded-lg shadow-md"
                            style="box-shadow: -3px -3px 0 #c7a840" />
                        <figcaption class="mt-2 text-sm text-amber-800">
                            Momento em que leigos acompanharam Kennedy nas primeiras visitas evangelísticas.
                        </figcaption>
                    </figure>
                </div>
            </div>

            {{-- Marco 1967–1970: Consolidação e expansão --}}
            <div class="flex flex-col items-start gap-8 lg:flex-row lg:items-center">
                <div class="order-2 lg:w-1/2 lg:order-1">
                    <h2 class="mb-3 text-2xl font-semibold text-yellow-700">1967–1970: Consolidação e expansão</h2>
                    <p class="mb-4 text-justify">
                        O sucesso do treinamento prático levou ao crescimento rápido da Igreja
                        Presbiteriana Coral Ridge. Em 1967, foi realizada a <strong>primeira clínica de
                            líderes</strong> para pastores de outras igrejas. O interesse
                        foi tão grande que, em 1970, a participação saltou para <em>350 pessoas</em>,
                        obrigando a recusar outras 1.500 por falta de espaço.
                        Para garantir a qualidade e a fidelidade do método, o ministério
                        estabeleceu um processo de certificação e, diante da ampla aceitação,
                        criou a organização <em>Evangelism Explosion International</em> como entidade
                        interdenominacional e internacional.
                    </p>
                </div>
                <div class="order-1 lg:w-1/2 lg:order-2">
                    <figure class="w-full">
                        <img src="{{ asset('images/photo4.jpg') }}"
                            alt="Primeiras clínicas de treinamento realizadas na Coral Ridge"
                            class="w-full h-auto border-t-4 border-r-4 border-white rounded-lg shadow-md"
                            style="box-shadow: 3px -3px 0 #c7a840" />
                        <figcaption class="mt-2 text-sm text-amber-800">
                            Foto ilustrativa das clínicas de liderança que começaram em 1967 e se expandiram em 1970.
                        </figcaption>
                    </figure>
                </div>
            </div>

            {{-- Marco 1973–1996: Impacto global --}}
            <div class="flex flex-col items-start gap-8 lg:flex-row lg:items-center lg:flex-row-reverse">
                <div class="order-2 lg:w-1/2 lg:order-1">
                    <h2 class="mb-3 text-2xl font-semibold text-yellow-700">1973–1996: Impacto global</h2>
                    <p class="mb-4 text-justify">
                        A partir da década de 1970, o Evangelismo Explosivo começou a se espalhar
                        pelo mundo. Clínicas foram realizadas na Europa, África, Austrália e
                        Ásia, e milhares de igrejas foram treinadas. Em 1988, com o ministério
                        presente em 66 nações, fixou-se a meta audaciosa de alcançar todos os
                        países. Em 1996, essa meta foi atingida: o E.E. entrou na Coreia do
                        Norte e se tornou o <em>primeiro ministério cristão presente em todos os
                            países do mundo</em>. Estima-se que já em 1997
                        cerca de 100.000 igrejas em 211 nações treinem seus leigos utilizando
                        os princípios deste ministério.
                    </p>
                </div>
                <div class="order-1 lg:w-1/2 lg:order-2">
                    <figure class="w-full">
                        <img src="{{ asset('images/photo1.jpg') }}"
                            alt="Representantes de várias nações celebrando o alcance global do E.E."
                            class="w-full h-auto border-t-4 border-l-4 border-white rounded-lg shadow-md"
                            style="box-shadow: -3px -3px 0 #c7a840" />
                        <figcaption class="mt-2 text-sm text-amber-800">
                            Celebração de 1996 na Coral Ridge com representantes de todas as nações.
                        </figcaption>
                    </figure>
                </div>
            </div>

            {{-- Marco Atualidade: Legado e visão futura --}}
            <div class="flex flex-col items-start gap-8 lg:flex-row lg:items-center">
                <div class="order-2 lg:w-1/2 lg:order-1">
                    <h2 class="mb-3 text-2xl font-semibold text-yellow-700">Atualidade e legado</h2>
                    <p class="mb-4 text-justify">
                        O Evangelismo Explosivo permanece, até hoje, um ministério baseado na
                        igreja local, adaptado culturalmente e centrado na <strong>multiplicação
                            de discípulos</strong>. Ao longo das décadas, ajustou-se para enfatizar o
                        evangelismo relacional e o discipulado contínuo, reconhecendo que
                        colher um fruto é importante, mas plantar uma árvore frutífera gera
                        uma colheita multiplicada. Atualmente, continua equipando cristãos
                        de todas as idades para compartilhar a fé com clareza, amor e
                        naturalidade.
                    </p>
                </div>
                <div class="order-1 lg:w-1/2 lg:order-2">
                    <figure class="w-full">
                        <img src="{{ asset('images/3rd_nations_congress_2016.webp') }}"
                            alt="Imagem contemporânea de uma igreja local envolvida com o E.E."
                            class="w-full h-auto border-t-4 border-r-4 border-white rounded-lg shadow-md"
                            style="box-shadow: 3px -3px 0 #c7a840" />
                        <figcaption class="mt-2 text-sm text-amber-800">
                            O E.E. hoje: igrejas e cristãos continuam sendo equipados para testemunhar e discipular.
                        </figcaption>
                    </figure>
                </div>
            </div>
        </div>
        </x-webweb.container>
</x-layouts.guest>
