<x-layouts.app :title="$profile->name ?: __('Perfil do participante')">
    <livewire:shared.church-user-profile :user="$profile"
        :back-url="$profile->church
            ? route('app.teacher.churches.show', $profile->church)
            : route('app.teacher.churches.index')"
        :delete-redirect-url="$profile->church
            ? route('app.teacher.churches.show', $profile->church)
            : route('app.teacher.churches.index')"
        :back-label="$profile->church ? __('Voltar para igreja') : __('Voltar para igrejas')" />
</x-layouts.app>
