<div class="space-y-6">
    <section
        class="rounded-2xl border border-amber-300/20 bg-linear-to-br from-slate-100 via-white to-slate-200 p-4 shadow-lg sm:p-6">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-4 border-b-2 border-slate-200/80 pb-4">
            <div class="space-y-1">
                <h2 class="text-xl font-semibold text-slate-900" style="font-family: 'Cinzel', serif;">
                    {{ __('Estoques sob sua responsabilidade') }}
                </h2>
                <p class="text-sm text-slate-600">
                    {{ __('Você visualiza e opera apenas os estoques delegados ao seu perfil de professor.') }}
                </p>
            </div>
        </div>

        <div class="flex flex-wrap gap-x-4 gap-y-8">
            <x-src.form.input name="teacher-inventory-search" wire:model.live.debounce.300ms="search"
                label="Buscar estoque" type="text" width_basic="280" />
            <x-src.form.select name="teacher-inventory-status-filter" wire:model.live="statusFilter" label="Status"
                width_basic="180" :options="$statusOptions" />
        </div>

        <div class="mt-6 overflow-x-auto rounded-2xl border border-slate-200 bg-white/95 shadow-sm">
            <table class="w-full min-w-4xl text-left text-sm">
                <thead class="bg-slate-100 text-xs uppercase tracking-wide text-slate-600">
                    <tr>
                        <th class="px-4 py-3">{{ __('Nome') }}</th>
                        <th class="px-4 py-3">{{ __('Professor responsável') }}</th>
                        <th class="px-4 py-3">{{ __('Cidade / Estado') }}</th>
                        <th class="px-4 py-3">{{ __('Status') }}</th>
                        <th class="px-4 py-3">{{ __('SKUs com saldo') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($inventories as $inventory)
                        <tr wire:key="teacher-inventory-row-{{ $inventory->id }}"
                            class="cursor-pointer border-t border-slate-200 transition odd:bg-white even:bg-slate-50 hover:bg-slate-100/80"
                            onclick="window.location='{{ route('app.teacher.inventory.show', $inventory) }}'">
                            <td class="px-4 py-4">
                                <div class="font-semibold text-slate-900">{{ $inventory->name }}</div>
                                <div class="text-xs text-slate-500">
                                    {{ $inventory->email ?: __('Sem email') }} · {{ $inventory->phone ?: __('Sem telefone') }}
                                </div>
                            </td>
                            <td class="px-4 py-4 text-slate-700">
                                {{ $inventory->responsibleUser?->name ?: __('Não informado') }}
                            </td>
                            <td class="px-4 py-4 text-slate-700">
                                {{ trim(implode(' / ', array_filter([$inventory->city, $inventory->state]))) ?: __('Não informado') }}
                            </td>
                            <td class="px-4 py-4">
                                <span
                                    class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $inventory->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-700' }}">
                                    {{ $inventory->is_active ? __('Ativo') : __('Inativo') }}
                                </span>
                            </td>
                            <td class="px-4 py-4 text-slate-700">{{ (int) $inventory->active_skus_count }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-sm text-slate-500">
                                <div class="mx-auto max-w-md space-y-2">
                                    <div class="text-base font-semibold text-slate-700">
                                        {{ __('Nenhum estoque delegado no momento') }}
                                    </div>
                                    <div>
                                        {{ __('Quando um diretor vincular um estoque ao seu usuário, ele aparecerá aqui automaticamente.') }}
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($inventories->hasPages())
            <div class="mt-5">
                {{ $inventories->links(data: ['scrollTo' => false]) }}
            </div>
        @endif
    </section>
</div>
