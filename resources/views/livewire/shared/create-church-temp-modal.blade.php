<div>
    <flux:modal name="create-church-temp-modal" wire:model="showModal" class="max-w-5xl w-full p-0!">
        <div>
            <header
                class="border-b border-sky-950/20 bg-linear-to-br from-sky-950 via-sky-900 to-sky-950 px-6 py-4 text-sky-50">
                <h3 class="text-lg font-semibold">Cadastrar igreja para análise</h3>
                <p class="text-sm opacity-80">
                    Preencha os dados abaixo para enviar a igreja para a fila de triagem.
                </p>
            </header>

            <div class="max-h-[calc(100vh-150px)] overflow-y-auto">
                <div class="space-y-8 px-6 py-6">
                    <section class="space-y-5">
                        <div>
                            <h4 class="text-base font-semibold text-sky-950">Dados da igreja</h4>
                        </div>

                        <div class="flex flex-wrap gap-x-4 gap-y-8">
                            <x-src.form.input name="churchTempName" wire:model.live="churchTempName"
                                label="Nome completo da igreja" width_basic="360" required />
                            <x-src.form.input name="churchTempPastor" wire:model.live="churchTempPastor"
                                label="Nome do pastor titular" width_basic="320" required />
                            <x-src.form.input type="tel" name="churchTempPhone" wire:model.live="churchTempPhone"
                                label="Telefone (opcional)" width_basic="220" />
                            <x-src.form.input type="email" name="churchTempEmail" wire:model.live="churchTempEmail"
                                label="E-mail (opcional)" width_basic="300" />
                        </div>
                    </section>

                    <section class="space-y-5">
                        <div>
                            <h4 class="text-base font-semibold text-sky-950">Endereço completo</h4>
                        </div>

                        <div class="flex flex-wrap gap-x-4 gap-y-8">
                            <x-src.form.input name="churchTempPostalCode" wire:model.live="churchTempPostalCode"
                                label="CEP" width_basic="180" required />
                            <x-src.form.input name="churchTempStreet" wire:model.live="churchTempStreet"
                                label="Logradouro" width_basic="360" required />
                            <x-src.form.input name="churchTempNumber" wire:model.live="churchTempNumber" label="Número"
                                width_basic="120" required />
                            <x-src.form.input name="churchTempDistrict" wire:model.live="churchTempDistrict"
                                label="Bairro" width_basic="220" required />
                            <x-src.form.input name="churchTempCity" wire:model.live="churchTempCity" label="Cidade"
                                width_basic="220" required />
                            <x-src.form.input name="churchTempState" wire:model.live="churchTempState" label="UF"
                                width_basic="90" required />
                        </div>
                    </section>
                </div>

                <div
                    class="sticky bottom-0 inset-x-0 mt-8 flex justify-between gap-2 border-t border-sky-950/20 bg-linear-to-br from-sky-950 via-sky-900 to-sky-950 px-6 py-4 text-sky-50">
                    <x-src.btn-silver type="button" wire:click="closeModal">
                        Cancelar
                    </x-src.btn-silver>
                    <x-src.btn-gold type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save">
                        Enviar cadastro
                    </x-src.btn-gold>
                </div>
            </div>
        </div>
    </flux:modal>
</div>
