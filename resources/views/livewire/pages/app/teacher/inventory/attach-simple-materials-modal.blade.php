<div>
    @php
        $resolveMaterialPhotoUrl = static function (?string $photoPath): string {
            $normalizedPhoto = trim((string) $photoPath);

            if ($normalizedPhoto !== '') {
                return \Illuminate\Support\Facades\Storage::disk('public')->url($normalizedPhoto);
            }

            return asset('images/logo/ee-gold.webp');
        };
    @endphp

    <flux:modal name="teacher-inventory-attach-simple-materials-modal" wire:model="showModal"
        class="max-w-5xl w-full bg-sky-950! p-0!">
        <div class="flex h-[min(90vh,52rem)] flex-col overflow-hidden rounded-2xl">
            <header class="sticky top-0 z-20 border-b border-sky-800 bg-sky-950 px-6 py-4 text-sky-50">
                <div class="space-y-1">
                    <h3 class="text-lg font-semibold">{{ __('Adicionar itens simples ao estoque') }}</h3>
                    <p class="text-sm opacity-90">
                        {{ __('Selecione itens simples já cadastrados no sistema para vinculá-los a este estoque do professor, mesmo quando a entrada física tenha acontecido por outro meio.') }}
                    </p>
                </div>
            </header>

            <div class="min-h-0 flex-1 overflow-y-auto bg-white px-6 py-6">
                <div class="mb-5 rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <div class="text-sm font-semibold text-slate-900">{{ $inventory->name }}</div>
                    <div class="text-xs text-slate-500">
                        {{ __('Seleção múltipla de itens simples elegíveis para este estoque.') }}
                    </div>
                </div>

                <div class="mb-5 flex flex-wrap items-end justify-between gap-4">
                    <x-src.form.input name="teacher-inventory-attach-material-search"
                        wire:model.live.debounce.300ms="search" label="Buscar item simples" type="text"
                        width_basic="320" autofocus />

                    <div class="rounded-full border border-sky-200 bg-sky-50 px-3 py-2 text-sm font-semibold text-sky-800">
                        {{ __('Selecionados: :count', ['count' => count($selectedMaterialIds)]) }}
                    </div>
                </div>

                @if ($hasAvailableMaterials)
                    <div class="overflow-x-auto rounded-xl border border-sky-200 bg-sky-50/35">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-sky-100 text-xs uppercase text-sky-900">
                                <tr>
                                    <th class="w-16 px-4 py-3 text-center">{{ __('Ok') }}</th>
                                    <th class="w-24 px-4 py-3 text-center">{{ __('Foto') }}</th>
                                    <th class="px-4 py-3">{{ __('Item simples') }}</th>
                                    <th class="w-40 px-4 py-3 text-center">{{ __('Preço') }}</th>
                                    <th class="w-44 px-4 py-3 text-center">{{ __('Mín. do professor') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white">
                                @foreach ($materials as $material)
                                    @php($isSelected = in_array((int) $material->id, $selectedMaterialIds, true))
                                    <tr wire:key="teacher-inventory-attach-material-{{ $material->id }}"
                                        class="cursor-pointer border-t border-sky-200 transition odd:bg-white even:bg-sky-50/40 hover:bg-sky-100/65 {{ $isSelected ? 'bg-sky-100/80 text-slate-900' : 'text-slate-900' }}"
                                        wire:click="toggleMaterial({{ $material->id }})">
                                        <td class="px-4 py-3 text-center">
                                            <span
                                                class="inline-flex size-7 items-center justify-center rounded-full border text-sm font-bold {{ $isSelected ? 'border-sky-900 bg-sky-900 text-white' : 'border-slate-300 bg-white text-transparent' }}">
                                                &#x2713;
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <img src="{{ $resolveMaterialPhotoUrl($material->photo) }}" alt="{{ __('Foto de :name', ['name' => $material->name]) }}"
                                                onerror="this.onerror=null;this.src='{{ asset('images/logo/ee-gold.webp') }}';"
                                                class="mx-auto h-12 w-12 rounded-lg object-cover">
                                        </td>
                                        <td class="px-4 py-3 font-medium">{{ $material->name }}</td>
                                        <td class="px-4 py-3 text-center font-semibold">
                                            {{ \App\Helpers\MoneyHelper::format_money($material->price) ?: __('Não informado') }}
                                        </td>
                                        <td class="px-4 py-3 text-center" wire:click.stop>
                                            <x-src.form.input
                                                name="teacher-inventory-attach-minimum-stock-{{ $material->id }}"
                                                wire:model.live="minimumStockByMaterialId.{{ $material->id }}"
                                                label=""
                                                type="number"
                                                width_basic="120"
                                                min="0"
                                                placeholder="0"
                                            />
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5">
                        <div class="text-base font-semibold text-amber-900">
                            {{ __('Nenhum item simples disponível') }}
                        </div>
                        <p class="mt-2 text-sm text-amber-900/90">
                            {{ __('Todos os itens simples ativos já estão vinculados a este estoque ou nenhum item simples ativo foi encontrado para o filtro informado.') }}
                        </p>
                    </div>
                @endif

                @error('selectedMaterialIds')
                    <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                        {{ $message }}
                    </div>
                @enderror
                @error('selectedMaterialIds.*')
                    <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                        {{ $message }}
                    </div>
                @enderror
                @error('minimumStockByMaterialId.*')
                    <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <footer class="sticky bottom-0 z-20 border-t border-sky-800 bg-sky-950 px-6 py-4 text-sky-50">
                <div class="flex items-center justify-between gap-3">
                    <x-src.btn-silver type="button" wire:click="closeModal" wire:loading.attr="disabled" wire:target="save">
                        {{ __('Cancelar') }}
                    </x-src.btn-silver>
                    @if ($hasAvailableMaterials)
                        <x-src.btn-gold type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save">
                            <span wire:loading.remove wire:target="save">{{ __('Salvar itens selecionados') }}</span>
                            <span wire:loading wire:target="save">{{ __('Salvando...') }}</span>
                        </x-src.btn-gold>
                    @endif
                </div>
            </footer>
        </div>
    </flux:modal>
</div>
