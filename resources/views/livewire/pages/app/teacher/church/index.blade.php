<div>
    <x-src.toolbar.header :title="__('Gerenciamento de Igrejas')" :description="__('Igrejas relacionadas aos seus treinamentos, incluindo sua igreja de vínculo.')" fixed-route-name="app.teacher.churches.index" />
    <div class="relative">
        <x-src.toolbar.nav>
            <x-src.toolbar.button :label="__('Nova igreja')" icon="plus" :tooltip="__('Cadastrar nova igreja')"
                x-on:click.prevent="$dispatch('open-teacher-church-create-modal')" />

            <div class="ml-auto w-full min-w-64 max-w-md">
                <flux:input wire:model.live.debounce.300ms="churchSearch"
                    :placeholder="__('Buscar igreja ou usuário por nome')" />
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
                                <a href="{{ route('app.teacher.churches.show', $userResult->church) }}"
                                    class="mb-1 flex items-center gap-3 rounded-xl px-2 py-2 transition hover:bg-slate-100">
                                    <div
                                        class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-slate-200 text-xs font-bold uppercase text-slate-700">
                                        {{ $userResult->initials() }}
                                    </div>
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
                        <th class="px-3 py-2">{{ __('Ordem') }}</th>
                        <th class="px-3 py-2 text-center">{{ __('Logo') }}</th>
                        <th class="px-3 py-2">{{ __('Igreja / Pastor') }}</th>
                        <th class="px-3 py-2">{{ __('Contato / E-mail') }}</th>
                        <th class="px-3 py-2">{{ __('Cidade / UF') }}</th>
                        <th class="px-3 py-2 text-center">{{ __('Total de membros') }}</th>
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
                            <td colspan="7" class="px-3 py-8 text-center text-sm text-slate-600">
                                {{ __('Nenhuma igreja encontrada para os seus treinamentos.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-5">
            {{ $churches->links() }}
        </div>
    </section>

    <livewire:pages.app.teacher.church.create-modal wire:key="teacher-church-create-modal" />
</div>
