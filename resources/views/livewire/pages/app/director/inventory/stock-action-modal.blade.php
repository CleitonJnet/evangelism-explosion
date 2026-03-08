<div>
    <flux:modal name="director-inventory-stock-action-modal" wire:model="showModal"
        class="max-w-4xl w-full bg-sky-950! p-0!">
        <div class="flex h-[min(90vh,46rem)] flex-col overflow-hidden rounded-2xl">
            <header class="sticky top-0 z-20 border-b border-sky-800 bg-sky-950 px-6 py-4 text-sky-50">
                <h3 class="text-lg font-semibold">{{ $modeMeta['title'] }}</h3>
                <p class="text-sm opacity-90">{{ $modeMeta['description'] }}</p>
            </header>

            <div class="min-h-0 flex-1 overflow-y-auto bg-white px-6 py-6">
                <div class="mb-5 rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <div class="text-sm font-semibold text-slate-900">{{ $inventory->name }}</div>
                    <div class="text-xs text-slate-500">
                        {{ $inventory->kind === 'central' ? __('Estoque central') : __('Estoque local de professor') }}
                    </div>
                </div>

                <div class="flex flex-wrap gap-x-4 gap-y-8">
                    <x-src.form.select name="director-inventory-stock-action-material" wire:model.live="material_id"
                        label="Material" width_basic="320" :options="$materialOptions" required />

                    @if ($mode === 'adjustment')
                        <x-src.form.input name="director-inventory-stock-action-target"
                            wire:model.live="target_quantity" :label="$modeMeta['quantity_label']" type="number"
                            width_basic="180" min="0" required />
                    @else
                        <x-src.form.input name="director-inventory-stock-action-quantity" wire:model.live="quantity"
                            :label="$modeMeta['quantity_label']" type="number" width_basic="180" min="1" required />
                    @endif

                    <x-src.form.textarea name="director-inventory-stock-action-notes" wire:model.live="notes"
                        label="Observação" rows="4" />
                </div>
            </div>

            <footer class="sticky bottom-0 z-20 border-t border-sky-800 bg-sky-950 px-6 py-4 text-sky-50">
                <div class="flex justify-between gap-3">
                    <x-src.btn-silver type="button" wire:click="closeModal">{{ __('Cancelar') }}</x-src.btn-silver>
                    <x-src.btn-gold type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save">
                        <span wire:loading.remove wire:target="save">{{ $modeMeta['action'] }}</span>
                        <span wire:loading wire:target="save">{{ __('Salvando...') }}</span>
                    </x-src.btn-gold>
                </div>
            </footer>
        </div>
    </flux:modal>
</div>
