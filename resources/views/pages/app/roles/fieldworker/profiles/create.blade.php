<x-layouts.app :title="__('Criar Perfil')">
    <ul class="flex gap-6 flex-wrap">
        <li><a href="{{ route('app.director.church.show', $church) }}">Visualizar Igreja</a></li>
    </ul>
    <hr>
    <livewire:pages.app.director.profile.create :church="$church" />
</x-layouts.app>
