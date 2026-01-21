@php
    $title = __('Evangelismo Eficaz');
    $description =
        'Escola de Evangelismo e Discipulado para Jovens e Adultos, voltada à formação de um estilo de vida evangelizador';
    $keywords =
        'evangelismo, evangelismo eficaz, compartilhar a fé, evangelismo no dia a dia, estratégias de evangelismo';
    $ogImage = asset('images/leadership-meeting.webp');
@endphp

<x-layouts.guest :title="$title" :description="$description" :keywords="$keywords" :ogImage="$ogImage">

    <div class="">
        <x-web.header :title="$title"
            subtitle='Escola de Evangelismo e Discipulado para Jovens e Adultos, voltada à formação de um estilo de vida evangelizador'
            :cover="asset('images/leadership-meeting.webp')" />

        <x-web.everyday-evangelism.hero />
        <x-web.everyday-evangelism.about />
        <x-web.everyday-evangelism.parts />
        <x-web.everyday-evangelism.methodology />
        <x-web.everyday-evangelism.clinic />
        <x-web.everyday-evangelism.list-events />
        <x-web.everyday-evangelism.faq-accordion />
        <x-web.everyday-evangelism.cta />

    </div>

</x-layouts.guest>

@push('js')
    {{-- JS puro: smooth scroll + (opcional) apenas um FAQ aberto por vez --}}
    <script>
        (function() {
            // Smooth scroll para âncoras internas (sem dependências)
            document.addEventListener('click', function(e) {
                const a = e.target.closest('a[href^="#"]');
                if (!a) return;

                const id = a.getAttribute('href');
                if (!id || id === '#') return;

                const target = document.querySelector(id);
                if (!target) return;

                e.preventDefault();
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });

                // Atualiza URL sem "pular" a tela
                history.pushState(null, '', id);
            });

            // FAQ: manter só 1 <details> aberto por vez (opcional e elegante)
            const detailsList = document.querySelectorAll('#faq details');
            detailsList.forEach(d => {
                d.addEventListener('toggle', () => {
                    if (!d.open) return;
                    detailsList.forEach(other => {
                        if (other !== d) other.open = false;
                    });
                });
            });
        })();
    </script>
@endpush
