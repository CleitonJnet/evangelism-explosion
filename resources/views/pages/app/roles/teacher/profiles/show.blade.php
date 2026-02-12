<x-layouts.app :title="__('Visualizar Perfil')">
    <ul class="flex gap-6 flex-wrap">
        <li><a href="{{ route('app.teacher.church.show', $church) }}">Visualizar Igreja</a></li>
        <li><a href="{{ route('app.teacher.church.profile.edit', ['church' => $church, 'profile' => $profile]) }}">Editar
                Perfil</a></li>
    </ul>
    <hr>
    {{-- <livewire:pages.app.teacher.profile.view :profile="$profile" /> --}}
</x-layouts.app>
