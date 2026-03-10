<div>
    <x-src.toolbar.header :title="__('Gerenciamento de Igrejas')" :description="__('Igrejas relacionadas aos seus treinamentos, incluindo sua igreja de vínculo.')" fixed-route-name="app.teacher.churches.index" />
    <div class="relative">
        <x-src.toolbar.nav>
            <x-src.toolbar.button :label="__('Nova igreja')" icon="plus" :tooltip="__('Cadastrar nova igreja')"
                x-on:click.prevent="$dispatch('open-teacher-church-create-modal')" />
            <x-src.toolbar.button :label="__('Listar usuários')" icon="users" :tooltip="__('Listar todos os usuários cadastrados')"
                x-on:click.prevent="$wire.openAllUsersModal()" />

            <div class="relative ml-auto w-full min-w-64 max-w-md">
                <input type="text" wire:model.live.debounce.300ms="churchSearch"
                    x-on:keydown.escape="$wire.set('churchSearch', '')"
                    placeholder="{{ __('Buscar igreja ou usuário por nome') }}"
                    class="w-full rounded-xl border border-slate-300 bg-white/95 px-3 py-2 pr-10 text-sm text-slate-900 shadow-xs outline-none transition placeholder:text-slate-400 focus:border-sky-500 focus:ring-2 focus:ring-sky-200" />
                @if (trim($churchSearch) !== '')
                    <button type="button" wire:click="$set('churchSearch', '')"
                        class="absolute inset-y-0 right-2 my-auto inline-flex h-7 w-7 items-center justify-center rounded-md text-slate-500 transition hover:bg-slate-100 hover:text-slate-700"
                        aria-label="{{ __('Limpar busca') }}">
                        <span class="text-base leading-none">&times;</span>
                    </button>
                @endif
            </div>
        </x-src.toolbar.nav>

        @if (trim($churchSearch) !== '')
            <div class="pointer-events-none absolute inset-x-0 top-full z-50 -mt-2">
                <div
                    class="pointer-events-auto ml-auto w-full min-w-64 max-w-md overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">
                    <div class="max-h-96 overflow-y-auto p-2">
                        <section>
                            <div class="px-2 pb-1 pt-1 text-xs font-bold uppercase tracking-wide text-slate-500">
                                {{ __('Igrejas encontradas') }}
                            </div>

                            @forelse ($churchSearchResults as $churchResult)
                                @php
                                    $logoPath = trim((string) $churchResult->getRawOriginal('logo'));
                                    $logoUrl =
                                        $logoPath !== '' &&
                                        \Illuminate\Support\Facades\Storage::disk('public')->exists($logoPath)
                                            ? \Illuminate\Support\Facades\Storage::disk('public')->url($logoPath)
                                            : asset('images/svg/church.svg');
                                @endphp
                                <a href="{{ route('app.teacher.churches.show', $churchResult) }}"
                                    class="mb-1 flex items-center gap-3 rounded-xl px-2 py-2 transition hover:bg-slate-100">
                                    <img src="{{ $logoUrl }}" alt="{{ __('Logo da igreja') }}"
                                        class="h-10 w-10 rounded-full border border-slate-200 bg-white object-cover">
                                    <div class="min-w-0">
                                        <div class="truncate text-sm font-semibold text-slate-900">
                                            {{ $churchResult->name }}
                                        </div>
                                        <div class="truncate text-xs text-slate-500">
                                            {{ $churchResult->city ?: __('Cidade não informada') }}
                                            @if ($churchResult->state)
                                                / {{ $churchResult->state }}
                                            @endif
                                        </div>
                                    </div>
                                </a>
                            @empty
                                <div class="rounded-xl px-2 py-3 text-sm text-slate-500">
                                    {{ __('Igreja não encontrada.') }}
                                </div>
                            @endforelse
                        </section>

                        <div class="my-2 border-t border-slate-200"></div>

                        <section>
                            <div class="px-2 pb-1 pt-1 text-xs font-bold uppercase tracking-wide text-slate-500">
                                {{ __('Usuários encontrados') }}
                            </div>

                            @forelse ($userSearchResults as $userResult)
                                <a href="{{ route('app.teacher.church.profiles.show', $userResult) }}"
                                    class="mb-1 flex items-center gap-3 rounded-xl px-2 py-2 transition hover:bg-slate-100">
                                    @if ($userResult->profile_photo_url)
                                        <img src="{{ $userResult->profile_photo_url }}"
                                            alt="{{ __('Foto de :name', ['name' => $userResult->name]) }}"
                                            class="h-10 w-10 rounded-full border border-slate-200 bg-white object-cover">
                                    @else
                                        <div
                                            class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-slate-200 text-xs font-bold uppercase text-slate-700">
                                            {{ $userResult->initials() }}
                                        </div>
                                    @endif
                                    <div class="min-w-0">
                                        <div class="truncate text-sm font-semibold text-slate-900">
                                            {{ $userResult->name }}
                                        </div>
                                        <div class="truncate text-xs text-slate-500">
                                            {{ $userResult->email }}
                                        </div>
                                        <div class="truncate text-xs text-slate-500">
                                            {{ $userResult->church?->name ?? __('Sem igreja oficial') }}
                                        </div>
                                    </div>
                                </a>
                            @empty
                                <div class="rounded-xl px-2 py-3 text-sm text-slate-500">
                                    {{ __('Nenhum usuário encontrado para este termo.') }}
                                </div>
                            @endforelse
                        </section>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <section
        class="rounded-2xl border border-amber-300/20 bg-linear-to-br from-slate-100 via-white to-slate-200 p-4 shadow-lg sm:p-6">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3 border-b-2 border-slate-200/80 pb-3">
            <div class="flex flex-col gap-1">
                <h2 class="text-xl font-semibold text-slate-900" style="font-family: 'Cinzel', serif;">
                    {{ __('Igrejas vinculadas') }}
                </h2>
                <p class="text-sm text-slate-600">{{ __('Somente igrejas acessíveis ao professor.') }}</p>
            </div>
            <div class="rounded-full bg-amber-100 px-3 py-1 text-sm font-semibold text-amber-800">
                {{ __('Total listado: :count', ['count' => $churches->total()]) }}
            </div>
        </div>

        @if (session('success'))
            <div class="mb-4 rounded-xl border border-emerald-300 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        <div class="overflow-x-auto">
            <table class="w-full min-w-4xl text-left text-sm">
                <thead class="text-xs uppercase text-slate-500">
                    <tr class="border-b border-slate-200">
                        <th class="px-3 py-2">
                            <button type="button" wire:click="sortBy('index')"
                                class="inline-flex items-center gap-1 font-semibold text-slate-600 hover:text-slate-900">
                                <span>{{ __('Ordem') }}</span>
                                @if ($sortField === 'index')
                                    <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </button>
                        </th>
                        <th class="px-3 py-2 text-center">{{ __('Logo') }}</th>
                        <th class="px-3 py-2">
                            <button type="button" wire:click="sortBy('church')"
                                class="inline-flex items-center gap-1 font-semibold text-slate-600 hover:text-slate-900">
                                <span>{{ __('Igreja / Pastor') }}</span>
                                @if ($sortField === 'church')
                                    <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </button>
                        </th>
                        <th class="px-3 py-2">
                            <button type="button" wire:click="sortBy('contact')"
                                class="inline-flex items-center gap-1 font-semibold text-slate-600 hover:text-slate-900">
                                <span>{{ __('Contato / E-mail') }}</span>
                                @if ($sortField === 'contact')
                                    <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </button>
                        </th>
                        <th class="px-3 py-2">
                            <button type="button" wire:click="sortBy('location')"
                                class="inline-flex items-center gap-1 font-semibold text-slate-600 hover:text-slate-900">
                                <span>{{ __('Cidade / UF') }}</span>
                                @if ($sortField === 'location')
                                    <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </button>
                        </th>
                        <th class="px-3 py-2 text-center">
                            <button type="button" wire:click="sortBy('members')"
                                class="inline-flex items-center gap-1 font-semibold text-slate-600 hover:text-slate-900">
                                <span>{{ __('Total de membros') }}</span>
                                @if ($sortField === 'members')
                                    <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </button>
                        </th>
                        <th class="px-3 py-2 text-center">
                            <button type="button" wire:click="sortBy('accredited')"
                                class="inline-flex items-center gap-1 font-semibold text-slate-600 hover:text-slate-900">
                                <span>{{ __('Total de credenciados') }}</span>
                                @if ($sortField === 'accredited')
                                    <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </button>
                        </th>
                        <th class="px-3 py-2 text-right">{{ __('Ações') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($churches as $church)
                        <tr wire:key="teacher-church-{{ $church->id }}"
                            class="cursor-pointer transition odd:bg-white even:bg-slate-50 hover:bg-slate-100/90"
                            data-row-link="{{ route('app.teacher.churches.show', $church) }}"
                            x-on:click="window.location = $el.dataset.rowLink">
                            <td class="px-3 py-3 align-middle">
                                {{ ($churches->currentPage() - 1) * $churches->perPage() + $loop->iteration }}
                            </td>
                            <td class="px-3 py-3 text-center align-middle">
                                @php
                                    $logoPath = trim((string) $church->getRawOriginal('logo'));
                                    $logoUrl =
                                        $logoPath !== '' &&
                                        \Illuminate\Support\Facades\Storage::disk('public')->exists($logoPath)
                                            ? \Illuminate\Support\Facades\Storage::disk('public')->url($logoPath)
                                            : asset('images/svg/church.svg');
                                @endphp
                                <div class="flex justify-center">
                                    <img src="{{ $logoUrl }}" alt="{{ __('Logo da igreja') }}"
                                        class="h-10 w-10 rounded-lg border border-slate-200 bg-white object-cover">
                                </div>
                            </td>
                            <td class="px-3 py-3 align-middle">
                                <div class="font-semibold text-slate-900">
                                    {{ $church->name }}
                                </div>
                                <div class="text-xs text-slate-600">{{ $church->pastor ?: __('Pastor não informado') }}
                                </div>
                            </td>
                            <td class="px-3 py-3 align-middle">
                                <div class="font-medium text-slate-800">
                                    {{ $church->contact ?: __('Contato não informado') }}</div>
                                <div class="text-xs text-slate-600">
                                    {{ $church->contact_email ?: __('E-mail não informado') }}</div>
                            </td>
                            <td class="px-3 py-3 align-middle">
                                <div class="font-medium text-slate-800">
                                    {{ $church->city ?: __('Cidade não informada') }}</div>
                                <div class="text-xs text-slate-600">{{ $church->state ?: __('UF não informada') }}
                                </div>
                            </td>
                            <td class="px-3 py-3 text-center align-middle">
                                <span
                                    class="inline-flex rounded-full bg-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-700">
                                    {{ $church->total_members_count }}
                                </span>
                            </td>
                            <td class="px-3 py-3 text-center align-middle">
                                <span
                                    class="inline-flex rounded-full bg-sky-100 px-2.5 py-1 text-xs font-semibold text-sky-800">
                                    {{ (int) $church->total_accredited_members_count }}
                                </span>
                            </td>
                            <td class="px-3 py-3 align-middle">
                                <div class="flex justify-end" x-on:click.stop>
                                    <flux:button variant="danger" size="sm" icon="trash" icon:variant="outline"
                                        wire:click="removeChurch({{ $church->id }})"
                                        wire:confirm="{{ __('Deseja remover esta igreja? Esta ação é permanente.') }}"
                                        aria-label="{{ __('Remover igreja') }}"
                                        :disabled="(bool) auth()->user()?->church_id && auth()->user()->church_id === $church->id">
                                    </flux:button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-3 py-8 text-center text-sm text-slate-600">
                                {{ __('Nenhuma igreja encontrada para os seus treinamentos.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-5">
            {{ $churches->links(data: ['scrollTo' => false]) }}
        </div>
    </section>

    <flux:modal name="teacher-all-users-modal" wire:model="showAllUsersModal" class="max-w-[92vw] w-full bg-sky-950! p-0!">
        <div class="flex max-h-[90vh] flex-col overflow-hidden rounded-2xl">
            <div class="sticky top-0 z-20 border-b border-sky-800 bg-sky-950 px-6 py-4">
                <flux:heading size="lg"><span class="text-white!">{{ __('Perfis cadastrados no sistema') }}</span></flux:heading>
                <flux:subheading>
                    <span class="text-white! opacity-80">{{ __('Lista completa de usuários com busca por nome, e-mail, cidade, estado e igreja.') }}</span>
                </flux:subheading>
            </div>

            <div class="min-h-0 flex-1 overflow-y-auto bg-white/95 px-6 py-5">
                <div class="space-y-5">
                    <div class="grid gap-3 md:grid-cols-[minmax(0,1fr)_auto] md:items-center">
                        <div class="relative">
                            <input type="text" wire:model.live.debounce.300ms="userDirectorySearch"
                                x-on:keydown.escape="$wire.set('userDirectorySearch', '')"
                                placeholder="{{ __('Buscar por nome, e-mail, cidade, UF ou igreja') }}"
                                class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 pr-10 text-sm text-slate-900 shadow-xs outline-none transition placeholder:text-slate-400 focus:border-sky-500 focus:ring-2 focus:ring-sky-200" />
                            @if (trim($userDirectorySearch) !== '')
                                <button type="button" wire:click="$set('userDirectorySearch', '')"
                                    class="absolute inset-y-0 right-2 my-auto inline-flex h-7 w-7 items-center justify-center rounded-md text-slate-500 transition hover:bg-slate-100 hover:text-slate-700"
                                    aria-label="{{ __('Limpar busca') }}">
                                    <span class="text-base leading-none">&times;</span>
                                </button>
                            @endif
                        </div>

                        <div class="rounded-full bg-amber-100 px-3 py-1 text-sm font-semibold text-amber-800">
                            {{ __('Total listado: :count', ['count' => $allUsers->total()]) }}
                        </div>
                    </div>

                    <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white">
                        <table class="w-full min-w-5xl text-left text-sm">
                            <thead class="sticky top-0 z-10 bg-slate-100 text-xs uppercase text-slate-500">
                                <tr class="border-b border-slate-200">
                                    <th class="px-3 py-3 text-center">{{ __('Foto') }}</th>
                                    <th class="px-3 py-3">{{ __('Usuário') }}</th>
                                    <th class="px-3 py-3">{{ __('Cidade / UF') }}</th>
                                    <th class="px-3 py-3">{{ __('Igreja') }}</th>
                                    <th class="px-3 py-3">{{ __('Cursos') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                @forelse ($allUsers as $listedUser)
                                    @php
                                        $completedCourses = $listedUser->trainings
                                            ->pluck('course')
                                            ->filter()
                                            ->unique('id')
                                            ->values();
                                    @endphp
                                    <tr wire:key="teacher-all-user-{{ $listedUser->id }}"
                                        class="cursor-pointer odd:bg-white even:bg-slate-50/80 hover:bg-sky-50/70 transition"
                                        data-row-link="{{ route('app.teacher.church.profiles.show', $listedUser) }}"
                                        x-on:click="window.location = $el.dataset.rowLink">
                                        <td class="px-3 py-3 align-middle">
                                            <div class="flex justify-center">
                                                @if ($listedUser->profile_photo_url)
                                                    <img src="{{ $listedUser->profile_photo_url }}"
                                                        alt="{{ __('Foto de :name', ['name' => $listedUser->name]) }}"
                                                        class="h-11 w-11 rounded-full border border-slate-200 object-cover shadow-xs">
                                                @else
                                                    <div
                                                        class="flex h-11 w-11 items-center justify-center rounded-full bg-slate-200 text-xs font-bold uppercase text-slate-700">
                                                        {{ $listedUser->initials() }}
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-3 py-3 align-middle">
                                            <div class="font-semibold text-slate-900">{{ $listedUser->name }}</div>
                                            <div class="text-xs text-slate-500">{{ $listedUser->email }}</div>
                                        </td>
                                        <td class="px-3 py-3 align-middle text-slate-800">
                                            {{ $listedUser->city ?: __('Cidade não informada') }}
                                            <div class="text-xs text-slate-500">{{ $listedUser->state ?: __('UF não informada') }}</div>
                                        </td>
                                        <td class="px-3 py-3 align-middle">
                                            <div class="font-medium text-slate-800">
                                                {{ $listedUser->church?->name ?? $listedUser->church_temp?->name ?? __('Sem igreja vinculada') }}
                                            </div>
                                            <div class="text-xs text-slate-500">
                                                @if ($listedUser->church)
                                                    {{ __('Igreja oficial') }}
                                                @elseif ($listedUser->church_temp)
                                                    {{ __('Igreja temporária') }}
                                                @else
                                                    {{ __('Sem vínculo') }}
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-3 py-3 align-middle">
                                            <div class="flex items-center">
                                                @forelse ($completedCourses->take(5) as $index => $course)
                                                    <span
                                                        class="{{ $index > 0 ? '-ml-2' : '' }} inline-flex h-8 min-w-8 items-center justify-center rounded-full border border-white bg-sky-100 px-2 text-[11px] font-bold uppercase text-sky-800 shadow-sm"
                                                        title="{{ $course->name }}">
                                                        {{ $course->initials ?: \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($course->name, 0, 2)) }}
                                                    </span>
                                                @empty
                                                    <span class="text-sm text-slate-500">{{ __('Sem cursos') }}</span>
                                                @endforelse

                                                @if ($completedCourses->count() > 5)
                                                    <span
                                                        class="-ml-2 inline-flex h-8 min-w-8 items-center justify-center rounded-full border border-white bg-slate-200 px-2 text-[11px] font-bold text-slate-700 shadow-sm">
                                                        +{{ $completedCourses->count() - 5 }}
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-3 py-8 text-center text-sm text-slate-600">
                                            {{ __('Nenhum usuário encontrado para este filtro.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div>
                        {{ $allUsers->links(data: ['scrollTo' => false]) }}
                    </div>
                </div>
            </div>

            <div class="sticky bottom-0 z-20 border-t border-sky-800 bg-sky-950 px-6 py-4">
                <div class="flex justify-end">
                    <x-src.btn-silver type="button" wire:click="closeAllUsersModal">
                        {{ __('Fechar') }}
                    </x-src.btn-silver>
                </div>
            </div>
        </div>
    </flux:modal>

    <section
        class="mt-6 rounded-2xl border border-amber-300/20 bg-linear-to-br from-slate-100 via-white to-slate-200 p-4 shadow-lg sm:p-6">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3 border-b-2 border-slate-200/80 pb-3">
            <div class="flex flex-col gap-1">
                <h2 class="text-xl font-semibold text-slate-900" style="font-family: 'Cinzel', serif;">
                    {{ __('Pessoas sem igreja vinculada') }}
                </h2>
                <p class="text-sm text-slate-600">
                    {{ __('Somente inscritos nos treinamentos em que você é o professor titular.') }}
                </p>
            </div>
            <div class="rounded-full bg-amber-100 px-3 py-1 text-sm font-semibold text-amber-800">
                {{ __('Total listado: :count', ['count' => $unlinkedUsers->total()]) }}
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-4xl text-left text-sm">
                <thead class="text-xs uppercase text-slate-500">
                    <tr class="border-b border-slate-200">
                        <th class="px-3 py-2">{{ __('Ordem') }}</th>
                        <th class="px-3 py-2">{{ __('Nome') }}</th>
                        <th class="px-3 py-2">{{ __('E-mail') }}</th>
                        <th class="px-3 py-2">{{ __('Telefone') }}</th>
                        <th class="px-3 py-2 text-center">{{ __('Inscrições') }}</th>
                        <th class="px-3 py-2 text-right">{{ __('Ações') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($unlinkedUsers as $user)
                        <tr wire:key="teacher-unlinked-user-{{ $user->id }}"
                            class="cursor-pointer transition odd:bg-white even:bg-slate-50 hover:bg-slate-100/80"
                            wire:click="openUnlinkedUserModal({{ $user->id }})">
                            <td class="px-3 py-3 align-middle">
                                {{ ($unlinkedUsers->currentPage() - 1) * $unlinkedUsers->perPage() + $loop->iteration }}
                            </td>
                            <td class="px-3 py-3 align-middle font-semibold text-slate-900">
                                {{ $user->name }}
                            </td>
                            <td class="px-3 py-3 align-middle text-slate-800">
                                {{ $user->email }}
                            </td>
                            <td class="px-3 py-3 align-middle text-slate-800">
                                {{ $user->phone ?: __('Não informado') }}
                            </td>
                            <td class="px-3 py-3 text-center align-middle">
                                <span
                                    class="inline-flex rounded-full bg-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-700">
                                    {{ (int) ($user->teacher_training_registrations_count ?? 0) }}
                                </span>
                            </td>
                            <td class="px-3 py-3 align-middle">
                                <div class="flex justify-end gap-2" x-on:click.stop>
                                    <flux:button variant="danger" size="sm" icon="trash" icon:variant="outline"
                                        wire:click.stop="removeUnlinkedUser({{ $user->id }})"
                                        wire:confirm="{{ __('Deseja remover este usuário sem igreja vinculada? Esta ação é permanente.') }}"
                                        aria-label="{{ __('Remover usuário') }}">
                                    </flux:button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-3 py-8 text-center text-sm text-slate-600">
                                {{ __('Nenhum inscrito sem igreja vinculada encontrado nos seus treinamentos.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-5">
            {{ $unlinkedUsers->links(data: ['scrollTo' => false]) }}
        </div>
    </section>

    <flux:modal name="teacher-unlinked-user-modal" wire:model="showUnlinkedUserModal" class="max-w-5xl w-full bg-sky-950! p-0!">
        <div class="flex max-h-[90vh] flex-col overflow-hidden rounded-2xl">
            <div class="sticky top-0 z-20 border-b border-sky-800 bg-sky-950 px-6 py-4">
                <flux:heading size="lg"><span class="text-white!">{{ __('Detalhes do participante sem igreja') }}</span></flux:heading>
                <flux:subheading><span class="text-white! opacity-80">{{ $selectedUnlinkedUserName }}</span></flux:subheading>
            </div>

            <div class="min-h-0 flex-1 space-y-5 overflow-y-auto bg-white/95 px-6 py-4">
                <section class="grid gap-4 md:grid-cols-2">
                    <article class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <h3 class="text-sm font-semibold text-slate-800">{{ __('Dados do usuário') }}</h3>
                        <dl class="mt-3 grid gap-2 text-sm text-slate-700">
                            <div>
                                <dt class="text-xs uppercase text-slate-500">{{ __('E-mail') }}</dt>
                                <dd>{{ $selectedUnlinkedUserEmail ?: __('Não informado') }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs uppercase text-slate-500">{{ __('Telefone') }}</dt>
                                <dd>{{ $selectedUnlinkedUserPhone ?: __('Não informado') }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs uppercase text-slate-500">{{ __('Cidade / UF') }}</dt>
                                <dd>
                                    {{ $selectedUnlinkedUserCity ?: __('Cidade não informada') }}
                                    / {{ $selectedUnlinkedUserState ?: __('UF não informada') }}
                                </dd>
                            </div>
                        </dl>
                    </article>

                    <article class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <h3 class="text-sm font-semibold text-slate-800">{{ __('Associar igreja oficial') }}</h3>
                        <div class="mt-3 space-y-3">
                            <input type="text" wire:model.live.debounce.300ms="linkChurchSearch"
                                placeholder="{{ __('Buscar igreja por nome, cidade ou UF') }}"
                                class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-sky-500 focus:ring-2 focus:ring-sky-200" />

                            <div class="max-h-52 overflow-y-auto rounded-xl border border-slate-200 bg-white p-2">
                                @forelse ($linkableChurches as $church)
                                    <label wire:key="teacher-link-church-{{ $church->id }}"
                                        class="mb-1 flex cursor-pointer items-center gap-3 rounded-lg px-2 py-2 transition hover:bg-slate-50">
                                        <input type="radio" wire:model.live="linkChurchId" value="{{ $church->id }}"
                                            class="h-4 w-4 border-slate-300 text-sky-700 focus:ring-sky-300">
                                        <span class="min-w-0">
                                            <span class="block truncate text-sm font-semibold text-slate-900">
                                                {{ $church->name }}
                                            </span>
                                            <span class="block truncate text-xs text-slate-500">
                                                {{ $church->city ?: __('Cidade não informada') }} / {{ $church->state ?: __('UF não informada') }}
                                            </span>
                                        </span>
                                    </label>
                                @empty
                                    <p class="px-2 py-3 text-sm text-slate-600">{{ __('Nenhuma igreja encontrada para este filtro.') }}</p>
                                @endforelse
                            </div>

                            @error('linkChurchId')
                                <p class="text-xs font-semibold text-red-600">{{ $message }}</p>
                            @enderror

                            <div class="flex justify-end">
                                <flux:button variant="primary" wire:click="associateChurchToSelectedUser">
                                    {{ __('Associar igreja') }}
                                </flux:button>
                            </div>
                        </div>
                    </article>
                </section>

                <section class="rounded-xl border border-slate-200">
                    <header class="border-b border-slate-200 bg-slate-50 px-4 py-3">
                        <h3 class="text-sm font-semibold text-slate-800">{{ __('Treinamentos cursados') }}</h3>
                    </header>
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-3xl text-left text-sm">
                            <thead class="text-xs uppercase text-slate-500">
                                <tr class="border-b border-slate-200">
                                    <th class="px-3 py-2">{{ __('Treinamento') }}</th>
                                    <th class="px-3 py-2">{{ __('Curso') }}</th>
                                    <th class="px-3 py-2">{{ __('Igreja sede') }}</th>
                                    <th class="px-3 py-2 text-right">{{ __('Link') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                @forelse ($selectedUserTrainings as $training)
                                    <tr wire:key="teacher-modal-training-{{ $training->id }}" class="odd:bg-white even:bg-slate-50">
                                        <td class="px-3 py-2 font-semibold text-slate-900">#{{ $training->id }}</td>
                                        <td class="px-3 py-2 text-slate-700">
                                            {{ trim((string) $training->course?->type . ' ' . (string) $training->course?->name) ?: __('Não informado') }}
                                        </td>
                                        <td class="px-3 py-2 text-slate-700">{{ $training->church?->name ?: __('Não informada') }}</td>
                                        <td class="px-3 py-2 text-right">
                                            <a href="{{ route('app.teacher.trainings.show', $training) }}"
                                                class="text-sm font-semibold text-sky-700 hover:underline">
                                                {{ __('Ver treinamento') }}
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-3 py-6 text-center text-sm text-slate-600">
                                            {{ __('Nenhum treinamento encontrado para este usuário.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>

            <div class="sticky bottom-0 z-20 border-t border-sky-800 bg-sky-950 px-6 py-4">
                <div class="flex justify-end">
                    <x-src.btn-silver type="button" wire:click="closeUnlinkedUserModal">
                        {{ __('Fechar') }}
                    </x-src.btn-silver>
                </div>
            </div>
        </div>
    </flux:modal>

    <livewire:pages.app.teacher.church.create-modal wire:key="teacher-church-create-modal" />
</div>
