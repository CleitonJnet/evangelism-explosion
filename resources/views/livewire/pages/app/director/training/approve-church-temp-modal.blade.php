<div>
    <flux:modal name="approve-church-temp-modal" wire:model="showModal" class="max-w-5xl w-full p-0!">
        <div>
            <header
                class="border-b border-sky-950/20 bg-linear-to-br from-sky-950 via-sky-900 to-sky-950 px-6 py-4 text-sky-50">
                <h3 class="text-lg font-semibold">{{ __('Review church before approve') }}</h3>
                <p class="text-sm opacity-80">
                    {{ __('Revise e ajuste os dados da igreja oficial antes de confirmar a aprovação.') }}
                </p>
            </header>

            <div class="max-h-[calc(100vh-150px)] overflow-y-auto">
                <div class="space-y-8 px-6 py-6">
                    <section class="space-y-5">
                        <div>
                            <h4 class="text-base font-semibold text-sky-950">{{ __('Dados da igreja') }}</h4>
                            <p class="text-sm text-slate-600">{{ __('Campos obrigatórios e opcionais para cadastro oficial.') }}</p>
                        </div>

                        <div class="flex flex-wrap gap-y-8 gap-x-4">
                            <x-src.form.input name="church_name" wire:model.live="church_name"
                                label="Nome completo da igreja" width_basic="320" required />
                            <x-src.form.input name="pastor_name" wire:model.live="pastor_name"
                                label="Nome do pastor titular" width_basic="280" required />
                            <x-src.form.input type="tel" name="phone_church" wire:model.live="phone_church"
                                label="Telefone da igreja" width_basic="220" />
                            <x-src.form.input type="email" name="church_email" wire:model.live="church_email"
                                label="E-mail da igreja" width_basic="280" />
                        </div>
                    </section>

                    <section class="space-y-5">
                        <div>
                            <h4 class="text-base font-semibold text-sky-950">{{ __('Endereço') }}</h4>
                            <p class="text-sm text-slate-600">{{ __('Informe o endereço completo da igreja.') }}</p>
                        </div>

                        <livewire:address-fields wire:model="churchAddress" title="Endereço da igreja"
                            wire:key="teacher-training-approve-church-address" />
                    </section>
                </div>

                <div
                    class="sticky bottom-0 inset-x-0 mt-8 flex justify-between gap-2 border-t border-sky-950/20 bg-linear-to-br from-sky-950 via-sky-900 to-sky-950 px-6 py-4 text-sky-50">
                    <x-src.btn-silver type="button" wire:click="closeModal">
                        {{ __('Cancel') }}
                    </x-src.btn-silver>
                    <x-src.btn-gold type="button" wire:click="confirmApprove" wire:loading.attr="disabled"
                        wire:target="confirmApprove">
                        {{ __('Confirm Approve') }}
                    </x-src.btn-gold>
                </div>
            </div>
        </div>
    </flux:modal>
</div>
