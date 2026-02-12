@php
    $title = 'Agendar Evento de Lancamento';
    $description =
        'Saiba como agendar treinamentos do Evangelismo Explosivo em sua igreja e capacitar sua comunidade para compartilhar a fe de forma eficaz.';
    $keywords = 'base de treinamento, evangelismo explosivo, implementacao, discipulado, mentoria';
    $ogImage = asset('images/leadership-meeting.webp');
@endphp

<x-layouts.guest :title="$title" :description="$description" :keywords="$keywords" :ogImage="$ogImage">
    <x-web.header :title="$title" subtitle="Datas disponiveis para agendar treinamentos em sua igreja"
        :cover="asset('images/leadership-meeting.webp')" />

</x-layouts.guest>
