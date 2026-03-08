<div>
    <flux:modal name="director-inventory-edit-modal" wire:model="showModal" class="max-w-5xl w-full bg-sky-950! p-0!">
        <div class="flex h-[min(90vh,52rem)] flex-col overflow-hidden rounded-2xl">
            <header class="sticky top-0 z-20 border-b border-sky-800 bg-sky-950 px-6 py-4 text-sky-50">
                <h3 class="text-lg font-semibold">{{ __('Editar estoque') }}</h3>
                <p class="text-sm opacity-90">
                    {{ __('Atualize o responsável, status e dados de localização do estoque.') }}
                </p>
            </header>

            <div class="min-h-0 flex-1 overflow-y-auto bg-white px-6 py-6">
                <div class="space-y-8 pb-2">
                    <section class="space-y-5">
                        <div class="flex flex-wrap gap-x-4 gap-y-8">
                            <x-src.form.input name="director-inventory-edit-name" wire:model.live="name" label="Nome"
                                type="text" width_basic="280" required />
                            <x-src.form.select name="director-inventory-edit-kind" wire:model.live="kind" label="Tipo"
                                width_basic="180" :options="$kindOptions" required />
                            <x-src.form.select name="director-inventory-edit-status" wire:model.live="status"
                                label="Status" width_basic="180" :options="$statusOptions" required />
                            @if ($kind === 'teacher')
                                <x-src.form.select name="director-inventory-edit-user" wire:model.live="user_id"
                                    label="Professor responsável" width_basic="320" :options="$teacherOptions" required />
                            @endif
                            <x-src.form.input name="director-inventory-edit-phone" wire:model.live="phone"
                                label="Telefone" type="text" width_basic="180" />
                            <x-src.form.input name="director-inventory-edit-email" wire:model.live="email"
                                label="Email" type="email" width_basic="240" />
                        </div>
                    </section>

                    <section class="space-y-5">
                        <div class="space-y-5">
                            <livewire:address-fields wire:model="address" title="Endereço do estoque"
                                wire:key="director-inventory-edit-address-fields-{{ $inventoryId }}" />

                            <div class="flex flex-wrap gap-x-4 gap-y-8">
                                <x-src.form.textarea name="director-inventory-edit-notes" wire:model.live="notes"
                                    label="Observações" rows="4" />
                            </div>
                        </div>
                    </section>
                </div>
            </div>

            <footer class="sticky bottom-0 z-20 border-t border-sky-800 bg-sky-950 px-6 py-4 text-sky-50">
                <div class="flex justify-between gap-3">
                    <x-src.btn-silver type="button" wire:click="closeModal">{{ __('Cancelar') }}</x-src.btn-silver>
                    <x-src.btn-gold type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save">
                        <span wire:loading.remove wire:target="save">{{ __('Salvar alterações') }}</span>
                        <span wire:loading wire:target="save">{{ __('Salvando...') }}</span>
                    </x-src.btn-gold>
                </div>
            </footer>
        </div>
    </flux:modal>
</div>
