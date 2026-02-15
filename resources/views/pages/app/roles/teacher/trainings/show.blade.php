@php
    use App\Helpers\DayScheduleHelper;

    $training->loadMissing([
        'eventDates' => fn($query) => $query->orderBy('date')->orderBy('start_time'),
        'scheduleItems' => fn($query) => $query->orderBy('date')->orderBy('starts_at')->orderBy('position'),
    ]);

    $hasScheduleError = !DayScheduleHelper::hasAllDaysMatch($training->eventDates, $training->scheduleItems);
@endphp

<x-layouts.app :title="__('Treinamento')">
    <div x-data="{ showDeleteModal: false }" x-on:keydown.escape.window="showDeleteModal = false">
        <x-src.toolbar.bar :title="__('Detalhes do treinamento')" :description="__('Acompanhe informações, agenda e participantes do treinamento selecionado.')">
            <x-src.toolbar.button :href="route('app.teacher.trainings.index')" :label="__('Listar todos')" icon="list" :tooltip="__('Lista de treinamentos')" />
            <span class="mx-1 h-7 w-px bg-slate-300/80"></span>
            <x-src.toolbar.button :href="route('app.teacher.trainings.schedule', $training)" :label="__('Programação')" icon="calendar" :tooltip="__('Programação do evento')"
                :error="$hasScheduleError" />
            <x-src.toolbar.button :href="route('app.teacher.trainings.ojt.sessions.index', $training)" :label="__('OJT')" icon="users-chat" :tooltip="__('On-The-Job Training')" />
            <span class="mx-1 h-7 w-px bg-slate-300/80"></span>
            <x-src.toolbar.button href="#" :label="__('Excluir')" icon="trash" :tooltip="__('Excluir treinamento')"
                x-on:click.prevent="showDeleteModal = true" />
        </x-src.toolbar.bar>

        <livewire:pages.app.teacher.training.view :training="$training" />

        <div x-cloak x-show="showDeleteModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4"
            x-on:click.self="showDeleteModal = false">
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl dark:bg-slate-900">
                <form method="POST" action="{{ route('app.teacher.trainings.destroy', $training) }}"
                    class="flex flex-col gap-6">
                    @csrf
                    @method('DELETE')

                    <div class="space-y-2">
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                            {{ __('Confirmar exclusão') }}
                        </h2>
                        <p class="text-sm text-slate-600 dark:text-slate-300">
                            {{ __('Esta ação é permanente e removerá todas as informações do treinamento.') }}
                        </p>
                    </div>

                    <div class="flex items-center justify-end gap-3">
                        <button type="button" class="text-sm font-semibold text-slate-600 dark:text-slate-300"
                            x-on:click="showDeleteModal = false">
                            {{ __('Cancelar') }}
                        </button>
                        <button type="submit"
                            class="inline-flex items-center justify-center rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-700">
                            {{ __('Excluir') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
