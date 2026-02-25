@php
    $training->loadMissing('course.ministry');

    $eventTitle = trim(
        implode(' ', array_filter([
            $training->course?->type,
            $training->course?->name,
        ])),
    );
    $ministryName = $training->course?->ministry?->name ?: __('Ministério não informado');
@endphp

<div>
    <x-src.toolbar.header wire:ignore :title="__('Saidas de Treinamento Praticos')" :description="__('Detalhes sobre as saidas de Treinamento Pratico.')">
        <x-slot:right>
            <div class="hidden px-1 py-2 text-right md:block">
                <div class="text-sm font-bold text-slate-800">
                    {{ $eventTitle !== '' ? $eventTitle : __('Evento sem nome') }}
                </div>
                <div class="text-xs font-light text-slate-600">
                    {{ $ministryName }}
                </div>
            </div>
        </x-slot:right>
    </x-src.toolbar.header>
    <x-src.toolbar.nav>
        <x-src.toolbar.button :href="route('app.teacher.trainings.show', $training)" :label="__('Detalhes do Evento')" icon="eye" :tooltip="__('Voltar para o Treinamento')" />
        <x-src.toolbar.button :href="route('app.teacher.trainings.stp.approaches', $training)" :label="__('Visitas')" icon="list" :tooltip="__('Distribuição de visitas STP')" />
        <x-src.toolbar.button href="#" :label="__('Mentores')" icon="user-group" :tooltip="__('Gerenciador de mentores')"
            x-on:click.prevent="$dispatch('open-manage-mentors-modal', { trainingId: {{ $training->id }} })">
            <div class="absolute -top-2.5 -right-0.5 text-blue-800 z-20 text-base bg-white/75 rounded px-1">
                {{ $mentorsCount }}
            </div>
        </x-src.toolbar.button>
        <span class="mx-1 h-7 w-px bg-slate-300/80"></span>
    </x-src.toolbar.nav>

    <div
        class="w-full overflow-x-auto bg-linear-to-br from-slate-100 via-white to-slate-200 p-4 rounded-2xl sticky top-0">
        <div class="mb-4 flex flex-wrap items-start justify-between gap-2">
            <div class="flex flex-wrap items-center gap-2">
                <label for="stp-session-select" class="text-xs font-semibold text-slate-700">Sessão STP:</label>
                <select id="stp-session-select" class="h-9 rounded-lg border border-slate-300 bg-white px-3 text-sm"
                    wire:change="selectSession($event.target.value)">
                    <option value="">Selecione</option>
                    @foreach ($sessions as $session)
                        <option value="{{ $session['id'] }}" @selected($activeSessionId === $session['id'])>
                            {{ $session['label'] }}
                        </option>
                    @endforeach
                </select>

                <button type="button"
                    class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50"
                    wire:click="createSession" @disabled(!$canCreateSession) title="{{ $createSessionBlockedReason ?? '' }}">
                    Criar sessão STP
                </button>

                @if ($activeSessionId !== null && count($teams) === 0)
                    <button type="button"
                        class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                        wire:click="formTeams">
                        Formar equipes
                    </button>
                @endif

                @if ($activeSessionId !== null && $isLeadershipExecutionTraining && $canRandomizeTeams)
                    <button type="button"
                        class="inline-flex items-center gap-2 rounded-lg border border-indigo-300 bg-indigo-50 px-3 py-2 text-sm font-semibold text-indigo-700 hover:bg-indigo-100"
                        wire:click="randomizeTeams">
                        Redefinir equipes
                    </button>
                @endif

                @if ($activeSessionId !== null && count($teams) > 0)
                    <button type="button"
                        class="inline-flex items-center gap-2 rounded-lg border border-emerald-300 bg-emerald-50 px-3 py-2 text-sm font-semibold text-emerald-700 hover:bg-emerald-100"
                        wire:click="createRandomTeam">
                        Nova equipe
                    </button>
                @endif

                @if (count($pendingStudents) > 0)
                    <span
                        class="inline-flex items-center rounded-lg bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800">
                        Pendências STP: {{ count($pendingStudents) }}
                    </span>
                @endif
            </div>

            @if ($activeSessionId !== null)
                <button type="button"
                    class="inline-flex items-center gap-2 rounded-lg border border-red-300 bg-red-50 px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-100"
                    wire:click="requestSessionRemoval({{ $activeSessionId }})">
                    Remover sessão
                </button>
            @endif
        </div>

        @if (!$canCreateSession && $createSessionBlockedReason)
            <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-700">
                {{ $createSessionBlockedReason }}
            </div>
        @endif

        @error('sessionCreation')
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
                {{ $message }}
            </div>
        @enderror

        @error('teamFormation')
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
                {{ $message }}
            </div>
        @enderror

        @error('teamCreation')
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
                {{ $message }}
            </div>
        @enderror

        <div class="min-w-280 overflow-hidden rounded-xl">
            <table
                class="w-full table-fixed text-xs text-black rounded-xl [&_tr>*:first-child]:border-l-0 [&_tr>*:last-child]:border-r-0 [&_thead_tr:first-child>*]:border-t-0 [&_tfoot_tr:last-child>*]:border-b-0">
                <colgroup>
                    <col class="w-8">
                    <col class="w-24">
                    <col class="w-54">
                    @for ($i = 0; $i < 12; $i++)
                        <col class="w-10">
                    @endfor
                </colgroup>

                <thead>
                    <tr>
                        <th rowspan="2" colspan="3"
                            class="border border-b border-y-white border-x-white px-1.5 py-1 bg-yellow-50 align-bottom">
                            <div class="h-55 flex items-end justify-center pb-2 font-semibold">
                                Integrantes das Equipes
                            </div>
                        </th>

                        <th colspan="4"
                            class="border border-b border-y-white border-x-white px-1.5 py-1 bg-green-100 text-center font-semibold">
                            Tipo de Contato
                        </th>

                        <th colspan="2"
                            class="border border-b border-y-white border-x-white px-1.5 py-1 bg-fuchsia-200 text-center font-semibold">
                            Evangelho Explicado
                        </th>

                        <th colspan="4"
                            class="border border-b border-y-white border-x-white px-1.5 py-1 bg-red-100 text-center font-semibold">
                            Resultado
                        </th>

                        <th colspan="2"
                            class="border border-b border-y-white border-x-white px-1.5 py-1 bg-blue-100 text-center font-semibold">
                            Acompanha<br>mento
                        </th>
                    </tr>

                    <tr>
                        @foreach ($typeContactLabels as $label)
                            <th
                                class="border border-b-4 border-y-white {{ $loop->first ? 'border-l-white border-r-green-300' : ($loop->last ? 'border-l-green-300 border-r-white' : 'border-x-green-300') }} bg-green-100 p-0 align-bottom">
                                <div class="h-55 flex items-end py-2 justify-center px-1">
                                    <span class="[writing-mode:vertical-rl] rotate-180 whitespace-nowrap leading-none">
                                        {{ $label }}
                                    </span>
                                </div>
                            </th>
                        @endforeach

                        @foreach ($gospelLabels as $label)
                            <th
                                class="border border-b-4 border-y-white {{ $loop->first ? 'border-l-white border-r-fuchsia-300' : ($loop->last ? 'border-l-fuchsia-300 border-r-white' : 'border-x-fuchsia-300') }} bg-fuchsia-200 p-0 align-bottom">
                                <div class="h-55 flex items-end py-2 justify-center px-1">
                                    <span class="[writing-mode:vertical-rl] rotate-180 whitespace-nowrap leading-none">
                                        {{ $label }}
                                    </span>
                                </div>
                            </th>
                        @endforeach

                        @foreach ($resultLabels as $label)
                            <th
                                class="border border-b-4 border-y-white {{ $loop->first ? 'border-l-white border-r-red-300' : ($loop->last ? 'border-l-red-300 border-r-white' : 'border-x-red-300') }} bg-red-100 p-0 align-bottom">
                                <div class="h-55 flex items-end py-2 justify-center px-1">
                                    <span class="[writing-mode:vertical-rl] rotate-180 whitespace-nowrap leading-none">
                                        {{ $label }}
                                    </span>
                                </div>
                            </th>
                        @endforeach

                        @foreach ($followUpLabels as $label)
                            <th
                                class="border border-b-4 border-y-white {{ $loop->first ? 'border-l-white border-r-blue-300' : ($loop->last ? 'border-l-blue-300 border-r-white' : 'border-x-blue-300') }} bg-blue-100 p-0 align-bottom">
                                <div class="h-55 flex items-end py-2 justify-center px-1">
                                    <span class="[writing-mode:vertical-rl] rotate-180 whitespace-nowrap leading-none">
                                        {{ $label }}
                                    </span>
                                </div>
                            </th>
                        @endforeach
                    </tr>
                </thead>

                <tbody>
                    @if ($activeSessionId === null)
                        <tr>
                            <td colspan="15"
                                class="border border-white bg-white px-4 py-6 text-center text-sm text-slate-500">
                                Nenhuma sessão STP criada.
                            </td>
                        </tr>
                    @elseif (count($teams) === 0)
                        <tr>
                            <td colspan="15"
                                class="border border-white bg-white px-4 py-6 text-center text-sm text-slate-500">
                                Sessão selecionada sem equipes formadas.
                            </td>
                        </tr>
                    @else
                        @foreach ($teams as $team)
                            <tr class="h-10 relative group" wire:key="team-{{ $team['id'] }}">
                                <th
                                    class="relative border border-y-4 border-y-white border-l-white border-r-yellow-300 bg-yellow-50 group-hover:bg-yellow-100 px-1 text-center">
                                    <span class="group-hover:opacity-0 transition-opacity">
                                        {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}.
                                    </span>
                                    <button type="button"
                                        class="absolute inset-0 hidden h-full w-full items-center justify-center bg-sky-100 text-sky-700 group-hover:flex hover:bg-red-100 hover:text-red-600 cursor-pointer"
                                        wire:click="requestTeamRemoval({{ $team['id'] }})"
                                        aria-label="{{ __('Remover equipe') }}" title="{{ __('Remover equipe') }}">
                                        <svg version="1.1" id="remove-team-icon" xmlns="http://www.w3.org/2000/svg"
                                            class="h-5 w-5 fill-red-500" xmlns:xlink="http://www.w3.org/1999/xlink"
                                            viewBox="0 0 459.739 459.739" xml:space="preserve">
                                            <path
                                                d="M229.869,0C102.917,0,0,102.917,0,229.869c0,126.952,102.917,229.869,229.869,229.869s229.869-102.917,229.869-229.869 C459.738,102.917,356.821,0,229.869,0z M313.676,260.518H146.063c-16.926,0-30.649-13.723-30.649-30.649 c0-16.927,13.723-30.65,30.649-30.65h167.613c16.925,0,30.649,13.723,30.649,30.65C344.325,246.795,330.601,260.518,313.676,260.518 z" />
                                        </svg>
                                    </button>
                                </th>

                                <td
                                    class="border border-y-4 border-y-white border-l-yellow-300 border-r-yellow-300 bg-yellow-50 px-1 group-hover:bg-yellow-100 min-w-fit">
                                    <div class="js-statistics-mentor-list flex flex-wrap"
                                        data-team-id="{{ $team['id'] }}">
                                        <div class="js-statistics-mentor-item rounded pl-7 border border-orange-500 pr-2 py-2 bg-linear-to-br from-orange-100 via-white to-orange-200 font-semibold truncate w-32 flex items-center gap-1 cursor-pointer relative"
                                            data-mentor-id="{{ $team['mentor']['id'] }}"
                                            title="Mentor(a): {{ $team['mentor']['name'] }}"
                                            wire:click="openMentorSelector({{ $team['id'] }})">
                                            <button type="button"
                                                class="js-statistics-mentor-handle inline-flex absolute left-0 inset-y-0 h-full w-5 items-center justify-center border-r border-orange-300 bg-white/70 text-[10px] text-orange-700 cursor-grab!"
                                                title="{{ __('Mover mentor') }}" x-on:click.stop
                                                aria-label="{{ __('Mover mentor') }}">
                                                ::
                                            </button>
                                            <span class="truncate">{{ $team['mentor']['name'] }}</span>
                                        </div>
                                    </div>
                                </td>

                                <td
                                    class="border border-y-4 border-y-white border-l-yellow-300 border-r-white bg-yellow-50 px-1 group-hover:bg-yellow-100 min-w-fit">
                                    <div class="js-statistics-student-list flex gap-1 flex-wrap"
                                        data-team-id="{{ $team['id'] }}">
                                        @foreach ($team['students'] as $student)
                                            <div class="js-statistics-student-item relative rounded border border-sky-500 pl-7 pr-2 py-2 bg-linear-to-br from-sky-100 via-white to-sky-200 font-semibold truncate max-w-32 min-w-24 flex items-center gap-1 cursor-grab!"
                                                wire:key="student-{{ $team['id'] }}-{{ $student['id'] }}"
                                                data-student-id="{{ $student['id'] }}"
                                                wire:click="openStudentSelector({{ $team['id'] }}, {{ $student['id'] }})"
                                                title="Aluno(a): {{ $student['name'] }}">
                                                <button type="button"
                                                    class="js-statistics-student-handle inline-flex absolute left-0 inset-y-0 h-full w-5 items-center justify-center border-r border-sky-300 bg-white/70 text-[10px] text-sky-700 cursor-grab!"
                                                    title="{{ __('Mover aluno') }}" x-on:click.stop
                                                    aria-label="{{ __('Mover aluno') }}">
                                                    ::
                                                </button>
                                                <span class="truncate">{{ $student['name'] }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </td>

                                <td
                                    class="border border-y-4 border-y-white border-l-white border-r-green-300 bg-green-100 group-hover:bg-green-200 align-middle text-center text-sm font-bold text-blue-800">
                                    {{ $team['visitant'] }}</td>
                                <td
                                    class="border border-y-4 border-y-white border-x-green-300 bg-green-100 group-hover:bg-green-200 align-middle text-center text-sm font-bold text-blue-800">
                                    {{ $team['questionnaire'] }}</td>
                                <td
                                    class="border border-y-4 border-y-white border-x-green-300 bg-green-100 group-hover:bg-green-200 align-middle text-center text-sm font-bold text-blue-800">
                                    {{ $team['indication'] }}</td>
                                <td
                                    class="border border-y-4 border-y-white border-l-green-300 border-r-white bg-green-100 group-hover:bg-green-200 align-middle text-center text-sm font-bold text-blue-800">
                                    {{ $team['lifeway'] }}</td>

                                <td
                                    class="border border-y-4 border-y-white border-l-white border-r-fuchsia-300 bg-fuchsia-200 group-hover:bg-fuchsia-300 align-middle text-center text-sm font-bold text-blue-800">
                                    {{ $team['totExplained'] }}</td>
                                <td
                                    class="border border-y-4 border-y-white border-l-fuchsia-300 border-r-white bg-fuchsia-200 group-hover:bg-fuchsia-300 align-middle text-center text-sm font-bold text-blue-800">
                                    {{ $team['totPeople'] }}</td>

                                <td
                                    class="border border-y-4 border-y-white border-l-white border-r-red-300 bg-red-100 group-hover:bg-red-200 align-middle text-center text-sm font-bold text-blue-800">
                                    {{ $team['totDecision'] }}</td>
                                <td
                                    class="border border-y-4 border-y-white border-x-red-300 bg-red-100 group-hover:bg-red-200 align-middle text-center text-sm font-bold text-blue-800">
                                    {{ $team['totInteresting'] }}</td>
                                <td
                                    class="border border-y-4 border-y-white border-x-red-300 bg-red-100 group-hover:bg-red-200 align-middle text-center text-sm font-bold text-blue-800">
                                    {{ $team['totReject'] }}</td>
                                <td
                                    class="border border-y-4 border-y-white border-l-red-300 border-r-white bg-red-100 group-hover:bg-red-200 align-middle text-center text-sm font-bold text-blue-800">
                                    {{ $team['totChristian'] }}</td>

                                <td
                                    class="border border-y-4 border-y-white border-l-white border-r-blue-300 bg-blue-100 group-hover:bg-blue-200 align-middle text-center text-sm font-bold text-blue-800">
                                    {{ $team['meansGrowth'] }}</td>
                                <td
                                    class="border border-y-4 border-y-white border-l-blue-300 border-r-white bg-blue-100 group-hover:bg-blue-200 align-middle text-center text-sm font-bold text-blue-800">
                                    {{ $team['folowship'] }}</td>
                            </tr>
                        @endforeach

                        <tr class="h-7">
                            <td colspan="3"
                                class="border border-y-4 border-y-white border-x-black/20 bg-[#E5E5E5] pr-4 text-right italic font-semibold">
                                Total de cada coluna por sessão:
                            </td>

                            @foreach ($columnTotals as $columnTotal)
                                <td
                                    class="border border-y-4 border-y-white border-x-black/20 bg-[#E5E5E5] align-middle text-center text-sm font-bold text-blue-800">
                                    {{ $columnTotal }}
                                </td>
                            @endforeach
                        </tr>
                    @endif
                </tbody>

                <tfoot>
                    <tr class="h-8">
                        <td colspan="3"
                            class="border border-t-4 border-t-white border-x-black/20 bg-slate-700 text-white pr-4 text-right italic font-semibold">
                            Total geral (sessão ativa):
                        </td>

                        @foreach ($columnTotals as $columnTotal)
                            <td
                                class="border border-t-4 border-t-white border-x-black/20 bg-slate-700 align-middle text-center text-sm font-bold text-blue-300">
                                {{ $columnTotal }}
                            </td>
                        @endforeach
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <flux:modal name="statistics-mentor-selector" wire:model="showMentorSelectorModal" class="max-w-lg w-full">
        <div class="space-y-4">
            <div class="space-y-1">
                <flux:heading size="lg">{{ __('Alterar mentor da equipe') }}</flux:heading>
                <flux:subheading>{{ __('Selecione um mentor cadastrado neste treinamento.') }}</flux:subheading>
            </div>

            <div class="space-y-2">
                <label for="mentor-selection" class="text-sm font-medium text-slate-700">
                    {{ __('Mentores disponíveis') }}
                </label>

                <select id="mentor-selection" wire:model="selectedMentorId"
                    class="h-11 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm">
                    <option value="">{{ __('Selecione') }}</option>
                    @foreach ($mentorSelectionOptions as $mentor)
                        <option value="{{ $mentor['id'] }}">{{ $mentor['name'] }}</option>
                    @endforeach
                </select>

                @error('selectedMentorId')
                    <p class="text-sm font-semibold text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end gap-2">
                <x-src.btn-silver type="button" wire:click="closeMentorSelector" :label="__('Cancelar')" />
                <x-src.btn-gold type="button" wire:click="assignMentorToTeam" :label="__('Salvar mentor')" />
            </div>
        </div>
    </flux:modal>

    <flux:modal name="statistics-student-selector" wire:model="showStudentSelectorModal" class="max-w-lg w-full">
        <div class="space-y-4">
            <div class="space-y-1">
                <flux:heading size="lg">{{ __('Alterar aluno da equipe') }}</flux:heading>
                <flux:subheading>{{ __('Selecione um aluno cadastrado neste treinamento.') }}</flux:subheading>
            </div>

            <div class="space-y-2">
                <label for="student-selection" class="text-sm font-medium text-slate-700">
                    {{ __('Alunos disponíveis') }}
                </label>

                <select id="student-selection" wire:model="selectedStudentId"
                    class="h-11 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm">
                    <option value="">{{ __('Selecione') }}</option>
                    @foreach ($studentSelectionOptions as $student)
                        <option value="{{ $student['id'] }}">{{ $student['name'] }}</option>
                    @endforeach
                </select>

                @error('selectedStudentId')
                    <p class="text-sm font-semibold text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end gap-2">
                <x-src.btn-silver type="button" wire:click="requestStudentRemoval" :label="__('Remover aluno')" />
                <x-src.btn-silver type="button" wire:click="closeStudentSelector" :label="__('Cancelar')" />
                <x-src.btn-gold type="button" wire:click="assignStudentToTeam" :label="__('Salvar aluno')" />
            </div>
        </div>
    </flux:modal>

    <flux:modal name="statistics-remove-student-confirmation" wire:model="showStudentRemovalConfirmationModal"
        class="max-w-lg w-full">
        <div class="space-y-4">
            <div class="space-y-1">
                <flux:heading size="lg">{{ __('Remover aluno da equipe?') }}</flux:heading>
                <flux:subheading>
                    {{ __('Esta ação remove apenas o aluno do card/equipe selecionado nesta sessão STP.') }}
                </flux:subheading>
            </div>

            <div class="flex justify-end gap-2">
                <x-src.btn-silver type="button" wire:click="cancelStudentRemoval" :label="__('Cancelar')" />
                <x-src.btn-gold type="button" wire:click="confirmStudentRemoval" :label="__('Remover aluno')" />
            </div>
        </div>
    </flux:modal>

    <flux:modal name="statistics-remove-session-confirmation" wire:model="showSessionRemovalConfirmationModal"
        class="max-w-lg w-full">
        <div class="space-y-4">
            <div class="space-y-1">
                <flux:heading size="lg">{{ __('Remover sessão STP?') }}</flux:heading>
                <flux:subheading>
                    {{ __('Os resultados obtidos pelas equipes desta sessão serão permanentemente perdidos.') }}
                </flux:subheading>
            </div>

            <div class="flex justify-end gap-2">
                <x-src.btn-silver type="button" wire:click="cancelSessionRemoval" :label="__('Cancelar')" />
                <x-src.btn-gold type="button" wire:click="confirmSessionRemoval" :label="__('Remover sessão')" />
            </div>
        </div>
    </flux:modal>

    <flux:modal name="statistics-remove-team-confirmation" wire:model="showTeamRemovalConfirmationModal"
        class="max-w-lg w-full">
        <div class="space-y-4">
            <div class="space-y-1">
                <flux:heading size="lg">{{ __('Remover equipe STP?') }}</flux:heading>
                <flux:subheading>
                    {{ __('Esta ação remove a equipe da sessão ativa e não pode ser desfeita.') }}
                </flux:subheading>
            </div>

            <div class="flex justify-end gap-2">
                <x-src.btn-silver type="button" wire:click="cancelTeamRemoval" :label="__('Cancelar')" />
                <x-src.btn-gold type="button" wire:click="confirmTeamRemoval" :label="__('Remover equipe')" />
            </div>
        </div>
    </flux:modal>

    <flux:modal name="statistics-create-team-modal" wire:model="showTeamCreationModal" class="max-w-2xl w-full">
        <div class="space-y-4">
            <div class="space-y-1">
                <flux:heading size="lg">{{ __('Nova equipe') }}</flux:heading>
                <flux:subheading>{{ __('Selecione o mentor e ao menos 2 alunos para compor a nova equipe.') }}
                </flux:subheading>
            </div>

            <div class="space-y-2">
                <label for="new-team-mentor" class="text-sm font-medium text-slate-700">
                    {{ __('Mentor') }}
                </label>
                <select id="new-team-mentor" wire:model="selectedNewTeamMentorId"
                    class="h-11 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm">
                    <option value="">{{ __('Selecione') }}</option>
                    @foreach ($teamCreationMentorOptions as $mentor)
                        <option value="{{ $mentor['id'] }}">{{ $mentor['name'] }}</option>
                    @endforeach
                </select>
                @error('selectedNewTeamMentorId')
                    <p class="text-sm font-semibold text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="space-y-2">
                <p class="text-sm font-medium text-slate-700">{{ __('Alunos') }}</p>
                <div class="max-h-64 overflow-y-auto rounded-lg border border-slate-200 bg-slate-50 p-3">
                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                        @foreach ($teamCreationStudentOptions as $student)
                            <label
                                class="inline-flex items-center gap-2 rounded-md bg-white px-2 py-1.5 text-sm text-slate-700">
                                <input type="checkbox" wire:model="selectedNewTeamStudentIds"
                                    value="{{ $student['id'] }}" class="rounded border-slate-300 text-blue-600">
                                <span class="truncate">{{ $student['name'] }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
                @error('selectedNewTeamStudentIds')
                    <p class="text-sm font-semibold text-red-600">{{ $message }}</p>
                @enderror
            </div>

            @error('teamCreation')
                <p class="text-sm font-semibold text-red-600">{{ $message }}</p>
            @enderror

            <div class="flex justify-end gap-2">
                <x-src.btn-silver type="button" wire:click="cancelTeamCreation" :label="__('Cancelar')" />
                <x-src.btn-gold type="button" wire:click="confirmTeamCreation" :label="__('Criar equipe')" />
            </div>
        </div>
    </flux:modal>
</div>
