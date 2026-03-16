<div>
    <flux:modal name="director-inventory-create-modal" wire:model="showModal" class="max-w-5xl w-full bg-sky-950! p-0!">
        <div class="flex h-[min(90vh,52rem)] flex-col overflow-hidden rounded-2xl">
            <header class="sticky top-0 z-20 border-b border-sky-800 bg-sky-950 px-6 py-4 text-sky-50">
                <div class="space-y-1">
                    <h3 class="text-lg font-semibold">{{ __('Cadastrar novo estoque') }}</h3>
                    <p class="text-sm opacity-90">
                        {{ __('Organize as informações principais do estoque antes de começar a movimentação de materiais.') }}
                    </p>
                </div>
            </header>

            <div class="min-h-0 flex-1 overflow-y-auto bg-slate-50 px-6 py-6">
                <div class="space-y-6 pb-2">
                    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="mb-5 flex flex-col gap-1">
                            <h4 class="text-base font-semibold text-slate-900">{{ __('1. Identificação do estoque') }}</h4>
                            <p class="text-sm text-slate-600">
                                {{ __('Defina como este estoque será identificado e qual será o seu tipo de operação dentro da área de materiais.') }}
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-x-4 gap-y-8">
                            <x-src.form.input name="director-inventory-create-name" wire:model.live="name" label="Nome"
                                type="text" width_basic="320" autofocus required />
                            <x-src.form.select name="director-inventory-create-kind" wire:model.live="kind" label="Tipo"
                                width_basic="220" :options="$kindOptions" required />
                        </div>
                    </section>

                    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="mb-5 flex flex-col gap-1">
                            <h4 class="text-base font-semibold text-slate-900">{{ __('2. Responsável e contato') }}</h4>
                            <p class="text-sm text-slate-600">
                                {{ __('Associe o estoque a um professor quando necessário e informe os canais de contato para facilitar a operação.') }}
                            </p>
                        </div>

                        @if ($kind === 'teacher')
                            <div class="mb-5 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                                {{ __('Como este estoque é do tipo professor, escolha abaixo o responsável por ele.') }}
                            </div>
                        @endif

                        @if ($kind === 'base')
                            <div class="mb-5 rounded-2xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-900">
                                {{ __('Como este estoque é do tipo base, vincule-o a uma igreja-base para liberar a visualização institucional no Portal Base.') }}
                            </div>
                        @endif

                        <div class="flex flex-wrap gap-x-4 gap-y-8">
                            @if ($kind === 'teacher')
                                <x-src.form.select name="director-inventory-create-user" wire:model.live="user_id"
                                    label="Professor responsável" width_basic="320" :options="$teacherOptions" required />
                            @endif
                            @if ($kind === 'base')
                                <x-src.form.select name="director-inventory-create-church" wire:model.live="church_id"
                                    label="Base vinculada" width_basic="420" :options="$churchOptions" required />
                            @endif
                            <x-src.form.input name="director-inventory-create-phone" wire:model.live="phone"
                                label="Telefone" type="text" width_basic="200" />
                            <x-src.form.input name="director-inventory-create-email" wire:model.live="email"
                                label="Email" type="email" width_basic="280" />
                        </div>
                    </section>

                    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="mb-5 flex flex-col gap-1">
                            <h4 class="text-base font-semibold text-slate-900">{{ __('3. Endereço do estoque') }}</h4>
                            <p class="text-sm text-slate-600">
                                {{ __('Preencha a localização física para facilitar entregas, retiradas e conferências presenciais.') }}
                            </p>
                        </div>

                        <livewire:address-fields wire:model="address" title="Endereço do estoque"
                            wire:key="director-inventory-create-address-fields" />
                    </section>

                    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="mb-5 flex flex-col gap-1">
                            <h4 class="text-base font-semibold text-slate-900">{{ __('4. Observações operacionais') }}</h4>
                            <p class="text-sm text-slate-600">
                                {{ __('Registre orientações, detalhes de acesso ou qualquer contexto útil para quem vai usar este estoque no dia a dia.') }}
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-x-4 gap-y-8">
                            <x-src.form.textarea name="director-inventory-create-notes" wire:model.live="notes"
                                label="Observações" rows="4" />
                        </div>
                    </section>
                </div>
            </div>

            <footer class="sticky bottom-0 z-20 border-t border-sky-800 bg-sky-950 px-6 py-4 text-sky-50">
                <div class="flex items-center justify-between gap-3">
                    <x-src.btn-silver type="button" wire:click="closeModal" wire:loading.attr="disabled"
                        wire:target="save">
                        {{ __('Cancelar') }}
                    </x-src.btn-silver>
                    <x-src.btn-gold type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save">
                        <span wire:loading.remove wire:target="save">{{ __('Salvar estoque') }}</span>
                        <span wire:loading wire:target="save">{{ __('Salvando...') }}</span>
                    </x-src.btn-gold>
                </div>
            </footer>
        </div>
    </flux:modal>
</div>
