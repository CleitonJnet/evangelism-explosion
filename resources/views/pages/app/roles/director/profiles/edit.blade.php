<x-layouts.app :title="__('Editar Perfil')">
    <x-src.toolbar.header :title="__('Editar participante')" :description="__('Atualize os dados do participante vinculado à igreja.')" />
    <x-src.toolbar.nav>
        <x-src.toolbar.button :href="route('app.director.church.show', $church)" :label="__('Voltar para igreja')" icon="list"
            :tooltip="__('Detalhes da igreja')" />
        <x-src.toolbar.button :href="route('app.director.church.profile.show', ['church' => $church, 'profile' => $profile])"
            :label="__('Detalhes')" icon="eye" :tooltip="__('Detalhes do participante')" />
    </x-src.toolbar.nav>

    <section
        class="rounded-2xl border border-amber-300/20 bg-linear-to-br from-slate-100 via-white to-slate-200 p-6 shadow-lg">
        <livewire:pages.app.director.profile.edit :profile="$profile" />
    </section>
</x-layouts.app>
