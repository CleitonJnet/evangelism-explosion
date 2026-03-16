@php
    // Metadados para a Home
    $title = 'Home - Treinamentos, Evangelismo e Discipulado';
    $description =
        'Evangelismo Explosivo (EE) no Brasil capacita igrejas a evangelizar com clareza, discipular com fidelidade e multiplicar líderes.';
    $keywords = 'evangelismo, discipulado, treinamento, evangelismo explosivo, EE Brasil';
    $ogImage = asset('images/og/home.webp');
@endphp

<x-layouts.guest :title="$title" :description="$description" :keywords="$keywords" :ogImage="$ogImage">
    <x-web.home.hero />
    <x-web.home.portals :portal-cards="$portalCards" />
    <x-web.home.about />
    <x-web.home.list-events />
    @livewire('web.home.testimonials')
    <x-web.home.gallery-instagram />
    <x-web.home.faq-accordion />
</x-layouts.guest>
