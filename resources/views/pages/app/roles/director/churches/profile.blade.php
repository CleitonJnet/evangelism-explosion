<x-layouts.app :title="$profile->name ?: __('Perfil do participante')">
    <livewire:shared.church-user-profile :user="$profile"
        :back-url="$profile->church
            ? route('app.director.church.show', $profile->church)
            : route('app.director.church.index')"
        :delete-redirect-url="$profile->church
            ? route('app.director.church.show', $profile->church)
            : route('app.director.church.index')"
        :back-label="$profile->church ? __('Voltar para igreja') : __('Voltar para igrejas')" />
</x-layouts.app>
