<div>
    <flux:modal name="director-church-create-participant-modal" wire:model="showModal" class="max-w-4xl w-[calc(100%-4px)] mx-auto p-0! max-h-[calc(100vh-4px)]! overflow-hidden">
        <div>
            <header class="border-b border-sky-950/20 bg-linear-to-br from-sky-950 via-sky-900 to-sky-950 px-6 py-4 text-sky-50">
                <h3 class="text-lg font-semibold">{{ __('Novo participante da igreja') }}</h3>
                <p class="text-sm opacity-80">
                    {{ __('Associe um participante existente ou crie um novo acesso já vinculado à igreja selecionada.') }}
                </p>
            </header>

            <div class="space-y-6 px-6 py-6">
                <section class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                    <div class="font-semibold">{{ __('Vínculo direto com a igreja') }}</div>
                    <div class="mt-1">
                        {{ __('Comece pelo e-mail do participante. Se já existir cadastro, basta revisar os dados e confirmar. Se não existir, o sistema criará o acesso com senha padrão.') }}
                    </div>
                </section>

                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <div class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-500">
                        {{ __('Igreja selecionada') }}: {{ $churchName }}
                    </div>

                    <div class="flex flex-wrap items-end gap-4">
                        <x-src.form.input type="email" name="director-church-participant-identify-email" wire:model.blur="email"
                            label="E-mail" width_basic="320" required />
                        <x-src.btn-gold label="Continuar com e-mail" type="button" wire:click="identifyByEmail"
                            class="h-10 text-nowrap px-4!" />
                    </div>

                    @if ($emailNotice)
                        <div class="mt-3 text-xs font-medium text-amber-800">{{ $emailNotice }}</div>
                    @endif
                </div>

                @if ($mode === 'register')
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <form wire:submit="registerParticipant" class="space-y-8">
                            <section class="space-y-5">
                                <div class="flex items-center justify-between gap-3">
                                    <div>
                                        <h4 class="text-base font-semibold text-sky-950">{{ __('Dados do participante') }}</h4>
                                        <p class="text-sm text-slate-600">
                                            @if ($existingUserId)
                                                {{ __('Cadastro localizado. Revise os dados antes de confirmar o vínculo com a igreja.') }}
                                            @else
                                                {{ __('Preencha o cadastro completo. O acesso será criado com senha padrão.') }}
                                            @endif
                                        </p>
                                    </div>
                                    @if (! $existingUserId)
                                        <div class="rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-900">
                                            {{ __('Senha inicial: :password', ['password' => $defaultPassword]) }}
                                        </div>
                                    @endif
                                </div>

                                <div class="flex flex-wrap gap-x-4 gap-y-8">
                                    <x-src.form.select name="director-church-participant-ispastor" wire:model="ispastor"
                                        label="É pastor?" width_basic="90" :select="false" value="0" :options="$yesNoOptions" />

                                    <x-src.form.input name="director-church-participant-name" wire:model="name"
                                        label="Nome completo" type="text" width_basic="400" :required="! $existingUserId" />

                                    <x-src.form.input type="tel" name="director-church-participant-mobile" wire:model="mobile"
                                        data-no-tel-mask label="Celular &#10023; WhatsApp" width_basic="200" :required="! $existingUserId" />

                                    <x-src.form.input type="email" name="director-church-participant-email" wire:model="email"
                                        label="E-mail" width_basic="350" required />

                                    <x-src.form.input type="date" name="director-church-participant-birth-date" wire:model="birth_date"
                                        label="Data de Nascimento" width_basic="200" />

                                    <x-src.form.select name="director-church-participant-gender" wire:model="gender"
                                        label="Gênero" width_basic="200" :options="$genderOptions" />
                                </div>
                            </section>

                            <div class="flex justify-end gap-3 pt-2">
                                <div class="w-full text-xs text-slate-600">
                                    @if ($existingUserId)
                                        {{ __('A confirmação atualizará o vínculo do participante para esta igreja.') }}
                                    @else
                                        {{ __('O participante será criado e vinculado imediatamente a esta igreja.') }}
                                    @endif
                                </div>
                                <x-src.btn-gold label="Confirmar participante" type="submit" class="text-nowrap" />
                            </div>
                        </form>
                    </div>
                @endif
            </div>

            <div class="flex justify-between gap-2 border-t border-sky-950/20 bg-linear-to-br from-sky-950 via-sky-900 to-sky-950 px-6 py-4 text-sky-50">
                <x-src.btn-silver type="button" wire:click="closeModal">
                    {{ __('Cancelar') }}
                </x-src.btn-silver>
            </div>
        </div>
    </flux:modal>
</div>
