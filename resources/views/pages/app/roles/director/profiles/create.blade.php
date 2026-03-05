<x-layouts.app :title="__('Criar Perfil')">
    <x-src.toolbar.header :title="__('Novo participante')" :description="__('Cadastre um novo participante vinculado à igreja selecionada.')" />
    <x-src.toolbar.nav>
        <x-src.toolbar.button :href="route('app.director.church.show', $church)" :label="__('Voltar para igreja')" icon="list"
            :tooltip="__('Detalhes da igreja')" />
    </x-src.toolbar.nav>

    <section
        class="rounded-2xl border border-amber-300/20 bg-linear-to-br from-slate-100 via-white to-slate-200 p-6 shadow-lg">
        <livewire:pages.app.director.profile.create :church="$church" />
    </section>
</x-layouts.app>
