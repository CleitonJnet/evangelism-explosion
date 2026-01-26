<x-layouts.app :title="__('Criar Treinamento')">
    <div class="mb-6">
        <x-src.toolbar.bar>
            <div class="w-full">
                <h1 class="text-xl font-semibold text-slate-900">
                    {{ __('Novo treinamento') }}
                </h1>
                <p class="text-sm text-slate-600">
                    {{ __('Cadastre as informações do treinamento e organize a agenda do evento.') }}
                </p>
            </div>
            <div class="h-px w-full bg-slate-200/90"></div>
            <div class="flex flex-wrap items-center gap-2 text-sm text-slate-700">
                <x-src.toolbar.button :href="route('app.teacher.training.index')" :label="__('Listar todos')" icon="list" :tooltip="__('Lista de treinamentos')" />
            </div>
        </x-src.toolbar.bar>
    </div>
    <livewire:pages.app.teacher.training.create />
</x-layouts.app>
