<x-layouts.app :title="__('Listar Testemunhos')">
    <x-src.toolbar.header :title="__('Gerenciamento de Testemunhos')" :description="__(
        'Gerencie os testemunhos exibidos na secao publica do site, com controle de status e ordem de apresentacao.',
    )" />
    <x-src.toolbar.nav>
        <div id="director-testimonials-toolbar" class="flex flex-wrap items-center gap-2"></div>
    </x-src.toolbar.nav>

    <section
        class="rounded-2xl border border-amber-300/20 bg-linear-to-br from-slate-100 via-white to-slate-200 p-6 shadow-lg">
        <livewire:pages.app.director.website.testimonials.index />
    </section>
</x-layouts.app>
