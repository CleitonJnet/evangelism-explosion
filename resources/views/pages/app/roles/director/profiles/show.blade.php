<x-layouts.app :title="__('Visualizar Perfil')">
    <x-src.toolbar.header :title="__('Detalhes do participante')" :description="__('Consulte os dados do participante vinculado à igreja.')" />
    <x-src.toolbar.nav>
        <x-src.toolbar.button :href="route('app.director.church.show', $church)" :label="__('Voltar para igreja')" icon="list"
            :tooltip="__('Detalhes da igreja')" />
        <x-src.toolbar.button :href="route('app.director.church.profile.edit', ['church' => $church, 'profile' => $profile])"
            :label="__('Editar')" icon="pencil" :tooltip="__('Editar participante')" />
    </x-src.toolbar.nav>

    <section
        class="rounded-2xl border border-amber-300/20 bg-linear-to-br from-slate-100 via-white to-slate-200 p-6 shadow-lg">
        <livewire:pages.app.director.profile.view :profile="$profile" />
    </section>
</x-layouts.app>
