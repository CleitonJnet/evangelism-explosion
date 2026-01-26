<x-layouts.app :title="__('Criar Perfil')">
    <ul class="flex gap-6 flex-wrap">
        <li><a href="{{ route('app.teacher.church.show', $church) }}">Visualizar Igreja</a></li>
    </ul>
    <hr>
    {{-- <livewire:pages.app.teacher.profile.create :church="$church" /> --}}
</x-layouts.app>
