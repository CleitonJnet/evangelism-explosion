<x-layouts.app :title="__('Editar Perfil')">
    <ul class="flex gap-6 flex-wrap">
        <li><a href="{{ route('app.director.church.show', $church) }}">Visualizar Igreja</a></li>
        <li><a href="{{ route('app.director.church.profile.show', ['church' => $church, 'profile' => $profile]) }}">Visualizar
                Perfil</a></li>
    </ul>
    <hr>
    <livewire:pages.app.director.profile.edit :profile="$profile" />
</x-layouts.app>
