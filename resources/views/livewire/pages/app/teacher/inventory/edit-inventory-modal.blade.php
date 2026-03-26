<div>
    <flux:modal name="teacher-inventory-edit-modal" wire:model="showModal" class="max-w-5xl w-[calc(100%-4px)] mx-auto bg-sky-950! p-0! max-h-[calc(100vh-4px)]! overflow-hidden">
        <div class="flex h-[min(90vh,52rem)] flex-col overflow-hidden rounded-2xl">
            @php
                $isActive = $status === 'active';
                $headerStatusLabel = $isActive ? __('Ativo') : __('Inativo');
                $headerStatusClasses = $isActive
                    ? 'border-emerald-400/40 bg-emerald-500/15 text-emerald-100'
                    : 'border-rose-400/40 bg-rose-500/15 text-rose-100';
            @endphp

            <header class="sticky top-0 z-20 border-b border-sky-800 bg-sky-950 px-6 py-4 text-sky-50">
                <div class="flex items-start justify-between gap-4">
                    <div class="space-y-1">
                        <h3 class="text-lg font-semibold">{{ __('Editar estoque') }}</h3>
                        <p class="text-sm opacity-90">
                            {{ __('Atualize os dados operacionais do estoque delegado sem alterar a vinculação definida pela diretoria.') }}
                        </p>
                    </div>

                    <div class="shrink-0 rounded-full border px-3 py-1 text-sm font-semibold {{ $headerStatusClasses }}">
                        {{ $headerStatusLabel }}
                    </div>
                </div>
            </header>

            <div class="min-h-0 flex-1 overflow-y-auto bg-slate-50 px-6 py-6">
                <div class="space-y-6 pb-2">
                    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="mb-5 flex flex-col gap-1">
                            <h4 class="text-base font-semibold text-slate-900">{{ __('1. Identificação do estoque') }}</h4>
                            <p class="text-sm text-slate-600">
                                {{ __('Revise o nome de identificação usado para a rotina do seu estoque.') }}
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-x-4 gap-y-8">
                            <x-src.form.input name="teacher-inventory-edit-name" wire:model.live="name" label="Nome"
                                type="text" width_basic="320" autofocus required />
                        </div>
                    </section>

                    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="mb-5 flex flex-col gap-1">
                            <h4 class="text-base font-semibold text-slate-900">{{ __('2. Contato') }}</h4>
                            <p class="text-sm text-slate-600">
                                {{ __('Mantenha telefone e email atualizados para comunicação rápida com a equipe.') }}
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-x-4 gap-y-8">
                            <x-src.form.input name="teacher-inventory-edit-phone" wire:model.live="phone"
                                label="Telefone" type="text" width_basic="200" />
                            <x-src.form.input name="teacher-inventory-edit-email" wire:model.live="email"
                                label="Email" type="email" width_basic="280" />
                        </div>
                    </section>

                    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="mb-5 flex flex-col gap-1">
                            <h4 class="text-base font-semibold text-slate-900">{{ __('3. Endereço do estoque') }}</h4>
                            <p class="text-sm text-slate-600">
                                {{ __('Mantenha a localização física atualizada para apoiar entregas, retiradas e conferências presenciais.') }}
                            </p>
                        </div>

                        <livewire:address-fields wire:model="address" title="Endereço do estoque"
                            wire:key="teacher-inventory-edit-address-fields-{{ $inventoryId }}" />
                    </section>

                    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="mb-5 flex flex-col gap-1">
                            <h4 class="text-base font-semibold text-slate-900">{{ __('4. Observações operacionais') }}</h4>
                            <p class="text-sm text-slate-600">
                                {{ __('Ajuste orientações, detalhes de acesso ou qualquer observação útil para a rotina deste estoque.') }}
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-x-4 gap-y-8">
                            <x-src.form.textarea name="teacher-inventory-edit-notes" wire:model.live="notes"
                                label="Observações" rows="4" />
                        </div>
                    </section>
                </div>
            </div>

            <footer class="sticky bottom-0 z-20 border-t border-sky-800 bg-sky-950 px-6 py-4 text-sky-50">
                <div class="flex items-center justify-between gap-3">
                    <x-src.btn-silver type="button" wire:click="closeModal" wire:loading.attr="disabled" wire:target="save">
                        {{ __('Cancelar') }}
                    </x-src.btn-silver>
                    <x-src.btn-gold type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save">
                        <span wire:loading.remove wire:target="save">{{ __('Salvar alterações') }}</span>
                        <span wire:loading wire:target="save">{{ __('Salvando...') }}</span>
                    </x-src.btn-gold>
                </div>
            </footer>
        </div>
    </flux:modal>
</div>
