<div>
    <flux:modal name="create-participant-registration-modal" wire:model="showModal" class="max-w-4xl w-full p-0!">
        <div>
            <header class="border-b border-sky-950/20 bg-linear-to-br from-sky-950 via-sky-900 to-sky-950 px-6 py-4 text-sky-50">
                <h3 class="text-lg font-semibold">{{ __('Novo inscrito no evento') }}</h3>
                <p class="text-sm opacity-80">
                    {{ __('Fluxo excepcional para o professor registrar um aluno usando o mesmo acesso da inscrição pública.') }}
                </p>
            </header>

            <div class="space-y-6 px-6 py-6">
                <section class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                    <div class="font-semibold">{{ __('Entrada excepcional no evento') }}</div>
                    <div class="mt-1">
                        {{ __('Comece pelo e-mail do aluno. Se já existir cadastro, o fluxo continua como no evento público; se não existir, o sistema abre o novo registro.') }}
                    </div>
                </section>

                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <div class="flex flex-wrap items-end gap-4">
                        <x-src.form.input type="email" name="teacher-training-registration-identify-email" wire:model.blur="email"
                            label="E-mail" width_basic="320" required />
                        <x-src.btn-gold label="Continuar com e-mail" type="button" wire:click="identifyByEmail"
                            class="h-10 text-nowrap px-4!" />
                    </div>
                    @if ($emailNotice)
                        <div class="mt-3 text-xs font-medium text-amber-800">{{ $emailNotice }}</div>
                    @endif
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" wire:click="switchToLogin"
                        class="{{ $mode === 'login' ? 'border-amber-300 bg-amber-50 text-amber-900' : 'border-slate-300 bg-white text-slate-700' }} rounded-xl border px-4 py-2 text-sm font-semibold transition hover:bg-slate-100">
                        Já tenho conta
                    </button>
                    <button type="button" wire:click="switchToRegister"
                        class="{{ $mode === 'register' ? 'border-amber-300 bg-amber-50 text-amber-900' : 'border-slate-300 bg-white text-slate-700' }} rounded-xl border px-4 py-2 text-sm font-semibold transition hover:bg-slate-100">
                        Criar inscrição
                    </button>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    @if ($mode === 'login')
                        <form wire:submit="loginEvent" class="space-y-8">
                            <div class="flex flex-wrap gap-x-4 gap-y-8">
                                <x-src.form.input type="email" name="teacher-training-registration-login-email"
                                    wire:model="email" label="E-mail" width_basic="320" required />
                                <x-src.form.input type="password" name="teacher-training-registration-login-password"
                                    wire:model="password" label="Senha" width_basic="320" required />
                            </div>

                            <div class="flex justify-end gap-3 pt-2">
                                <x-src.btn-gold label="Entrar e inscrever" type="submit" class="text-nowrap" />
                            </div>
                        </form>
                    @endif

                    @if ($mode === 'register')
                        <form wire:submit="registerEvent" class="space-y-8">
                            <div class="flex flex-wrap gap-x-4 gap-y-8">
                                <x-src.form.select name="teacher-training-registration-ispastor" wire:model="ispastor"
                                    label="É pastor?" width_basic="90" :select="false" value="0" :options="$yesNoOptions" />

                                <x-src.form.input name="teacher-training-registration-name" wire:model="name"
                                    label="Nome completo" type="text" width_basic="400" required />

                                <x-src.form.input type="tel" name="teacher-training-registration-mobile" wire:model="mobile"
                                    data-no-tel-mask label="Celular &#10023; WhatsApp" width_basic="200" required />

                                <x-src.form.input type="email" name="teacher-training-registration-email" wire:model="email"
                                    label="E-mail" width_basic="350" required />

                                <x-src.form.input type="password" name="teacher-training-registration-password" wire:model="password"
                                    label="Informe uma senha" width_basic="300" required />

                                <x-src.form.input type="password" name="teacher-training-registration-password-confirmation"
                                    wire:model="password_confirmation" label="Confirme a senha" width_basic="300" required />

                                <x-src.form.input type="date" name="teacher-training-registration-birth-date" wire:model="birth_date"
                                    label="Data de Nascimento" width_basic="200" />

                                <x-src.form.select name="teacher-training-registration-gender" wire:model="gender"
                                    label="Gênero" width_basic="200" :options="$genderOptions" />
                            </div>

                            <div class="flex justify-end gap-3 pt-2">
                                <div class="w-full text-xs text-red-600">
                                    {{ __('Os campos com obrigatórios seguem o mesmo fluxo da inscrição pública.') }}
                                </div>
                                <x-src.btn-gold label="Confirmar inscrição" type="submit" class="text-nowrap" />
                            </div>
                        </form>
                    @endif
                </div>
            </div>

            <div class="flex justify-between gap-2 border-t border-sky-950/20 bg-linear-to-br from-sky-950 via-sky-900 to-sky-950 px-6 py-4 text-sky-50">
                <x-src.btn-silver type="button" wire:click="closeModal">
                    {{ __('Cancelar') }}
                </x-src.btn-silver>
            </div>
        </div>
    </flux:modal>
</div>
