<div class="flex justify-end pt-6" data-church-modal-root>
    <x-src.btn-silver type="button" wire:click="openModal">
        {{ __('Igreja não encontrada? Cadastrar nova') }}
    </x-src.btn-silver>

    <flux:modal name="create-training-church-modal" wire:model="showModal" class="max-w-5xl w-full p-0!">
        <div class="">
            <header
                class="border-b border-sky-950/20 bg-linear-to-br from-sky-950 via-sky-900 to-sky-950 px-6 py-4 text-sky-50">
                <h3 class="text-lg font-semibold">{{ __('Cadastrar nova igreja') }}</h3>
                <p class="text-sm opacity-80">
                    {{ __('Preencha os dados abaixo para adicionar a igreja base do evento.') }}
                </p>
            </header>

            <div class="max-h-[calc(100vh-150px)] overflow-y-auto">
                <div class="space-y-8 px-6 py-6">
                    <section class="space-y-5">
                        <div>
                            <h4 class="text-base font-semibold text-sky-950">{{ __('Dados da igreja') }}</h4>
                            <p class="text-sm text-slate-600">{{ __('Informações principais da igreja anfitriã.') }}</p>
                        </div>

                        <div class="flex flex-wrap gap-y-8 gap-x-4">
                            <x-src.form.input name="church_name" wire:model.live="church_name"
                                label="Nome completo da igreja" width_basic="320" required />
                            <x-src.form.input name="pastor_name" wire:model.live="pastor_name"
                                label="Nome do pastor titular" width_basic="280" required />
                            <x-src.form.input type="tel" name="phone_church" wire:model.live="phone_church"
                                label="Telefone da igreja &#10023; WhatsApp" width_basic="220" required />
                            <x-src.form.input type="email" name="church_email" wire:model.live="church_email"
                                label="E-mail da igreja" width_basic="280" />
                        </div>
                    </section>

                    <section class="space-y-5">
                        <div>
                            <h4 class="text-base font-semibold text-sky-950">{{ __('Contato responsável') }}</h4>
                            <p class="text-sm text-slate-600">
                                {{ __('Pessoa de referência para comunicação do treinamento.') }}</p>
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
                            wire:key="teacher-training-create-church-address" />
                    </section>

                    <section class="space-y-5">
                        <div>
                            <h4 class="text-base font-semibold text-sky-950">{{ __('Observações') }}</h4>
                            <p class="text-sm text-slate-600">{{ __('Informações adicionais úteis para o evento.') }}
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-y-8 gap-x-4">
                            <x-src.form.textarea name="church_notes" wire:model.live="church_notes"
                                label="Comentários sobre a igreja" rows="2" />
                        </div>
                    </section>
                </div>

                <div
                    class="sticky bottom-0 inset-x-0 mt-8 flex justify-between gap-2 border-t border-sky-950/20 bg-linear-to-br from-sky-950 via-sky-900 to-sky-950 px-6 py-4 text-sky-50">
                    <x-src.btn-silver type="button" wire:click="closeModal">
                        {{ __('Cancelar') }}
                    </x-src.btn-silver>
                    <div class="flex items-center gap-2">
                        <x-src.btn-silver type="button" wire:click="submit" wire:loading.attr="disabled"
                            wire:target="submit,approveAndUseNow">
                            {{ __('Salvar igreja') }}
                        </x-src.btn-silver>
                        <x-src.btn-gold type="button" wire:click="approveAndUseNow" wire:loading.attr="disabled"
                            wire:target="submit,approveAndUseNow">
                            {{ __('Approve and Use Now') }}
                        </x-src.btn-gold>
                    </div>
                </div>
            </div>
        </div>
    </flux:modal>
</div>
