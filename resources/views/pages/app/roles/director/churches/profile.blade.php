<x-layouts.app :title="$profile->name ?: __('Perfil do participante')">
    <livewire:shared.church-user-profile :user="$profile" :back-url="route('app.director.church.index')"
        :back-label="__('Voltar para igrejas')" />
</x-layouts.app>
