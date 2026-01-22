<div class="px-3 py-4 md:p-6 2md:p-8 mx-auto max-w-2xl">
    <div class="flex items-center justify-center gap-4">
        <div class="flex items-center justify-center w-10 h-10 rounded-2xl ring-1 ring-black/10"
            style="background: linear-gradient(135deg, rgba(138,116,36,.18), rgba(199,168,64,.42), rgba(58, 56, 49, 0.2));">
            <img src="{{ asset('images/svg/user-work.svg') }}" alt="user" class="object-cover h-full">
        </div>

        <h4 class="text-lg font-extrabold text-slate-900">Formulário de Inscrição</h4>

    </div>

    <!-- Pagamento (placeholder) -->
    <div class="p-5 my-6 border rounded-2xl border-amber-200 bg-amber-50/60">
        @if ($isPaid)
            <div class="text-center">
                <div
                    class="rounded-md bg-amber-400/20 border border-amber-200 shadow text-amber-900 px-2 pt-1 pb-0.5 mb-2 text-sm w-fit mx-auto font-bold">
                    Investimento:
                    {{ $event->payment }}
                </div>

                <p class="mt-2 text-sm text-slate-700">
                    Após enviar o formulário, você verá as instruções de pagamento (PIX).
                </p>
                <p class="text-sm text-amber-900">Guarde o comprovante.</p>
            </div>
        @else
            <div class="">
                <div
                    class="rounded-md bg-amber-400/20 border border-amber-200 shadow text-amber-900 px-2 pt-1 pb-0.5 mb-2 text-sm w-fit mx-auto font-bold">
                    Evento gratuito
                </div>

                <div class="mt-1 text-sm text-slate-700">

                    <div class="text-sm font-extrabold text-slate-900 inline">Confirmação:</div>
                    Após enviar o formulário, sua inscrição será registrada e você receberá
                    as orientações do evento.
                </div>
            </div>
        @endif
    </div>

    <div class="flex gap-6">

        <form wire:submit="registerEvent" class="space-y-8">

            <div class="flex flex-wrap mt-6 gap-y-8 gap-x-4">

                <x-src.form.select name="ispastor" wire:model='ispastor' label="É pastor?" width_basic="50" autofocus
                    :select="false" value="N" :options="[['value' => 'Y', 'label' => 'Sim'], ['value' => 'N', 'label' => 'Não']]" />

                <x-src.form.input name="name" wire:model='name' label="Nome completo" type="text"
                    width_basic="350" required />

                <x-src.form.input type="tel" name="mobile" wire:model='mobile' data-no-tel-mask
                    label="Celular &#10023; WhatsApp" width_basic="200" required />

                <x-src.form.input type="email" name="email" wire:model.blur='email' label="E-mail"
                    width_basic="350" :note="$emailNotice" required />

                <x-src.form.input type="password" name="password" wire:model='password' label="Informe uma senha"
                    width_basic="300" required />

                <x-src.form.input type="password" name="password_confirmation" wire:model='password_confirmation'
                    label="Confirme a senha" width_basic="300" required />

                <x-src.form.input type="date" name="birth_date" wire:model='birth_date' label="Data de Nascimento"
                    width_basic="200" />

                <x-src.form.select name="gender" wire:model='gender' label="Gênero" width_basic="200"
                    :options="[['value' => 'M', 'label' => 'masculino'], ['value' => 'F', 'label' => 'Feminino']]" />
            </div>

            {{-- Navegação --}}
            <div class="pt-2 flex gap-3 justify-end">
                <div class="text-xs -mt-3 -m-2 text-red-600 w-full">Os campos com <sup>&#10033;</sup> são obrigatórios.
                </div>

                <div><x-src.btn-gold label="Confirmar inscrição" type="submit" class="text-nowrap" /></div>
            </div>

            <div class="flex justify-between text-sm">
                <a href="{{ route('web.event.details', $event->id) }}" class="text-sky-800 hover:underline">Retornar
                    para a página do evento</a>
                <a href="{{ route('web.event.login', $event->id) }}"
                    class='text-sky-800 hover:underline inline-flex items-center gap-0.5'>
                    <span class="hidden 2xs:inline">Já sou inscrito</span>
                    <div class='text-xs pt-1 opacity-50'>
                        &#10023;</div> Acessar evento
                </a>
            </div>
        </form>

    </div>
</div>
