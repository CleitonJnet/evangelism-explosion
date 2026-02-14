<section x-data="{
    {{-- step: @entangle('step').live, --}}
    step: 3,
        totalSteps: 4,
        async nextStep() {
                if (this.step >= this.totalSteps) {
                    return;
                }

                const canProceed = await this.$wire.canProceedStep(Number(this.step));

                if (canProceed) {
                    this.step++;
                }
            },
            previousStep() {
                if (this.step > 1) {
                    this.step--;
                }
            },
}"
    class="rounded-2xl border border-amber-300/20 bg-linear-to-br from-slate-100 via-white to-slate-200 p-6 shadow-lg h-full relative max-h-[calc(100vh-240px)]">

    <form x-on:submit.prevent class="">
        {{-- SELEÇÃO DO CURSO --}}
        <div x-cloak x-show="step === 1" id="step_1" class="flex flex-wrap">
            <div class="flex-1 ">{{ __('Selecione o Curso desejado:') }}</div>
            <div class="flex-1 grid gap-4">
                @foreach ($courses as $course)
                    <div>
                        <input type="radio" wire:model.live="course_id" name="course" class="peer sr-only"
                            id="{{ Str::slug($course->name) }}" value="{{ $course->id }}">
                        <label for="{{ Str::slug($course->name) }}"
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
        <div x-cloak x-show="step === 2" id="step_2" class="flex flex-wrap">
            <div class="flex-1 ">{{ __('Informe as datas:') }}</div>
            <div class="max-h-80 space-y-10 overflow-y-auto">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="text-sm font-semibold text-heading">{{ __('Datas do treinamento') }}</div>
                    <flux:button type="button" variant="outline" wire:click="addEventDate">
                        {{ __('Adicionar dia') }}
                    </flux:button>
                </div>

                @foreach ($eventDates as $index => $eventDate)
                    <div class="flex flex-wrap items-end gap-4">
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
            <div class="flex-1 ">
                {{ __('Selecione a Igreja Base do Evento:') }}
                <div class="text-justify">
                    {{ __('Se a igreja não está listada ao lado você pode adicioná-la em nossos registros a partir no botão abaixo:') }}
                </div>
                <div class="text-right">
                    <x-src.btn-silver :label="__('Abrir formulário de registro de novas igrejas')" />
                </div>
            </div>
            <div class="flex-1 grid gap-4">
                <x-src.form.input name="churchSearch" wire:model.live="churchSearch" label="Buscar igreja"
                    width_basic="900" autofocus="" />

                <div class="max-h-80 space-y-2 overflow-y-auto">
                    @foreach ($churches as $church)
                        <div>
                            <input type="radio" wire:model.live="church_id" name="church" class="peer sr-only"
                                id="{{ Str::slug($church->name) }}" value="{{ $church->id }}">
                            <label for="{{ Str::slug($church->name) }}"
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
        <div x-cloak x-show="step === 4" id="step_4" class="flex flex-wrap">
            <div class="flex-1 ">{{ __('Valores para Inscrição:') }}</div>
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

        <div class="absolute bottom-2 inset-x-0 z-10 w-full px-4">
            <div
                class="flex items-center justify-between rounded-lg border border-sky-950 bg-white/80 px-6 py-3 shadow">
                <div class="min-w-24">
                    <button type="button" x-show="step > 1" x-on:click="previousStep">&#x276E; Voltar</button>
                </div>
                <div class="flex justify-end">
                    <button type="button" x-show="step < totalSteps" x-on:click="nextStep"
                        @disabled(!$this->canProceedToNextStep)
                        class="disabled:cursor-not-allowed disabled:opacity-50">{{ __('Próximo') }}
                        &#x276F;</button>
                    <button type="button" wire:click="submit"
                        x-show="step === totalSteps">{{ __('Salvar evento') }}
                        &#x2713;</button>
                </div>
            </div>
        </div>
    </form>
</section>
