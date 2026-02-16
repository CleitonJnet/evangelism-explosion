<x-layouts.app :title="__('Listar Testemunhos')">
    <x-src.toolbar.header :title="__('Gerenciamento de Testemunhos')" :description="__(
        'Gerencie os testemunhos exibidos na secao publica do site, com controle de status e ordem de apresentacao.',
    )" />
    <x-src.toolbar.nav>
        <div id="director-testimonials-toolbar" class="flex flex-wrap items-center gap-2"></div>
    </x-src.toolbar.nav>

    <livewire:pages.app.director.website.testimonials.index />
</x-layouts.app>
