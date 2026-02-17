@php
    use App\Helpers\DayScheduleHelper;

    $training->loadMissing([
        'eventDates' => fn($query) => $query->orderBy('date')->orderBy('start_time'),
        'scheduleItems' => fn($query) => $query->orderBy('date')->orderBy('starts_at')->orderBy('position'),
        'students' => fn($query) => $query->with('church_temp:id,status'),
    ]);

    $hasScheduleError = !DayScheduleHelper::hasAllDaysMatch($training->eventDates, $training->scheduleItems);
    $hasRegistrationsError = $training->students->contains(function ($student): bool {
        $hasNoChurch = $student->church_id === null && $student->church_temp_id === null;
        $hasPendingChurchValidation = $student->church_id === null && $student->church_temp?->status === 'pending';

        return $hasNoChurch || $hasPendingChurchValidation;
    });
@endphp

<x-layouts.app :title="__('Treinamento')">
    <div x-data="{ showDeleteModal: false }" x-on:keydown.escape.window="showDeleteModal = false">
        <x-src.toolbar.header :title="__('Detalhes do treinamento')" :description="__('Acompanhe informações, agenda e participantes do treinamento selecionado.')" />
        <x-src.toolbar.nav>
            <x-src.toolbar.button :href="route('app.teacher.trainings.index')" :label="__('Listar todos')" icon="list" :tooltip="__('Lista de treinamentos')" />
            <span class="mx-1 h-7 w-px bg-slate-300/80"></span>
            <x-src.toolbar.button :href="route('app.teacher.trainings.registrations', $training)" :label="__('Inscrições')" icon="user-work" :tooltip="__('Gerenciador de Inscrições')"
                :error="$hasRegistrationsError" />
            <x-src.toolbar.button :href="route('app.teacher.trainings.schedule', $training)" :label="__('Programação')" icon="calendar" :tooltip="__('Programação do evento')"
                :error="$hasScheduleError" />
            <x-src.toolbar.button href="#" :label="__('Mentores')" icon="user-group"
                :tooltip="__('Gerenciador de mentores')"
                x-on:click.prevent="$dispatch('open-manage-mentors-modal', { trainingId: {{ $training->id }} })" />
            <x-src.toolbar.button :href="'#'" :label="__('OJT')" icon="users-chat" :tooltip="__('On-The-Job Training')" />
            <span class="mx-1 h-7 w-px bg-slate-300/80"></span>
            <x-src.toolbar.button href="#" :label="__('Excluir')" icon="trash" :tooltip="__('Excluir treinamento')"
                x-on:click.prevent="showDeleteModal = true" />
        </x-src.toolbar.nav>

        <livewire:pages.app.teacher.training.view :training="$training" />
        <livewire:pages.app.teacher.training.manage-mentors-modal :trainingId="$training->id"
            wire:key="manage-mentors-modal-{{ $training->id }}" />
        <livewire:pages.app.teacher.training.create-mentor-user-modal :trainingId="$training->id"
            wire:key="create-mentor-user-modal-{{ $training->id }}" />

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
