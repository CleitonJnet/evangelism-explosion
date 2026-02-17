<div>
    <flux:modal name="create-mentor-church-modal" wire:model="showModal" class="max-w-5xl w-full p-0!">
        <div>
            <header
                class="border-b border-sky-950/20 bg-linear-to-br from-sky-950 via-sky-900 to-sky-950 px-6 py-4 text-sky-50">
                <h3 class="text-lg font-semibold">{{ __('Cadastrar nova igreja oficial') }}</h3>
                <p class="text-sm opacity-80">
                    {{ __('Preencha os dados para criar uma igreja oficial no sistema.') }}
                </p>
            </header>

            <div class="max-h-[calc(100vh-160px)] overflow-y-auto">
                <div class="space-y-8 px-6 py-6">
                    <section class="space-y-5">
                        <div class="flex flex-wrap gap-x-4 gap-y-8">
                            <x-src.form.input name="churchName" wire:model.live="churchName"
                                label="Nome completo da igreja" width_basic="360" required />
                            <x-src.form.input name="pastorName" wire:model.live="pastorName"
                                label="Nome do pastor titular" width_basic="320" required />
                            <x-src.form.input type="tel" name="phone" wire:model.live="phone"
                                label="Telefone (opcional)" width_basic="220" />
                            <x-src.form.input type="email" name="email" wire:model.live="email"
                                label="E-mail (opcional)" width_basic="300" />
                        </div>
                    </section>

                    <section class="space-y-5">
                        <div class="flex flex-wrap gap-x-4 gap-y-8">
                            <x-src.form.input name="postalCode" wire:model.live="postalCode" label="CEP"
                                width_basic="180" required />
                            <x-src.form.input name="street" wire:model.live="street" label="Logradouro"
                                width_basic="360" required />
                            <x-src.form.input name="number" wire:model.live="number" label="Número"
                                width_basic="120" required />
                            <x-src.form.input name="district" wire:model.live="district" label="Bairro"
                                width_basic="220" required />
                            <x-src.form.input name="city" wire:model.live="city" label="Cidade"
                                width_basic="220" required />
                            <x-src.form.input name="state" wire:model.live="state" label="UF" width_basic="90"
                                required />
                        </div>
                    </section>
                </div>

                <div
                    class="sticky bottom-0 inset-x-0 mt-8 flex justify-between gap-2 border-t border-sky-950/20 bg-linear-to-br from-sky-950 via-sky-900 to-sky-950 px-6 py-4 text-sky-50">
                    <x-src.btn-silver type="button" wire:click="closeModal">
                        {{ __('Cancelar') }}
                    </x-src.btn-silver>
                    <x-src.btn-gold type="button" wire:click="save" wire:loading.attr="disabled"
                        wire:target="save">
                        {{ __('Salvar igreja') }}
                    </x-src.btn-gold>
                </div>
            </div>
        </div>
    </flux:modal>
</div>
