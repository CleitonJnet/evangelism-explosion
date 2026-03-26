<div>
    <flux:modal name="director-create-church-modal" wire:model="showModal" class="max-w-5xl w-[calc(100%-4px)] mx-auto bg-sky-950! p-0! max-h-[calc(100vh-4px)]! overflow-hidden"
        data-church-modal-root>
        <div class="flex max-h-[90vh] flex-col overflow-hidden rounded-2xl">
            <header class="sticky top-0 z-20 border-b border-sky-800 bg-sky-950 px-6 py-4 text-sky-50">
                <h3 class="text-lg font-semibold">{{ __('Cadastrar nova igreja') }}</h3>
                <p class="text-sm opacity-90">
                    {{ __('Preencha os dados abaixo para adicionar uma nova igreja oficial.') }}
                </p>
            </header>

            <div class="min-h-0 flex-1 overflow-y-auto bg-white px-6 py-6">
                <div class="space-y-8">
                    <section class="space-y-5">
                        <div>
                            <h4 class="text-base font-semibold text-sky-950">{{ __('Logo e dados da igreja') }}</h4>
                            <p class="text-sm text-slate-600">{{ __('Informações principais da igreja.') }}</p>
                        </div>

                        <div class="flex flex-wrap items-start gap-6">
                            <div class="grid gap-2">
                                <input id="director-church-logo-upload" type="file" accept="image/*"
                                    wire:model.live="logoUpload" class="sr-only">

                                <label for="director-church-logo-upload"
                                    class="cursor-pointer overflow-hidden rounded-xl border border-slate-300 bg-slate-100 p-1">
                                    <img src="{{ $logoPreviewUrl }}" alt="{{ __('Logo da igreja') }}"
                                        class="h-28 w-28 rounded-lg object-cover">
                                </label>

                                <p class="text-xs text-slate-600">{{ __('Clique na imagem para enviar a logo.') }}</p>

                                @error('logoUpload')
                                    <p class="text-xs font-semibold text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex flex-auto flex-wrap gap-x-4 gap-y-8">
                                <x-src.form.input name="church_name" wire:model.live="church_name"
                                    label="Nome completo da igreja" width_basic="320" required />
                                <x-src.form.input name="pastor_name" wire:model.live="pastor_name"
                                    label="Nome do pastor titular" width_basic="280" required />
                                <x-src.form.input type="tel" name="phone_church" wire:model.live="phone_church"
                                    label="Telefone da igreja &#10023; WhatsApp" width_basic="220" required />
                                <x-src.form.input type="email" name="church_email" wire:model.live="church_email"
                                    label="E-mail da igreja" width_basic="280" />
                            </div>
                        </div>
                    </section>

                    <section class="space-y-5">
                        <div>
                            <h4 class="text-base font-semibold text-sky-950">{{ __('Contato responsável') }}</h4>
                            <p class="text-sm text-slate-600">{{ __('Pessoa de referência para comunicação.') }}</p>
                        </div>

                        <div class="flex flex-wrap gap-y-8 gap-x-4">
                            <x-src.form.input name="church_contact" wire:model.live="church_contact"
                                label="Nome completo do contato" width_basic="320" required />
                            <x-src.form.input type="tel" name="church_contact_phone"
                                wire:model.live="church_contact_phone" label="Telefone do contato" width_basic="220"
                                required />
                            <x-src.form.input type="email" name="church_contact_email"
                                wire:model.live="church_contact_email" label="E-mail do contato" width_basic="300" />
                        </div>
                    </section>

                    <section class="space-y-5">
                        <div>
                            <h4 class="text-base font-semibold text-sky-950">{{ __('Endereço') }}</h4>
                            <p class="text-sm text-slate-600">{{ __('Informe o endereço completo da igreja.') }}</p>
                        </div>

                        <livewire:address-fields wire:model="churchAddress" title="Endereço da igreja"
                            wire:key="director-church-create-address" />
                    </section>

                    <section class="space-y-5">
                        <div>
                            <h4 class="text-base font-semibold text-sky-950">{{ __('Observações') }}</h4>
                            <p class="text-sm text-slate-600">{{ __('Informações adicionais sobre a igreja.') }}</p>
                        </div>

                        <div class="flex flex-wrap gap-y-8 gap-x-4">
                            <x-src.form.textarea name="church_notes" wire:model.live="church_notes"
                                label="Comentários sobre a igreja" rows="2" />
                        </div>
                    </section>
                </div>
            </div>

            <footer class="sticky bottom-0 z-20 border-t border-sky-800 bg-sky-950 px-6 py-4 text-sky-50">
                <div class="flex justify-between gap-3">
                    <x-src.btn-silver type="button" wire:click="closeModal" wire:loading.attr="disabled"
                        wire:target="save,logoUpload">
                        {{ __('Cancelar') }}
                    </x-src.btn-silver>
                    <x-src.btn-gold type="button" wire:click="save" wire:loading.attr="disabled"
                        wire:target="save,logoUpload">
                        {{ __('Salvar') }}
                    </x-src.btn-gold>
                </div>
            </footer>
        </div>
    </flux:modal>
</div>
