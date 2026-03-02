<div>
    <x-src.toolbar.header :title="__('Gerenciamento de Igrejas')" :description="__('Igrejas relacionadas aos seus treinamentos, incluindo sua igreja de vínculo.')"
        fixed-route-name="app.teacher.churches.index" />
    <x-src.toolbar.nav>
        <x-src.toolbar.button href="#" :label="__('Nova igreja')" icon="plus" :tooltip="__('Cadastrar nova igreja')"
            x-on:click.prevent="$dispatch('open-teacher-church-create-modal')" />

        <div class="ml-auto w-full min-w-64 max-w-md">
            <flux:input wire:model.live.debounce.300ms="churchSearch" :placeholder="__('Buscar igreja por nome')" />
        </div>
    </x-src.toolbar.nav>

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
                        <th class="px-3 py-2">{{ __('Logo') }}</th>
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
                            class="cursor-pointer bg-white/70 transition hover:bg-slate-100/90"
                            data-row-link="{{ route('app.teacher.churches.show', $church) }}"
                            x-on:click="window.location = $el.dataset.rowLink">
                            <td class="px-3 py-3 align-top">
                                {{ ($churches->currentPage() - 1) * $churches->perPage() + $loop->iteration }}
                            </td>
                            <td class="px-3 py-3 align-top">
                                @php
                                    $logoPath = trim((string) $church->getRawOriginal('logo'));
                                    $logoUrl =
                                        $logoPath !== '' && \Illuminate\Support\Facades\Storage::disk('public')->exists($logoPath)
                                            ? \Illuminate\Support\Facades\Storage::disk('public')->url($logoPath)
                                            : asset('images/svg/church.svg');
                                @endphp
                                <div class="flex justify-center">
                                    <img src="{{ $logoUrl }}" alt="{{ __('Logo da igreja') }}"
                                        class="h-10 w-10 rounded-lg border border-slate-200 bg-white object-cover">
                                </div>
                            </td>
                            <td class="px-3 py-3 align-top">
                                <a class="font-semibold text-slate-900 hover:underline"
                                    href="{{ route('app.teacher.churches.show', $church) }}">
                                    {{ $church->name }}
                                </a>
                                <div class="text-xs text-slate-600">{{ $church->pastor ?: __('Pastor não informado') }}
                                </div>
                            </td>
                            <td class="px-3 py-3 align-top">
                                <div class="font-medium text-slate-800">
                                    {{ $church->contact ?: __('Contato não informado') }}</div>
                                <div class="text-xs text-slate-600">
                                    {{ $church->contact_email ?: __('E-mail não informado') }}</div>
                            </td>
                            <td class="px-3 py-3 align-top">
                                <div class="font-medium text-slate-800">
                                    {{ $church->city ?: __('Cidade não informada') }}</div>
                                <div class="text-xs text-slate-600">{{ $church->state ?: __('UF não informada') }}
                                </div>
                            </td>
                            <td class="px-3 py-3 text-center align-top">
                                <span
                                    class="inline-flex rounded-full bg-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-700">
                                    {{ $church->total_members_count }}
                                </span>
                            </td>
                            <td class="px-3 py-3 align-top">
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
