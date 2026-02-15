<section x-data="{
    step: @entangle('step').live,
    {{-- step: 5, --}}
    totalSteps: 5,
    canProceed: false,
    async refreshCanProceed() {
        this.canProceed = await this.$wire.canProceedStep(Number(this.step));
    },
    async init() {
        await this.refreshCanProceed();

        this.$watch('step', async () => {
            await this.refreshCanProceed();
        });
    },
    async handleEnter(event) {
        if (event.target?.closest?.('[data-church-modal-root]')) {
            return;
        }

        const tagName = (event.target?.tagName || '').toLowerCase();
        const inputType = (event.target?.getAttribute?.('type') || '').toLowerCase();

        if (tagName === 'textarea' || event.target?.isContentEditable) {
            return;
        }

        if (inputType === 'button' || inputType === 'submit') {
            return;
        }

        if (event.shiftKey || event.ctrlKey || event.altKey || event.metaKey) {
            return;
        }

        if (this.step >= this.totalSteps) {
            return;
        }

        event.preventDefault();
        await this.nextStep();
    },
    async nextStep() {
        if (this.step >= this.totalSteps) {
            return;
        }

        await this.refreshCanProceed();

        if (this.canProceed) {
            this.step++;
            await this.refreshCanProceed();
        }
    },
    previousStep() {
        if (this.step > 1) {
            this.step--;
        }
    },
}"
    x-on:step-validity-updated.window="refreshCanProceed()"
    class="rounded-2xl border border-amber-300/20 bg-linear-to-br from-slate-100 via-white to-slate-200 p-6 shadow-lg relative h-[calc(100vh-252px)]">

    <form x-on:submit.prevent x-on:keydown.enter="handleEnter($event)"
        x-on:input.debounce.150ms="if ($event.target.closest('[data-church-modal-root]')) return; refreshCanProceed()"
        x-on:change="if ($event.target.closest('[data-church-modal-root]')) return; refreshCanProceed()"
        class="h-full pb-20">
        {{-- SELEÇÃO DO CURSO --}}
        <div x-cloak x-show="step === 1" id="step_1" class="flex flex-wrap gap-4">
            <div class="flex-1">
                <img src="https://placehold.co/600x120?text=Passo+1+-+Curso"
                    alt="Ilustração do passo de seleção de curso"
                    class="mb-4 h-auto w-full rounded-lg border border-sky-950/10 object-cover" />
                <div class="text-base font-semibold text-sky-950">{{ __('Escolha o curso do treinamento') }}</div>
                <div class="text-sm text-slate-700">
                    {{ __('Selecione o curso que será realizado neste evento. Essa escolha define a base do conteúdo e o valor inicial da inscrição.') }}
                </div>
            </div>
            <div class="flex-1 grid gap-4">
                @foreach ($courses as $course)
                    <div wire:key="course-option-{{ $course->id }}">
                        <input type="radio" wire:model.change="course_id" name="course" class="peer sr-only"
                            id="course-{{ $course->id }}" value="{{ $course->id }}">
                        <label for="course-{{ $course->id }}"
                            class="block cursor-pointer select-none rounded-lg border-2 border-slate-300 p-4 peer-checked:border-sky-900 peer-checked:[&_.course-check]:inline-flex transition-all hover:bg-white hover:shadow-[0_0_0_2px_#cad5e2]">
                            <div class="flex gap-2 justify-between">
                                <div class="font-bold">{{ $course->type }} {{ $course->name }}</div>
                                <div
                                    class="course-check hidden size-6 items-center justify-center rounded-full bg-sky-900 text-white">
                                    <div class="">&#x2713;</div>
                                </div>
                            </div>
                            <div class="text-xs opacity-80">{{ $course->ministry?->name }}</div>
                        </label>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- DATA DO EVENTO --}}
        <div x-cloak x-show="step === 2" id="step_2" class="flex flex-wrap gap-4">
            <div class="flex-1">
                <img src="https://placehold.co/600x120?text=Passo+2+-+Datas"
                    alt="Ilustração do passo de datas do evento"
                    class="mb-4 h-auto w-full rounded-lg border border-sky-950/10 object-cover" />
                <div class="text-base font-semibold text-sky-950">{{ __('Defina os dias e horários') }}</div>
                <div class="text-sm text-slate-700">
                    {{ __('Adicione todos os dias do treinamento com horário de início e fim. Revise os horários antes de avançar para evitar conflitos na programação.') }}
                </div>
            </div>
            <div class="max-h-80 space-y-10 overflow-y-auto">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="text-sm font-semibold text-heading">{{ __('Datas do treinamento') }}</div>
                    <flux:button type="button" variant="outline" wire:click="addEventDate">
                        {{ __('Adicionar dia') }}
                    </flux:button>
                </div>

                @foreach ($eventDates as $index => $eventDate)
                    <div wire:key="event-date-{{ $index }}" class="flex flex-wrap items-end gap-4">
                        <x-src.form.input name="eventDates.{{ $index }}.date"
                            wire:model.live="eventDates.{{ $index }}.date" label="Data" type="date"
                            width_basic="220" required />
                        <x-src.form.input name="eventDates.{{ $index }}.start_time"
                            wire:model.live="eventDates.{{ $index }}.start_time" label="Início" type="time"
                            width_basic="160" required />
                        <x-src.form.input name="eventDates.{{ $index }}.end_time"
                            wire:model.live="eventDates.{{ $index }}.end_time" label="Fim" type="time"
                            width_basic="160" required />
                        <flux:button type="button" variant="danger" class="shrink-0"
                            wire:click="removeEventDate({{ $index }})">
                            {{ __('Remover') }}
                        </flux:button>
                    </div>
                @endforeach

            </div>
        </div>

        {{-- SELEÇÃO A IGREJA BASE DO EVENTO --}}
        <div x-cloak x-show="step === 3" id="step_3" class="flex flex-wrap gap-4">
            <div class="flex-1">
                <img src="https://placehold.co/600x120?text=Passo+3+-+Igreja+Base"
                    alt="Ilustração do passo de seleção da igreja base"
                    class="mb-4 h-auto w-full rounded-lg border border-sky-950/10 object-cover" />
                <div class="text-base font-semibold text-sky-950">{{ __('Escolha a igreja base do evento') }}</div>
                <div class="text-slate-700 text-justify ">
                    {{ __('Use a busca para localizar a igreja anfitriã e selecioná-la na lista. Se a igreja ainda não existir no sistema, use o botão abaixo para cadastrar e continuar sem sair deste registro.') }}

                    <livewire:pages.app.teacher.training.create-church-modal wire:model="newChurchSelection"
                        wire:key="teacher-training-create-church-modal" />
                </div>
            </div>
            <div class="flex-1 grid gap-4">
                <x-src.form.input name="churchSearch" wire:model.live="churchSearch" label="Buscar igreja"
                    width_basic="900" autofocus="" />

                <div class="max-h-80 space-y-2 overflow-y-auto">
                    @foreach ($churches as $church)
                        <div wire:key="church-option-{{ $church->id }}">
                            <input type="radio" name="church" class="peer sr-only" id="church-{{ $church->id }}"
                                value="{{ $church->id }}" wire:click="selectChurch({{ $church->id }})"
                                @checked((int) $church_id === (int) $church->id)>
                            <label for="church-{{ $church->id }}"
                                class="block cursor-pointer select-none rounded-lg border-2 border-slate-300 py-2 px-4 peer-checked:border-sky-900 peer-checked:[&_.church-check]:inline-flex">
                                <div class="flex gap-2 justify-between">
                                    <div class="font-bold">{{ $church->name }}</div>
                                    <div
                                        class="church-check hidden size-6 items-center justify-center rounded-full bg-sky-900 text-white">
                                        <div>&#x2713;</div>
                                    </div>
                                </div>
                                <div class="text-xs uppercase border-b border-sky-950/20 pb-1 mb-1">
                                    {{ $church->pastor }}
                                </div>
                                <div class="text-xs opacity-80">{{ $church->district }}, {{ $church->city }},
                                    {{ $church->state }}</div>
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- FINANCEIRO --}}
        <div x-cloak x-show="step === 4" id="step_4" class="flex flex-wrap gap-4">
            <div class="flex-1">
                <img src="https://placehold.co/600x120?text=Passo+4+-+Valores"
                    alt="Ilustração do passo de valores do evento"
                    class="mb-4 h-auto w-full rounded-lg border border-sky-950/10 object-cover" />
                <div class="text-base font-semibold text-sky-950">{{ __('Revise os valores da inscrição') }}</div>
                <div class="text-sm text-slate-700">
                    {{ __('Confira o preço base do curso, informe despesas extras e desconto por inscrição. O valor final é calculado automaticamente para conferência antes de salvar o evento.') }}
                </div>
            </div>
            <div class="flex-1 grid gap-10">
                <div class="flex justify-between gap-0.5s">
                    <div class="">{{ __('O custo do treinamento selecionado é:') }}</div>
                    <div class="border-b border-dashed border-sky-950 flex-auto"></div>
                    <div>{{ __('R$') }} {{ $price }}</div>
                </div>

                <div class="flex flex-wrap gap-2">
                    <div class="">{{ __('Digite uma taxa para despesas extras') }}</div>
                    <div class="border-b border-dashed border-sky-950/20 flex-auto"></div>
                    <x-src.form.input name="price_church" wire:model.live="price_church" label="Despesas extras"
                        class="text-right" width_basic="10" />
                </div>

                <div class="flex flex-wrap gap-2">
                    <div class="">{{ __('Digite o valor do desconto em cada inscrição') }}</div>
                    <div class="border-b border-dashed border-sky-950/20 flex-auto"></div>
                    <x-src.form.input name="discount" wire:model.live="discount" label="Desconto" class="text-right"
                        width_basic="10" />
                </div>

                <div class="flex justify-between gap-0.5 font-bold">
                    <div class="">{{ __('Valor final para cada inscrição:') }}</div>
                    <div class="border-b border-dashed border-sky-950 flex-auto"></div>
                    <div>{{ __('R$') }} {{ $this->finalPricePerRegistration }}</div>
                </div>
            </div>
        </div>

        {{-- DIVULGAÇÃO --}}
        <div x-cloak x-show="step === 5" id="step_5" class="flex flex-wrap gap-4">
            <div class="flex-1">
                <img src="https://placehold.co/600x120?text=Passo+5+-+Divulgacao"
                    alt="Ilustração do passo de divulgação do evento"
                    class="mb-4 h-auto w-full rounded-lg border border-sky-950/10 object-cover" />
                <div class="text-base font-semibold text-sky-950">{{ __('Arquivo de divulgação') }}</div>
                <div class="text-sm text-slate-700">
                    {{ __('Você já possui uma arte para divulgar este evento nas redes sociais? Se sim, envie a imagem agora para anexá-la ao cadastro.') }}
                </div>
            </div>
            <div class="flex-1 grid gap-6">
                <div class="grid gap-4 rounded-xl border border-slate-300 bg-white/70 p-4">
                    <div class="text-sm font-semibold text-sky-950">
                        {{ __('Upload da arte de divulgação (opcional)') }}</div>
                    <div class="text-sm text-slate-700">
                        {{ __('Se você já possui uma imagem de divulgação, envie o arquivo abaixo.') }}
                    </div>

                    <div class="flex flex-wrap items-start gap-4">
                        <div class="min-w-0 flex-auto basis-48">
                            <input id="event-promotion-upload" type="file" accept="image/*"
                                wire:model.live="bannerUpload" class="sr-only">

                            <label for="event-promotion-upload"
                                class="inline-flex w-fit cursor-pointer items-center gap-2 rounded-lg border border-sky-950 bg-sky-950 px-4 py-2 text-sm font-semibold text-white transition hover:bg-sky-900">
                                {{ __('Selecionar imagem do computador') }}
                            </label>

                            <div class="mt-2 text-xs text-slate-600">
                                {{ __('Formatos aceitos: JPG e PNG. Tamanho máximo: 5MB.') }}
                            </div>

                            @error('bannerUpload')
                                <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        @if ($bannerUpload && str_starts_with($bannerUpload->getMimeType(), 'image/'))
                            <div
                                class="w-56 flex-auto flex justify-center rounded-lg border border-slate-300 bg-slate-50 p-2">
                                <img src="{{ $bannerUpload->temporaryUrl() }}" alt="Arte de divulgação"
                                    class="max-h-52 w-auto rounded object-contain">
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="absolute bottom-2 inset-x-0 z-10 w-full px-4">
            <div
                class="flex items-center justify-between rounded-lg border border-sky-950 bg-white/80 px-6 py-3 shadow">
                <div class="min-w-24">
                    <button type="button" x-show="step > 1" x-on:click="previousStep">&#x276E; Voltar</button>
                </div>
                <div class="text-sm font-semibold text-sky-950">
                    <span x-text="`Passo ${step} de ${totalSteps}`"></span>
                </div>
                <div class="flex justify-end">
                    @if ($step < 5)
                        <button type="button" x-on:click="nextStep" x-bind:disabled="!canProceed"
                            class="disabled:cursor-not-allowed disabled:opacity-50">{{ __('Próximo') }}
                            &#x276F;</button>
                    @endif
                    @if ($step === 5)
                        <button type="button" wire:click="submit">{{ __('Salvar evento') }} &#x2713;</button>
                    @endif
                </div>
            </div>
        </div>

        <div wire:loading.flex wire:target="bannerUpload"
            class="absolute inset-0 z-40 items-center justify-center bg-slate-950/45 backdrop-blur-[1px]">
            <div class="rounded-xl bg-white px-5 py-4 text-sm font-semibold text-sky-950 shadow-lg">
                {{ __('Enviando arquivo. Aguarde...') }}
            </div>
        </div>
    </form>
</section>
