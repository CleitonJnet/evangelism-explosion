<div x-data="eeRegistrationWizard({ step: @entangle('step').live, totalSteps: {{ $isPaid ? 4 : 3 }} })" x-init="init()" class="px-3 py-4 md:p-6 md2:p-8">
    {{-- Cabeçalho + progresso --}}
    <div class="mb-6">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h3 class="text-lg font-extrabold text-slate-900">Formulário de Inscrição</h3>
                <p class="mt-1 text-sm text-red-600">
                    Os campos com <sup class="text-red-600">&#10033;</sup> são obrigatórios.
                </p>
            </div>

            <div class="text-sm font-semibold text-slate-600">
                <span x-text="`Fase ${step} de ${totalSteps}`"></span>
            </div>
        </div>

        <div class="mt-4">
            <div class="w-full h-2 overflow-hidden border rounded-full bg-slate-100 border-slate-200">
                <div class="h-full rounded-full bg-linear-to-r from-[#8a7424] via-[#c7a840] to-[#f1d57a]"
                    :style="`width: ${progress()}%`"></div>
            </div>

            <div class="grid {{ $isPaid > 0 ? 'grid-cols-4' : 'grid-cols-3' }} gap-2 mt-3">
                <template x-for="n in totalSteps" :key="n">
                    <button type="button" class="px-2 py-2 text-xs font-bold transition border rounded-xl"
                        :class="n === step ?
                            'border-amber-300 bg-amber-50 text-amber-900' :
                            (n < step ?
                                'border-slate-200 bg-slate-50 text-slate-700 hover:bg-white' :
                                'border-slate-200 bg-white text-slate-400 cursor-default')"
                        @click="goTo(n)" :title="stepTitle(n)">
                        <span x-text="stepShort(n)"></span>
                    </button>
                </template>
            </div>
        </div>
    </div>

    <form wire:submit.prevent="submit" class="space-y-8">

        {{-- =================== FASE 1: Participante =================== --}}
        <section class="space-y-8" x-show="step === 1" x-transition.opacity>
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-10 h-10 rounded-2xl ring-1 ring-black/10"
                    style="background: linear-gradient(135deg, rgba(138,116,36,.18), rgba(199,168,64,.42), rgba(241,213,122,.20));">
                    <img src="{{ asset('images/svg/user-work.svg') }}" alt="user" class="object-cover h-full">
                </div>
                <h4 class="text-lg font-extrabold text-slate-900">Fase 1: Participante
                </h4>
            </div>

            <div class="p-5 border rounded-2xl border-slate-200 bg-slate-50">
                <div class="text-sm font-extrabold text-slate-900">Dados para Login
                </div>
                <p class="mt-1 text-sm text-slate-600">
                    seu nome, e-mail e uma senha.
                </p>

                <div class="flex flex-wrap mt-6 gap-y-8 gap-x-4">

                    <x-src.form.input name="name" wire:model='name' label="Nome completo" type="text"
                        width_basic="300" required />

                    <x-src.form.input type="email" name="email" wire:model='email' label="E-mail" width_basic="300"
                        required />

                    <x-src.form.input type="password" name="password" wire:model='password' label="Informe uma senha"
                        width_basic="200" required />

                    <x-src.form.input type="password" name="password_confirmation" wire:model='password_confirmation'
                        label="Confirme a senha" width_basic="200" required />

                </div>
            </div>

            <div class="p-5 bg-white border rounded-2xl border-slate-200">
                <div class="text-sm font-extrabold text-slate-900">Dados Pessoais</div>

                <div class="flex flex-wrap mt-4 gap-y-8 gap-x-4">

                    <x-src.form.select name="ispastor" wire:model='ispastor' label="É pastor?" width_basic="200"
                        value="N" :options="[['value' => 'Y', 'label' => 'Sim'], ['value' => 'N', 'label' => 'Não']]" required />

                    <x-src.form.input type="tel" name="mobile" wire:model='mobile' data-no-tel-mask
                        label="Celular &#10023; WhatsApp" width_basic="300" required />

                    <x-src.form.input type="date" name="birth_date" wire:model='birth_date'
                        label="Data de Nascimento" width_basic="200" />

                    <x-src.form.select name="gender" wire:model='gender' label="Gênero" width_basic="200"
                        :options="[['value' => 'M', 'label' => 'masculino'], ['value' => 'F', 'label' => 'Feminino']]" required />

                </div>
            </div>
        </section>

        {{-- =================== FASE 2: Igreja =================== --}}
        <section class="space-y-8" x-show="step === 2" x-transition.opacity>
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-10 h-10 rounded-2xl ring-1 ring-black/10"
                    style="background: linear-gradient(135deg, rgba(138,116,36,.18), rgba(199,168,64,.42), rgba(241,213,122,.20));">
                    <img src="{{ asset('images/svg/church.svg') }}" alt="church" class="object-cover h-full">
                </div>
                <h4 class="text-lg font-extrabold text-slate-900">Fase 2: Igreja
                </h4>
            </div>

            <div class="p-5 bg-white border rounded-2xl border-slate-200">
                <div class="text-sm font-extrabold text-slate-900">Dados da Igreja
                </div>

                <div class="flex flex-wrap mt-6 gap-y-8 gap-x-4">

                    <x-src.form.input name="church_name" wire:model='church_name' label="Nome completo da Igreja"
                        type="text" width_basic="300" required />

                    <x-src.form.input name="pastor_name" wire:model='pastor_name' label="Nome do pastor titular"
                        type="text" width_basic="300" required />

                    <x-src.form.input type="tel" name="phone_church" wire:model='phone_church'
                        label="Telefone &#10023; WhatsApp" width_basic="300" required />

                    <x-src.form.input type="email" name="church_email" wire:model='church_email'
                        label="E-mail da Igreja" width_basic="300" />

                </div>
            </div>

            {{-- Bloco de endereço da igreja no componente livewire --}}
            <div class="p-5 border rounded-2xl border-slate-200 bg-slate-50">
                <div class="text-sm font-extrabold text-slate-900">Endereço da Igreja</div>
                <livewire:address-fields wire:model="churchAddress" title="Endereço da Igreja" :require-district-city-state="true"
                    wire:key="address-church" />
            </div>

        </section>

        {{-- =================== FASE 3: Confirmação =================== --}}
        <section x-show="step === 3" x-transition.opacity>
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-10 h-10 rounded-2xl ring-1 ring-black/10"
                    style="background: linear-gradient(135deg, rgba(138,116,36,.18), rgba(199,168,64,.42), rgba(241,213,122,.20));">
                    <img src="{{ asset('images/svg/confirm.svg') }}" alt="confirm" class="object-cover h-full">
                </div>
                <h4 class="text-lg font-extrabold text-slate-900">Fase 3: Confirmação</h4>
            </div>

            <div class="p-5 mt-5 border rounded-2xl border-amber-200 bg-amber-50/70">
                <h5 class="text-sm font-extrabold text-slate-900">Condições, restrições e
                    informações necessárias</h5>

                <ul class="pl-5 mt-3 space-y-2 text-sm list-disc text-slate-700">
                    <li>Não é permitido fotografar/filmar/gravar áudio ou vídeo do treinamento.
                    </li>
                    <li>É vedado reproduzir/distribuir o material e as aulas (direitos
                        autorais).</li>
                    <li>Cada aluno deve ter seu kit de material próprio.</li>
                    @if ($isPaid)
                        <li>Ouvintes/não pagantes não serão permitidos.</li>
                        <li>A mera inscrição sem pagamento não garante vaga.</li>
                    @else
                        <li>Ouvintes (sem inscrição) não serão permitidos.</li>
                        <li>A mera inscrição não garante vaga até a confirmação da equipe
                            responsável.</li>
                    @endif
                </ul>

                <div class="mt-4 space-y-3">
                    <label class="flex items-start gap-3">
                        <input type="checkbox" name="agree_terms" wire:model='agree_terms' required
                            class="mt-1 rounded border-slate-300 text-amber-600 focus:ring-amber-500">
                        <span class="text-sm text-slate-800">
                            Li e concordo com os termos e condições. <sup class="text-red-600 opacity-70">
                                &#10033;</sup>
                        </span>
                    </label>
                    @error($agree_terms)
                        <p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>
                    @enderror

                </div>
                <div class="mt-4 space-y-3">
                    <label class="flex items-start gap-3">
                        <input type="checkbox" name="agree_faith" wire:model='agree_faith' required
                            class="mt-1 rounded border-slate-300 text-amber-600 focus:ring-amber-500">
                        <span class="text-sm text-slate-800">
                            Li e concordo com a declaração de fé. <sup class="text-red-600 opacity-70">
                                &#10033;</sup>
                        </span>
                    </label>
                    @error($agree_faith)
                        <p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>
                    @enderror

                </div>
            </div>

            @if (!empty($whatsappGroupUrl))
                <div class="p-4 mt-5 border rounded-2xl border-emerald-200 bg-emerald-50">
                    <div class="flex items-start gap-3">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg"
                            alt="Logo WhatsApp" class="object-contain h-16">
                        <div class="flex-1">
                            <div class="text-sm font-extrabold text-emerald-900">Grupo do
                                WhatsApp do evento</div>
                            <p class="mt-1 text-sm text-emerald-800">
                                Após concluir sua inscrição, entre no grupo para receber avisos,
                                materiais e comunicados.
                            </p>

                            <a href="{{ $whatsappGroupUrl }}" target="_blank" rel="noopener"
                                class="inline-flex items-center justify-center gap-2 px-4 py-2 mt-3 text-sm font-extrabold text-white rounded-xl bg-emerald-700 hover:bg-emerald-800">
                                Entrar no grupo do WhatsApp
                                <span class="text-base leading-none">&#10148;</span>
                            </a>
                        </div>
                    </div>
                </div>
            @endif

        </section>

        @if ($isPaid)
            {{-- =================== FASE 4: Pagamento =================== --}}
            <section x-show="step === 4" x-transition.opacity>
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-10 h-10 rounded-2xl ring-1 ring-black/10"
                        style="background: linear-gradient(135deg, rgba(138,116,36,.18), rgba(199,168,64,.42), rgba(241,213,122,.20));">
                        <img src="{{ asset('images/svg/qr-code-icon.svg') }}" alt="payment"
                            class="object-cover h-full">
                    </div>
                    <h4 class="text-lg font-extrabold text-slate-900">Fase 4: Pagamento (PIX)
                    </h4>
                </div>

                <p class="mt-2 text-sm text-slate-600">
                    Efetue o pagamento via PIX. Guarde o comprovante.
                </p>

                <div class="grid gap-6 mt-5 lg:grid-cols-2">
                    <div class="p-5 border rounded-2xl border-slate-200 bg-slate-50">
                        <div class="text-sm font-extrabold text-slate-900">QR Code</div>

                        <div
                            class="flex items-center justify-center p-4 mt-4 bg-white border rounded-2xl border-slate-200">
                            @if (!empty($pix['qr_svg']))
                                <div class="w-56 h-56">{!! $pix['qr_svg'] !!}</div>
                            @elseif(!empty($pix['qr_base64']))
                                <img class="w-56 h-56" alt="QR Code PIX"
                                    src="data:image/png;base64,{{ $pix['qr_base64'] }}">
                            @elseif(!empty($pix['qr_image_url']))
                                <img class="w-56 h-56 object-contain" alt="QR Code PIX" src="{{ $pix['qr_image_url'] }}">
                            @else
                                <div class="flex items-center justify-center w-56 h-56 text-sm text-slate-400">
                                    O QR Code será exibido após gerar o pagamento.
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="p-5 border rounded-2xl border-slate-200 bg-slate-50">
                        <div class="text-sm font-extrabold text-slate-900">Chave PIX</div>

                        <div class="mt-4">
                            <label class="block text-xs font-semibold text-slate-600">Chave</label>
                            <div class="flex gap-2 mt-1">
                                <input id="pixKey" type="text" readonly value="{{ $pix['key'] ?? '' }}"
                                    class="w-full bg-white rounded-xl border-slate-300 focus:border-amber-500 focus:ring-amber-500"
                                    placeholder="Chave será exibida aqui">
                                <button type="button"
                                    class="px-4 py-2 rounded-xl font-extrabold text-[#1b1709]
                                                    bg-linear-to-br from-[#f1d57a] via-[#c7a840] to-[#8a7424]
                                                    border border-white/30 shadow-sm hover:brightness-110"
                                    @click="copy('pixKey')">
                                    Copiar
                                </button>
                            </div>

                            @if (!empty($pix['emv']))
                                <div class="mt-4">
                                    <label class="block text-xs font-semibold text-slate-600">PIX
                                        Copia e Cola</label>
                                    <textarea id="pixEmv" readonly rows="4"
                                        class="w-full mt-1 bg-white rounded-xl border-slate-300 focus:border-amber-500 focus:ring-amber-500">{{ $pix['emv'] }}</textarea>
                                    <button type="button"
                                        class="px-4 py-2 mt-2 font-extrabold bg-white border rounded-xl text-slate-800 border-slate-200 hover:bg-slate-50"
                                        @click="copy('pixEmv')">
                                        Copiar código PIX
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>

                    <label class="flex items-start gap-3 mt-6">
                        <input type="checkbox" wire:model.live="payment_confirmed"
                            class="mt-1 rounded border-slate-300 text-amber-600 focus:ring-amber-500">
                        <span class="text-sm text-slate-800">
                            Confirmo que já efetuei o pagamento via PIX e tenho o comprovante.
                            <sup class="text-red-600 opacity-70">&#10033;</sup>
                        </span>
                    </label>

                </div>
            </section>
        @endif

        {{-- Navegação --}}
        <div class="pt-2 border-t border-slate-200">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <button type="button"
                    class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl font-extrabold border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 disabled:opacity-40"
                    @click="prev()" :disabled="step === 1">
                    Voltar
                </button>

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <button type="button"
                        class="inline-flex items-center justify-center px-6 py-2.5 rounded-xl font-extrabold border border-slate-200 bg-slate-50 text-slate-800 hover:bg-white"
                        x-show="step < totalSteps" @click="next()">
                        Próximo
                    </button>

                    {{-- Envia no final da fase 3: gera PIX (se pago) ou finaliza (se gratuito) --}}
                    <button type="submit"
                        class="inline-flex items-center justify-center gap-2 px-8 py-3 font-extrabold rounded-xl text-[#1b1709] bg-linear-to-br from-[#f1d57a] via-[#c7a840] to-[#8a7424] border border-white/30 shadow-sm hover:brightness-110"
                        x-show="step === totalSteps">
                        Finalizar inscrição
                        <span class="text-xl leading-none">&#10148;</span>
                    </button>
                </div>
            </div>
        </div>

    </form>

    <script>
        // Máscara global para input[type="tel"], exceto o widget (.wa-phone-visible)
        (function attachGlobalTelMask() {
            function formatPhone(v) {
                v = v.replace(/\D/g, "").slice(0, 11);
                if (v.length <= 2) return v;
                const ddd = v.slice(0, 2),
                    num = v.slice(2);
                if (num.length <= 4) return `(${ddd}) ${num}`;
                if (num.length <= 8)
                    return `(${ddd}) ${num.slice(0, 4)}-${num.slice(4)}`;
                return `(${ddd}) ${num.slice(0, 5)}-${num.slice(5)}`;
            }
            document.addEventListener("input", (e) => {
                if (e.target.matches('input[type="tel"]:not(.wa-phone-visible)')) {
                    e.target.value = formatPhone(e.target.value);
                }
            });
        })();

        function eeRegistrationWizard({
            step,
            totalSteps = 4
        }) {
            return {
                step, // agora é entangled
                totalSteps,

                init() {
                    this.syncLeft();
                    this.$watch('step', () => this.syncLeft());
                },

                syncLeft() {
                    window.__eeRegStep = this.step; // alimenta a coluna esquerda
                },

                stepTitle(n) {
                    return ({
                        1: 'Acesso + Participante',
                        2: 'Igreja',
                        3: 'Confirmação',
                        4: 'Pagamento',
                    })[n] ?? `Fase ${n}`;
                },

                stepShort(n) {
                    return ({
                        1: 'Acesso',
                        2: 'Igreja',
                        3: 'Confirma',
                        4: 'PIX',
                    })[n] ?? `F${n}`;
                },

                progress() {
                    return Math.round(((this.step - 1) / (this.totalSteps - 1)) * 100);
                },

                goTo(n) {
                    if (n <= this.step) this.step = n; // só voltar (mais seguro)
                },

                next() {
                    if (this.step >= this.totalSteps) return;

                    this.$wire.call('validateStep', Number(this.step))
                        .then((ok) => {
                            if (ok) this.step++;
                        });
                },

                prev() {
                    if (this.step > 1) this.step--;
                },

                copy(id) {
                    const el = document.getElementById(id);
                    if (!el || !el.value) return;
                    navigator.clipboard.writeText(el.value);
                },
            };
        }
    </script>
</div>
