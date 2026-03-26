<div>
    <flux:modal name="director-inventory-transfer-modal" wire:model="showModal" class="max-w-4xl w-[calc(100%-4px)] mx-auto bg-sky-950! p-0! max-h-[calc(100vh-4px)]! overflow-hidden">
        <div class="flex h-[min(90vh,44rem)] flex-col overflow-hidden rounded-2xl">
            <header class="sticky top-0 z-20 border-b border-sky-800 bg-sky-950 px-6 py-4 text-sky-50">
                <h3 class="text-lg font-semibold">{{ __('Transferir estoque') }}</h3>
                <p class="text-sm opacity-90">
                    {{ __('A transferência gera saída no estoque atual e entrada no estoque de destino com o mesmo lote rastreável.') }}
                </p>
            </header>

            <div class="min-h-0 flex-1 overflow-y-auto bg-white px-6 py-6">
                <div class="mb-5 rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <div class="text-sm font-semibold text-slate-900">{{ __('Origem: :name', ['name' => $inventory->name]) }}</div>
                </div>

                <div class="flex flex-wrap gap-x-4 gap-y-8">
                    <x-src.form.select name="director-inventory-transfer-destination"
                        wire:model.live="destination_inventory_id" label="Estoque de destino" width_basic="320"
                        :options="$destinationOptions" required />
                    <x-src.form.select name="director-inventory-transfer-material" wire:model.live="material_id"
                        label="Material" width_basic="320" :options="$materialOptions" required />
                    <x-src.form.input name="director-inventory-transfer-quantity" wire:model.live="quantity"
                        label="Quantidade" type="number" width_basic="180" min="1" required />
                    <x-src.form.textarea name="director-inventory-transfer-notes" wire:model.live="notes"
                        label="Observação" rows="4" />
                </div>
            </div>

            <footer class="sticky bottom-0 z-20 border-t border-sky-800 bg-sky-950 px-6 py-4 text-sky-50">
                <div class="flex justify-between gap-3">
                    <x-src.btn-silver type="button" wire:click="closeModal">{{ __('Cancelar') }}</x-src.btn-silver>
                    <x-src.btn-gold type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save">
                        <span wire:loading.remove wire:target="save">{{ __('Transferir') }}</span>
                        <span wire:loading wire:target="save">{{ __('Transferindo...') }}</span>
                    </x-src.btn-gold>
                </div>
            </footer>
        </div>
    </flux:modal>
</div>
