<div class="space-y-6" wire:poll.15s>
    @php
        $resolveMaterialPhotoUrl = static function (?string $photoPath): string {
            $normalizedPhoto = trim((string) $photoPath);

            if ($normalizedPhoto !== '') {
                return \Illuminate\Support\Facades\Storage::disk('public')->url($normalizedPhoto);
            }

            return asset('images/logo/ee-gold.webp');
        };
    @endphp
    <flux:modal name="teacher-inventory-delete-modal" wire:model="showDeleteModal" class="max-w-md">
        <div class="space-y-4">
            <div>
                <flux:heading size="lg">{{ __('Excluir estoque') }}</flux:heading>
                <flux:text class="mt-2 text-sm text-slate-600">
                    @if ($inventoryDeletionBlockedReason !== '')
                        {{ $inventoryDeletionBlockedReason }}
                    @else
                        {{ __('Tem certeza que deseja excluir este estoque? Esta ação não pode ser desfeita.') }}
                    @endif
                </flux:text>
                <flux:text class="mt-1 text-sm font-semibold text-slate-900">
                    {{ $inventory->name }}
                </flux:text>
            </div>

            <div class="flex justify-end gap-2">
                <flux:button type="button" variant="ghost" wire:click="closeDeleteModal">
                    {{ $inventoryDeletionBlockedReason !== '' ? __('Fechar') : __('Cancelar') }}
                </flux:button>

                @if ($inventoryDeletionBlockedReason === '')
                    <flux:button type="button" variant="danger" wire:click="deleteInventory" wire:loading.attr="disabled"
                        wire:target="deleteInventory">
                        {{ __('Confirmar exclusão') }}
                    </flux:button>
                @endif
            </div>
        </div>
    </flux:modal>

    <section
        class="rounded-2xl border border-amber-300/20 bg-linear-to-br from-slate-100 via-white to-slate-200 p-4 shadow-lg sm:p-6">
        <div class="flex flex-wrap items-start justify-between gap-4 border-b-2 border-slate-200/80 pb-4">
            @php
                $formattedAddress = trim(
                    implode(
                        ', ',
                        array_filter([
                            $inventory->street,
                            $inventory->number,
                            $inventory->complement,
                            $inventory->district,
                            $inventory->city,
                            $inventory->state,
                            $inventory->postal_code,
                        ]),
                    ),
                );

                $headerMeta = array_values(array_filter([$inventory->responsibleUser?->name]));
                $hasContact = filled($inventory->phone) || filled($inventory->email);
            @endphp

            <div class="space-y-2">
                <h2 class="text-2xl font-semibold text-slate-900" style="font-family: 'Cinzel', serif;">
                    {{ $inventory->name }}
                </h2>
                @if ($headerMeta !== [])
                    <p class="text-sm font-semibold text-slate-700">
                        {{ implode(' · ', $headerMeta) }}
                    </p>
                @endif
                @if ($formattedAddress !== '')
                    <p class="max-w-4xl text-sm text-slate-600">
                        {{ $formattedAddress }}
                    </p>
                @endif
            </div>

            @if ($hasContact)
                <div class="max-w-sm text-right">
                    <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                        {{ __('Contato') }}
                    </div>
                    <div class="mt-2 space-y-1 text-sm font-semibold text-slate-900">
                        @if ($inventory->phone)
                            <div>{{ $inventory->phone }}</div>
                        @endif
                        @if ($inventory->email)
                            <div>{{ $inventory->email }}</div>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <div class="mt-4 flex flex-wrap items-center gap-2">
            <span class="rounded-full bg-sky-100 px-3 py-1 text-xs font-semibold text-sky-800">
                {{ __('Estoque local de professor') }}
            </span>
            <span
                class="rounded-full px-3 py-1 text-xs font-semibold {{ $inventory->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-700' }}">
                {{ $inventory->is_active ? __('Ativo') : __('Inativo') }}
            </span>
        </div>
    </section>

    @if ($lowStockItems->isNotEmpty())
        <section class="rounded-2xl border border-amber-200 bg-amber-50 p-5 shadow-sm">
            <div class="border-b border-amber-200 pb-3">
                <h3 class="text-lg font-semibold text-amber-900">{{ __('Alertas de estoque mínimo') }}</h3>
                <p class="text-sm text-amber-800">{{ __('Materiais com saldo abaixo do mínimo configurado ou com saldo zerado.') }}</p>
            </div>

            <div class="mt-4 flex flex-wrap gap-3">
                @foreach ($lowStockItems as $alertItem)
                    <div class="rounded-xl border px-4 py-3 text-sm {{ (int) $alertItem->available_alert_quantity === 0 ? 'border-rose-200 bg-rose-50 text-rose-900' : 'border-amber-200 bg-white text-amber-900' }}">
                        <span class="font-semibold">{{ $alertItem->name }}</span>
                        <span class="block text-xs opacity-80">
                            @if ($alertItem->type === 'composite')
                                {{ __('Pode compor: :current / Mínimo: :minimum', ['current' => $alertItem->available_alert_quantity, 'minimum' => $alertItem->minimum_stock]) }}
                            @else
                                {{ __('Saldo: :current / Mínimo: :minimum', ['current' => $alertItem->available_alert_quantity, 'minimum' => $alertItem->minimum_stock]) }}
                            @endif
                        </span>
                        @if ((int) $alertItem->available_alert_quantity === 0)
                            <span class="mt-2 inline-block rounded-full bg-rose-100 px-2.5 py-1 text-xs font-semibold text-rose-800">
                                {{ __('Saldo zerado') }}
                            </span>
                        @endif
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    <div class="space-y-6">
        <section class="rounded-2xl border border-slate-200 bg-white/95 p-5 shadow-sm">
            <div class="border-b border-slate-200 pb-3 mb-10">
                <h3 class="text-lg font-semibold text-slate-900">{{ __('Saldo atual por produto') }}</h3>
                <p class="text-sm text-slate-600">
                    {{ __('Use a busca abaixo para localizar rapidamente produtos compostos e itens simples neste estoque.') }}
                </p>
            </div>

            <div class="mt-5 flex flex-wrap gap-x-4 gap-y-8">
                <x-src.form.input name="teacher-inventory-material-search"
                    wire:model.live.debounce.300ms="materialSearch" label="Buscar produto" type="text"
                    width_basic="320" autofocus />
            </div>

            <div class="mt-6 space-y-8">
                <section>
                    <div class="pb-3">
                        <h4 class="text-base font-semibold text-slate-900">{{ __('Produtos compostos') }}</h4>
                        <p class="text-sm text-slate-600">
                            {{ __('Aqui aparecem os kits e produtos compostos, com a quantidade de componentes vinculados e o total que ainda pode ser composto a partir dos itens simples disponíveis.') }}
                        </p>
                    </div>

                    <div class="mt-3 overflow-x-auto rounded-xl border border-emerald-200 bg-emerald-50/40">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-emerald-100 text-xs uppercase text-emerald-900">
                                <tr>
                                    <th class="w-24 px-4 py-3 text-center">{{ __('Foto') }}</th>
                                    <th class="px-4 py-3">{{ __('Produto composto') }}</th>
                                    <th class="w-40 px-4 py-3 text-center">{{ __('Preço') }}</th>
                                    <th class="w-36 px-4 py-3 text-center">{{ __('Componentes') }}</th>
                                    <th class="w-36 px-4 py-3 text-center">{{ __('Pode compor') }}</th>
                                    <th class="w-36 px-4 py-3 text-center">{{ __('Mínimo') }}</th>
                                    <th class="w-44 px-4 py-3 text-center">{{ __('Alerta') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white">
                                @forelse ($compositeBalances as $balance)
                                    <tr class="cursor-pointer border-t border-emerald-200 transition odd:bg-white even:bg-emerald-50/45 hover:bg-emerald-100/60 {{ $balance->is_active ? 'text-slate-900' : 'text-slate-400' }}"
                                        onclick="window.Livewire.dispatch('open-teacher-material-action-modal', { materialId: {{ $balance->id }}, tab: 'exit' }); return false;">
                                        <td class="px-4 py-3">
                                            <img src="{{ $resolveMaterialPhotoUrl($balance->photo) }}" alt="{{ __('Foto de :name', ['name' => $balance->name]) }}"
                                                onerror="this.onerror=null;this.src='{{ asset('images/logo/ee-gold.webp') }}';"
                                                class="mx-auto h-12 w-12 rounded-lg object-cover">
                                        </td>
                                        <td class="px-4 py-3 font-medium">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <span class="truncate">{{ $balance->name }}</span>
                                                @if (! $balance->is_active)
                                                    <span class="rounded-full bg-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-700">
                                                        {{ __('Inativo') }}
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-center font-semibold">
                                            {{ \App\Helpers\MoneyHelper::format_money($balance->price) ?: __('Não informado') }}
                                        </td>
                                        <td class="px-4 py-3 text-center">{{ $balance->components_count }}</td>
                                        <td class="px-4 py-3 text-center font-semibold text-sky-800">
                                            {{ __('Até :quantity', ['quantity' => (int) $balance->composable_quantity]) }}
                                        </td>
                                        <td class="px-4 py-3 text-center">{{ $balance->minimum_stock }}</td>
                                        <td class="px-4 py-3 text-center">
                                            @if ((int) $balance->composable_quantity === 0)
                                                <span class="inline-block max-w-full truncate rounded-full bg-rose-100 px-2.5 py-1 text-xs font-semibold text-rose-800">
                                                    {{ __('Saldo zerado') }}
                                                </span>
                                            @elseif ($balance->minimum_stock > 0 && $balance->composable_quantity < $balance->minimum_stock)
                                                <span class="inline-block max-w-full truncate rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-800">
                                                    {{ __('Abaixo do mínimo') }}
                                                </span>
                                            @else
                                                <span class="inline-block max-w-full truncate text-slate-400">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-4 py-6 text-center text-sm text-slate-500">
                                            <div class="mx-auto max-w-sm space-y-2">
                                                <div class="text-base font-semibold text-slate-700">
                                                    {{ __('Nenhum produto composto encontrado') }}
                                                </div>
                                                <div>
                                                    {{ __('Este estoque ainda não possui produtos compostos com saldo para a busca atual.') }}
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </section>

                <section class="border-t border-slate-200 pt-6">
                    <div class="pb-3">
                        <h4 class="text-base font-semibold text-slate-900">{{ __('Itens simples') }}</h4>
                        <p class="text-sm text-slate-600">
                            {{ __('Esta tabela lista apenas os itens simples cadastrados no estoque, usados tanto individualmente quanto na composição de produtos compostos.') }}
                        </p>
                    </div>

                    <div class="mt-3 overflow-x-auto rounded-xl border border-sky-200 bg-sky-50/35">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-sky-100 text-xs uppercase text-sky-900">
                                <tr>
                                    <th class="w-24 px-4 py-3 text-center">{{ __('Foto') }}</th>
                                    <th class="px-4 py-3">{{ __('Item simples') }}</th>
                                    <th class="w-40 px-4 py-3 text-center">{{ __('Preço') }}</th>
                                    <th class="w-36 px-4 py-3 text-center">{{ __('Saldo') }}</th>
                                    <th class="w-36 px-4 py-3 text-center">{{ __('Mínimo') }}</th>
                                    <th class="w-44 px-4 py-3 text-center">{{ __('Alerta') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white">
                                @forelse ($simpleBalances as $balance)
                                    <tr class="cursor-pointer border-t border-sky-200 transition odd:bg-white even:bg-sky-50/40 hover:bg-sky-100/65 {{ $balance->is_active ? 'text-slate-900' : 'text-slate-400' }}"
                                        onclick="window.Livewire.dispatch('open-teacher-material-action-modal', { materialId: {{ $balance->id }}, tab: 'exit' }); return false;">
                                        <td class="px-4 py-3">
                                            <img src="{{ $resolveMaterialPhotoUrl($balance->photo) }}" alt="{{ __('Foto de :name', ['name' => $balance->name]) }}"
                                                onerror="this.onerror=null;this.src='{{ asset('images/logo/ee-gold.webp') }}';"
                                                class="mx-auto h-12 w-12 rounded-lg object-cover">
                                        </td>
                                        <td class="px-4 py-3 font-medium">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <span class="truncate">{{ $balance->name }}</span>
                                                @if (! $balance->is_active)
                                                    <span class="rounded-full bg-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-700">
                                                        {{ __('Inativo') }}
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-center font-semibold">
                                            {{ \App\Helpers\MoneyHelper::format_money($balance->price) ?: __('Não informado') }}
                                        </td>
                                        <td class="px-4 py-3 text-center font-semibold">{{ $balance->current_quantity }}</td>
                                        <td class="px-4 py-3 text-center">{{ $balance->minimum_stock }}</td>
                                        <td class="px-4 py-3 text-center">
                                            @if ((int) $balance->current_quantity === 0)
                                                <span class="inline-block max-w-full truncate rounded-full bg-rose-100 px-2.5 py-1 text-xs font-semibold text-rose-800">
                                                    {{ __('Saldo zerado') }}
                                                </span>
                                            @elseif ($balance->minimum_stock > 0 && $balance->current_quantity < $balance->minimum_stock)
                                                <span class="inline-block max-w-full truncate rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-800">
                                                    {{ __('Abaixo do mínimo') }}
                                                </span>
                                            @else
                                                <span class="inline-block max-w-full truncate text-slate-400">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-6 text-center text-sm text-slate-500">
                                            <div class="mx-auto max-w-sm space-y-2">
                                                <div class="text-base font-semibold text-slate-700">
                                                    {{ __('Nenhum item simples encontrado') }}
                                                </div>
                                                <div>
                                                    {{ __('Este estoque ainda não possui itens simples com saldo para a busca atual.') }}
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </section>
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white/95 p-5 shadow-sm">
            <div class="border-b border-slate-200 pb-3">
                <h3 class="text-lg font-semibold text-slate-900">{{ __('Histórico auditável') }}</h3>
                <p class="text-sm text-slate-600">
                    {{ __('Todas as movimentações manuais registradas neste estoque delegado.') }}
                </p>
            </div>

            <div class="mt-5 flex flex-wrap gap-x-4 gap-y-8">
                <x-src.form.select name="teacher-inventory-movement-type-filter" wire:model.live="movementTypeFilter"
                    label="Tipo de movimento" width_basic="240" :value="$movementTypeFilter" :options="$movementTypeOptions" />
            </div>

            <div class="mt-5 overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase text-slate-600">
                        <tr>
                            <th class="px-4 py-3">{{ __('Data / Hora') }}</th>
                            <th class="px-4 py-3">{{ __('Material') }}</th>
                            <th class="px-4 py-3">{{ __('Tipo') }}</th>
                            <th class="px-4 py-3">{{ __('Qtd.') }}</th>
                            <th class="px-4 py-3">{{ __('Saldo após') }}</th>
                            <th class="px-4 py-3">{{ __('Usuário') }}</th>
                            <th class="px-4 py-3">{{ __('Observação') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($movements as $movement)
                            <tr class="border-t border-slate-200">
                                <td class="px-4 py-3 text-slate-700">
                                    @php($movementTimestamp = $movement->created_at?->toIso8601String())
                                    <time datetime="{{ $movementTimestamp }}" title="{{ $movementTimestamp }}"
                                        x-data="{
                                            iso: @js($movementTimestamp),
                                            localDateTime() {
                                                if (!this.iso) {
                                                    return '-';
                                                }

                                                const date = new Date(this.iso);

                                                if (Number.isNaN(date.getTime())) {
                                                    return this.iso;
                                                }

                                                return new Intl.DateTimeFormat('pt-BR', {
                                                    day: '2-digit',
                                                    month: '2-digit',
                                                    year: 'numeric',
                                                    hour: '2-digit',
                                                    minute: '2-digit',
                                                }).format(date);
                                            },
                                        }"
                                        x-text="localDateTime()">
                                        {{ $movement->created_at?->format('d/m/Y H:i') ?? '-' }}
                                    </time>
                                </td>
                                <td class="px-4 py-3 font-medium text-slate-900">
                                    {{ $movement->material?->name ?: __('Material removido') }}
                                </td>
                                <td class="px-4 py-3 text-slate-700">
                                    <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $movement->typeBadgeClasses() }}">
                                        {{ $movement->typeLabel() }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 font-semibold text-slate-700">{{ $movement->quantity }}</td>
                                <td class="px-4 py-3 text-slate-700">{{ $movement->balance_after ?? __('-') }}</td>
                                <td class="px-4 py-3 text-slate-700">{{ $movement->user?->name ?: __('Sistema') }}</td>
                                <td class="px-4 py-3 text-slate-700">{{ $movement->notes ?: __('-') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-6 text-center text-sm text-slate-500">
                                    <div class="mx-auto max-w-sm space-y-2">
                                        <div class="text-base font-semibold text-slate-700">
                                            {{ __('Nenhuma movimentação encontrada') }}
                                        </div>
                                        <div>
                                            {{ __('Registre uma entrada, saída, ajuste ou perda para iniciar o histórico auditável deste estoque.') }}
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($movements->hasPages())
                <div class="mt-4">
                    {{ $movements->links(data: ['scrollTo' => false]) }}
                </div>
            @endif
        </section>
    </div>

    <livewire:pages.app.teacher.inventory.edit-inventory-modal :inventory-id="$inventory->id"
        wire:key="teacher-inventory-edit-modal-{{ $inventory->id }}" />
    <livewire:pages.app.teacher.inventory.stock-action-modal :inventory-id="$inventory->id"
        wire:key="teacher-inventory-stock-action-modal-{{ $inventory->id }}" />
    @foreach ($compositeBalances as $balance)
        <livewire:pages.app.teacher.inventory.material-action-modal :material-id="$balance->id" :inventory-id="$inventory->id"
            wire:key="teacher-material-action-modal-composite-{{ $balance->id }}" />
    @endforeach
    @foreach ($simpleBalances as $balance)
        <livewire:pages.app.teacher.inventory.material-action-modal :material-id="$balance->id" :inventory-id="$inventory->id"
            wire:key="teacher-material-action-modal-simple-{{ $balance->id }}" />
    @endforeach

    @if ($inventory->notes)
        <section class="rounded-2xl border border-slate-200 bg-white/95 p-5 shadow-sm">
            <div class="border-b border-slate-200 pb-3">
                <h3 class="text-lg font-semibold text-slate-900">{{ __('Observações do estoque') }}</h3>
                <p class="text-sm text-slate-600">
                    {{ __('Notas operacionais ou administrativas cadastradas para este local de estoque.') }}
                </p>
            </div>

            <div class="mt-4 text-sm leading-6 text-slate-700">
                {{ $inventory->notes }}
            </div>
        </section>
    @endif
</div>
