<div class="mx-auto max-w-3xl px-3 py-4 md:p-6 2md:p-8 min-h-[calc(100vh-494px)]">
    <div class="flex items-center justify-center gap-4">
        <div class="flex h-10 w-10 items-center justify-center rounded-2xl ring-1 ring-black/10"
            style="background: linear-gradient(135deg, rgba(138,116,36,.18), rgba(199,168,64,.42), rgba(58, 56, 49, 0.2));">
            <img src="{{ asset('images/svg/user-work.svg') }}" alt="user" class="h-full object-cover">
        </div>

        <h4 class="text-center text-lg font-extrabold text-slate-900">Acessar ou fazer inscricao no evento</h4>
    </div>

    <p class="mt-2 text-center text-sm text-slate-600">
        Informe seu e-mail e o sistema direciona voce para entrar ou criar a inscricao em poucos passos.
    </p>

    <div class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 p-4">
        <div class="flex flex-wrap items-end gap-4">
            <x-src.form.input type="email" name="email" wire:model.blur="email" label="E-mail" width_basic="320"
                required />
            <x-src.btn-gold label="Continuar com e-mail" type="button" wire:click="identifyByEmail"
                class="h-10 text-nowrap px-4!" />
        </div>
        @if ($emailNotice)
            <div class="mt-3 text-xs font-medium text-amber-800">{{ $emailNotice }}</div>
        @endif
    </div>

    <div class="mt-6 flex flex-wrap items-center gap-2">
        <button type="button" wire:click="switchToLogin"
            class="{{ $mode === 'login' ? 'border-amber-300 bg-amber-50 text-amber-900' : 'border-slate-300 bg-white text-slate-700' }} rounded-xl border px-4 py-2 text-sm font-semibold transition hover:bg-slate-100">
            Ja tenho conta
        </button>
        <button type="button" wire:click="switchToRegister"
            class="{{ $mode === 'register' ? 'border-amber-300 bg-amber-50 text-amber-900' : 'border-slate-300 bg-white text-slate-700' }} rounded-xl border px-4 py-2 text-sm font-semibold transition hover:bg-slate-100">
            Criar inscricao
        </button>
    </div>

    <div class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 p-4">
        @if ($mode === 'login')
            <form wire:submit="loginEvent" class="mt-6 space-y-8">
                <div class="flex flex-wrap gap-x-4 gap-y-8">
                    <x-src.form.input type="email" name="email" wire:model="email" label="E-mail" width_basic="320"
                        required />
                    <x-src.form.input type="password" name="password" wire:model="password" label="Senha"
                        width_basic="320" required />
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <x-src.btn-gold label="Entrar e acessar evento" type="submit" class="text-nowrap" />
                </div>
            </form>
        @endif

        @if ($mode === 'register')
            <form wire:submit="registerEvent" class="mt-6 space-y-8">
                <div class="flex flex-wrap gap-x-4 gap-y-8">
                    <x-src.form.select name="ispastor" wire:model="ispastor" label="E pastor?" width_basic="90"
                        :select="false" value="0" :options="[['value' => '1', 'label' => 'Sim'], ['value' => '0', 'label' => 'Nao']]" />

                    <x-src.form.input name="name" wire:model="name" label="Nome completo" type="text"
                        width_basic="400" required />

                    <x-src.form.input type="tel" name="mobile" wire:model="mobile" data-no-tel-mask
                        label="Celular &#10023; WhatsApp" width_basic="200" required />

                    <x-src.form.input type="email" name="email" wire:model="email" label="E-mail" width_basic="350"
                        required />

                    <x-src.form.input type="password" name="password" wire:model="password" label="Informe uma senha"
                        width_basic="300" required />

                    <x-src.form.input type="password" name="password_confirmation" wire:model="password_confirmation"
                        label="Confirme a senha" width_basic="300" required />

                    <x-src.form.input type="date" name="birth_date" wire:model="birth_date"
                        label="Data de Nascimento" width_basic="200" />

                    <x-src.form.select name="gender" wire:model="gender" label="Genero" width_basic="200"
                        :options="[['value' => '1', 'label' => 'Masculino'], ['value' => '2', 'label' => 'Feminino']]" />
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <div class="w-full text-xs text-red-600">Os campos com <sup>&#10033;</sup> sao obrigatorios.</div>
                    <x-src.btn-gold label="Confirmar inscricao" type="submit" class="text-nowrap" />
                </div>
            </form>
        @endif
    </div>

    <div class="mt-6 flex flex-wrap justify-between gap-3 text-sm">
        <x-src.btn-silver label="Voltar para o evento" :route="route('web.event.details', $event->id)" class="py-1.5! text-xs" />
    </div>
</div>
