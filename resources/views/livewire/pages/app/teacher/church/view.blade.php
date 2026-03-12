<div>
    <x-src.toolbar.header :title="__('Detalhes da igreja')" :description="__('Visão completa da igreja para gestão pelo professor.')" fixed-route-name="app.teacher.churches.show" />
    <x-src.toolbar.nav>
        <x-src.toolbar.button :href="route('app.teacher.churches.index')" :label="__('Listar Igrejas')" icon="list" :tooltip="__('Voltar para listagem')" />
        <x-src.toolbar.button :label="__('Novo participante')" icon="plus" :tooltip="__('Adicionar participante vinculado a esta igreja')"
            x-on:click.prevent="$dispatch('open-teacher-church-participant-create-modal', { churchId: {{ $church->id }} })" />
        <x-src.toolbar.button :label="__('Editar')" icon="pencil" :tooltip="__('Editar dados da igreja')"
            x-on:click.prevent="$dispatch('open-teacher-church-edit-modal', { churchId: {{ $church->id }} })" />
    </x-src.toolbar.nav>

    <section
        class="rounded-2xl border border-amber-300/20 bg-linear-to-br from-slate-100 via-white to-slate-200 p-4 shadow-lg sm:p-6">
        <div class="mb-6 flex flex-wrap items-start justify-between gap-4 border-b-2 border-slate-200/80 pb-4">
            <div class="flex items-start gap-4">
                <div class="shrink-0">
                    <img src="{{ $logoUrl }}" alt="{{ __('Logo da igreja') }}"
                        class="h-24 w-24 rounded-xl border border-slate-300 bg-white object-cover shadow-sm">
                </div>
                <div class="pt-1">
                    <h2 class="text-xl font-semibold text-slate-900" style="font-family: 'Cinzel', serif;">
                        {{ $church->name }}
                    </h2>
                    <p class="text-sm text-slate-600">{{ $church->pastor ?: __('Pastor não informado') }}</p>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap gap-4">
            <div class="rounded-xl border border-slate-200 bg-white/80 p-4 basis-44 flex-auto">
                <p class="text-xs uppercase text-slate-500">{{ __('Membros totais') }}</p>
                <p class="mt-1 text-2xl font-bold text-slate-900">{{ $totalMembersCount }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white/80 p-4 basis-44 flex-auto">
                <p class="text-xs uppercase text-sky-700">{{ __('Total de credenciados') }}
                </p>
                <p class="mt-1 text-2xl font-bold text-sky-950">{{ $totalAccreditedMembersInLeaderCourses }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white/80 p-4 basis-44 flex-auto">
                <p class="text-xs uppercase text-slate-500">{{ __('Pastores cadastrados') }}</p>
                <p class="mt-1 text-2xl font-bold text-slate-900">{{ $pastorMembersCount }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white/80 p-4 basis-44 flex-auto">
                <p class="text-xs uppercase text-slate-500">{{ __('Missionários vinculados') }}</p>
                <p class="mt-1 text-2xl font-bold text-slate-900">{{ $church->missionaries_count }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white/80 p-4 basis-44 flex-auto">
                <p class="text-xs uppercase text-slate-500">{{ __('Treinamentos na igreja') }}</p>
                <p class="mt-1 text-2xl font-bold text-slate-900">{{ $churchTrainingsCount }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white/80 p-4 basis-44 flex-auto">
                <p class="text-xs uppercase text-slate-500">{{ __('Treinamentos sob sua gestão') }}</p>
                <p class="mt-1 text-2xl font-bold text-slate-900">{{ $teacherTrainingsCount }}</p>
            </div>
        </div>

        <div class="mt-6 grid gap-4 lg:grid-cols-2">
            <div class="rounded-xl border border-slate-200 bg-white/80 p-4">
                <h3 class="mb-2 border-b-2 border-sky-800/30 pb-2 text-sm font-semibold text-slate-900 uppercase">
                    {{ __('Dados de contato da igreja') }}
                </h3>
                <div class="mt-3 flex flex-col gap-3 text-sm text-slate-700">
                    <div class="flex items-center justify-between gap-4 border-b border-sky-100/70">
                        <span>{{ __('E-mail da igreja') }}</span>
                        <span
                            class="text-right font-semibold text-slate-900">{{ $church->email ?: __('Não informado') }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-4 border-b border-sky-100/70">
                        <span>{{ __('Telefone da igreja') }}</span>
                        <span
                            class="text-right font-semibold text-slate-900">{{ $church->phone ?: __('Não informado') }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-4 border-b border-sky-100/70">
                        <span>{{ __('Contato responsável') }}</span>
                        <span
                            class="text-right font-semibold text-slate-900">{{ $church->contact ?: __('Não informado') }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-4 border-b border-sky-100/70">
                        <span>{{ __('Telefone do contato') }}</span>
                        <span
                            class="text-right font-semibold text-slate-900">{{ $church->contact_phone ?: __('Não informado') }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <span>{{ __('E-mail do contato') }}</span>
                        <span
                            class="text-right font-semibold text-slate-900">{{ $church->contact_email ?: __('Não informado') }}</span>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white/80 p-4">
                <h3 class="mb-2 border-b-2 border-sky-800/30 pb-2 text-sm font-semibold text-slate-900 uppercase">
                    {{ __('Endereço') }}
                </h3>
                <div class="mt-3 flex flex-col gap-3 text-sm text-slate-700">
                    <div class="flex items-center justify-between gap-4 border-b border-sky-100/70">
                        <span>{{ __('CEP') }}</span>
                        <span
                            class="text-right font-semibold text-slate-900">{{ $church->postal_code ?: __('Não informado') }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-4 border-b border-sky-100/70">
                        <span>{{ __('UF') }}</span>
                        <span
                            class="text-right font-semibold text-slate-900">{{ $church->state ?: __('Não informado') }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-4 border-b border-sky-100/70">
                        <span>{{ __('Logradouro') }}</span>
                        <span
                            class="text-right font-semibold text-slate-900">{{ $church->street ?: __('Não informado') }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-4 border-b border-sky-100/70">
                        <span>{{ __('Número') }}</span>
                        <span
                            class="text-right font-semibold text-slate-900">{{ $church->number ?: __('Não informado') }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-4 border-b border-sky-100/70">
                        <span>{{ __('Complemento') }}</span>
                        <span
                            class="text-right font-semibold text-slate-900">{{ $church->complement ?: __('Não informado') }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-4 border-b border-sky-100/70">
                        <span>{{ __('Bairro') }}</span>
                        <span
                            class="text-right font-semibold text-slate-900">{{ $church->district ?: __('Não informado') }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <span>{{ __('Cidade') }}</span>
                        <span
                            class="text-right font-semibold text-slate-900">{{ $church->city ?: __('Não informado') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6 grid gap-4 xl:grid-cols-2">
            <div class="rounded-xl border border-slate-200 bg-white/80 p-4">
                <h3 class="border-b border-slate-200 pb-2 text-sm font-semibold uppercase text-slate-700">
                    {{ __('Membros vinculados') }}
                </h3>

                <div class="mt-3">
                    <div class="flex items-stretch overflow-hidden rounded-xl border border-slate-300 bg-white shadow-sm">
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="memberSearch"
                            placeholder="{{ __('Buscar membro por nome ou e-mail') }}"
                            class="min-w-0 flex-1 border-0 bg-transparent px-4 py-2.5 text-sm text-slate-700 placeholder:text-slate-400 focus:outline-none focus:ring-0"
                        />

                        @if (filled($memberSearch))
                            <button
                                type="button"
                                wire:click="clearMemberSearch"
                                class="inline-flex items-center justify-center px-3 text-slate-400 transition hover:text-slate-700"
                                aria-label="{{ __('Limpar filtro') }}"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12" />
                                </svg>
                            </button>
                        @endif
                    </div>
                </div>

                <div class="mt-3 overflow-x-auto">
                    <table class="w-full min-w-[760px] text-left text-sm">
                        <thead class="text-xs uppercase text-slate-500">
                            <tr class="border-b border-slate-200">
                                <th class="px-3 py-3 font-semibold">
                                    <button type="button" wire:click="sortMembersBy('name')"
                                        class="inline-flex items-center gap-1.5 text-left transition hover:text-slate-900">
                                        <span>{{ __('Membro') }}</span>
                                        @if ($memberSortField === 'name')
                                            <span>{{ $memberSortDirection === 'asc' ? '↑' : '↓' }}</span>
                                        @endif
                                    </button>
                                </th>
                                <th class="px-3 py-3 font-semibold">
                                    <button type="button" wire:click="sortMembersBy('profile')"
                                        class="inline-flex items-center gap-1.5 text-left transition hover:text-slate-900">
                                        <span>{{ __('Perfil') }}</span>
                                        @if ($memberSortField === 'profile')
                                            <span>{{ $memberSortDirection === 'asc' ? '↑' : '↓' }}</span>
                                        @endif
                                    </button>
                                </th>
                                <th class="px-3 py-3 font-semibold">
                                    <button type="button" wire:click="sortMembersBy('courses')"
                                        class="inline-flex items-center gap-1.5 text-left transition hover:text-slate-900">
                                        <span>{{ __('Cursos') }}</span>
                                        @if ($memberSortField === 'courses')
                                            <span>{{ $memberSortDirection === 'asc' ? '↑' : '↓' }}</span>
                                        @endif
                                    </button>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @forelse ($members as $member)
                                @php
                                    $isFacilitator = $member->roles->contains(
                                        fn ($role): bool => mb_strtolower((string) $role->name, 'UTF-8') === 'facilitator',
                                    );
                                    $profileLabel = $isFacilitator ? __('Facilitador') : ((bool) $member->is_pastor ? __('Pastor') : __('Membro'));
                                    $memberCourses = $member->trainings->pluck('course')->filter()->unique('id')->values();
                                @endphp
                                <tr wire:key="teacher-church-member-{{ $member->id }}"
                                    class="cursor-pointer odd:bg-white even:bg-sky-50/50 hover:bg-amber-50/70 transition-colors"
                                    data-row-link="{{ route('app.teacher.church.profiles.show', $member) }}"
                                    x-on:click="window.location = $el.dataset.rowLink">
                                    <td class="px-3 py-3 align-middle">
                                        <div class="flex items-center gap-3">
                                            @if ($member->profile_photo_url)
                                                <img src="{{ $member->profile_photo_url }}"
                                                    alt="{{ __('Foto de :name', ['name' => $member->name]) }}"
                                                    class="h-11 w-11 shrink-0 rounded-full border border-slate-200 object-cover shadow-xs">
                                            @else
                                                <div
                                                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-slate-200 text-xs font-bold uppercase text-slate-700">
                                                    {{ $member->initials() }}
                                                </div>
                                            @endif

                                            <div class="min-w-0">
                                                <div class="font-semibold whitespace-nowrap text-slate-950">{{ $member->name }}</div>
                                                <div class="max-w-[18rem] truncate text-xs text-slate-600">
                                                    {{ $member->email ?: __('Não informado') }}
                                                </div>
                                                <div class="text-xs text-slate-500">{{ $member->phone ?: __('Não informado') }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-3 py-3 align-middle">
                                        <div class="font-medium text-slate-800">{{ $profileLabel }}</div>
                                        <div class="text-xs text-slate-500">
                                            {{ $memberCourses->count() }} {{ \Illuminate\Support\Str::plural('curso', $memberCourses->count()) }}
                                        </div>
                                    </td>
                                    <td class="px-3 py-3 align-middle">
                                        <div class="flex items-center py-1">
                                            @forelse ($memberCourses->take(6) as $index => $course)
                                                @php
                                                    $courseInitials = $course->initials ?: \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($course->name, 0, 2));
                                                    $courseColor = $course->color ?: '#0f766e';
                                                @endphp
                                                <span
                                                    class="{{ $index > 0 ? 'ml-1 md:-ml-1' : '' }} inline-flex h-9 min-w-9 items-center justify-center rounded-full border-2 px-2.5 text-[11px] font-bold tracking-[0.14em] text-white shadow-sm ring-2 ring-white"
                                                    style="z-index: {{ 20 - $index }}; background: linear-gradient(135deg, {{ $courseColor }}, {{ $courseColor }}CC); border-color: {{ $courseColor }};"
                                                    title="{{ trim(($course->type ? $course->type . ' - ' : '') . $course->name) }}">
                                                    {{ $courseInitials }}
                                                </span>
                                            @empty
                                                <span class="text-sm text-slate-500">{{ __('Sem cursos') }}</span>
                                            @endforelse

                                            @if ($memberCourses->count() > 6)
                                                <span
                                                    class="ml-1 md:-ml-1 inline-flex h-9 min-w-9 items-center justify-center rounded-full border-2 border-white bg-slate-200 px-2.5 text-[11px] font-bold text-slate-700 shadow-sm ring-2 ring-white"
                                                    style="z-index: 10;">
                                                    +{{ $memberCourses->count() - 6 }}
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-3 py-6 text-center text-slate-600">
                                        {{ __('Sem membros vinculados a esta igreja.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">{{ $members->links(data: ['scrollTo' => false]) }}</div>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white/80 p-4">
                <h3 class="border-b border-slate-200 pb-2 text-sm font-semibold uppercase text-slate-700">
                    {{ __('Treinamentos relacionados') }}
                </h3>

                <div class="mt-3 overflow-x-auto">
                    <table class="w-full min-w-xl text-left text-sm">
                        <thead class="text-xs uppercase text-slate-500">
                            <tr class="border-b border-slate-200">
                                <th class="px-2 py-2">{{ __('Curso') }}</th>
                                <th class="px-2 py-2">{{ __('Professor') }}</th>
                                <th class="px-2 py-2">{{ __('Status') }}</th>
                                <th class="px-2 py-2">{{ __('Primeira data') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @forelse ($trainings as $training)
                                @php
                                    $firstDate = $training->eventDates->first();
                                    $canAccessTraining = (int) $training->teacher_id === (int) auth()->id();
                                    $statusTextClass = match ($training->status?->key()) {
                                        'planning' => 'text-amber-900',
                                        'scheduled' => 'text-sky-900',
                                        'canceled' => 'text-rose-900',
                                        'completed' => 'text-emerald-900',
                                        default => 'text-slate-700',
                                    };
                                @endphp
                                <tr wire:key="church-training-{{ $training->id }}"
                                    class="{{ $canAccessTraining ? 'cursor-pointer hover:bg-slate-100/90' : 'cursor-not-allowed hover:bg-slate-50' }} odd:bg-white even:bg-slate-50"
                                    @if ($canAccessTraining) data-row-link="{{ route('app.teacher.trainings.show', $training) }}" x-on:click="window.location = $el.dataset.rowLink" @endif>
                                    <td class="px-2 py-2 font-medium text-slate-900">
                                        {{ $training->course?->name ?: __('Curso não informado') }}
                                    </td>
                                    <td class="px-2 py-2 text-slate-700">
                                        {{ $training->teacher?->name ?: __('Não informado') }}
                                    </td>
                                    <td class="px-2 py-2 font-semibold {{ $statusTextClass }}">
                                        {{ $training->status?->label() ?? __('Não informado') }}</td>
                                    <td class="px-2 py-2 text-slate-700">
                                        {{ $firstDate?->date ? \Illuminate\Support\Carbon::parse($firstDate->date)->format('d/m/Y') : __('Não informado') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-2 py-4 text-center text-slate-600">
                                        {{ __('Sem treinamentos vinculados a esta igreja.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">{{ $trainings->links(data: ['scrollTo' => false]) }}</div>
            </div>
        </div>

        @if ($leaderCoursesWithAccreditedMembers->isNotEmpty())
            <div class="mt-6 grid gap-4 xl:grid-cols-2">
                @foreach ($leaderCoursesWithAccreditedMembers as $leaderCourseCard)
                    @php
                        $leaderCourse = $leaderCourseCard['course'];
                        $accreditedMembers = $leaderCourseCard['accreditedMembers'];
                    @endphp
                    <div class="rounded-xl border border-slate-200 bg-white/80 p-4">
                        <h3 class="border-b border-slate-200 pb-2 text-sm font-semibold uppercase text-slate-700">
                            {{ __('Credenciados - :course', ['course' => $leaderCourse->name]) }}
                        </h3>

                        <div class="mt-3 overflow-x-auto">
                            <table class="w-full min-w-xl text-left text-sm">
                                <thead class="text-xs uppercase text-slate-500">
                                    <tr class="border-b border-slate-200">
                                        <th class="px-2 py-2">{{ __('Nome') }}</th>
                                        <th class="px-2 py-2">{{ __('E-mail') }}</th>
                                        <th class="px-2 py-2">{{ __('Telefone') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200">
                                    @forelse ($accreditedMembers as $accreditedMember)
                                        <tr class="odd:bg-white even:bg-slate-50">
                                            <td class="px-2 py-2 font-medium text-slate-900">
                                                {{ $accreditedMember->name }}</td>
                                            <td class="px-2 py-2 text-slate-700">
                                                {{ $accreditedMember->email ?: __('Não informado') }}
                                            </td>
                                            <td class="px-2 py-2 text-slate-700">
                                                {{ $accreditedMember->phone ?: __('Não informado') }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="px-2 py-4 text-center text-slate-600">
                                                {{ __('Sem membros credenciados neste curso.') }}
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">{{ $accreditedMembers->links(data: ['scrollTo' => false]) }}</div>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="mt-6 rounded-xl border border-slate-200 bg-white/80 p-4">
            <h3 class="mb-2 border-b-2 border-sky-800/30 pb-2 text-sm font-semibold text-slate-900 uppercase">
                {{ __('Auditoria do cadastro') }}
            </h3>
            <div class="mt-3 flex flex-col gap-3 text-sm text-slate-700">
                <div class="flex items-center justify-between gap-4 border-b border-sky-100/70">
                    <span>{{ __('Criado em') }}</span>
                    <span class="text-right font-semibold text-slate-900">
                        {{ $church->created_at?->format('d/m/Y H:i') ?? __('Não informado') }}
                    </span>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <span>{{ __('Atualizado em') }}</span>
                    <span class="text-right font-semibold text-slate-900">
                        {{ $church->updated_at?->format('d/m/Y H:i') ?? __('Não informado') }}
                    </span>
                </div>
            </div>
        </div>

        <div class="mt-6 rounded-xl border border-slate-200 bg-white/80 p-4">
            <h3 class="mb-2 border-b-2 border-sky-800/30 pb-2 text-sm font-semibold text-slate-900 uppercase">
                {{ __('Observações') }}
            </h3>
            <div class="mt-3 grid gap-3">
                <div>
                    <p class="text-sm whitespace-pre-line text-slate-900">{{ $church->notes ?: __('Não informado') }}
                    </p>
                </div>
            </div>
        </div>


    </section>

    <livewire:pages.app.teacher.church.create-participant-modal :church-id="$church->id"
        wire:key="teacher-church-create-participant-modal-{{ $church->id }}" />
    <livewire:pages.app.teacher.church.edit-modal :church-id="$church->id"
        wire:key="teacher-church-edit-modal-{{ $church->id }}" />
</div>
