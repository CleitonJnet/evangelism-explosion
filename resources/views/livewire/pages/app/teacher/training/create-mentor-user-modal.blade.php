<div>
    <flux:modal name="create-mentor-user-modal" wire:model="showModal" class="max-w-5xl w-full p-0!">
        <div>
            <header
                class="border-b border-sky-950/20 bg-linear-to-br from-sky-950 via-sky-900 to-sky-950 px-6 py-4 text-sky-50">
                <h3 class="text-lg font-semibold">{{ __('Cadastrar novo mentor') }}</h3>
                <p class="text-sm opacity-80">
                    {{ __('Crie um usuário mentor e vincule-o a uma igreja oficial.') }}
                </p>
            </header>

            <div class="max-h-[calc(100vh-160px)] overflow-y-auto">
                <div class="space-y-8 px-6 py-6">
                    <section class="space-y-5">
                        <div>
                            <h4 class="text-base font-semibold text-sky-950">{{ __('Dados do mentor') }}</h4>
                        </div>

                        <div class="flex flex-wrap gap-x-4 gap-y-8">
                            <x-src.form.input name="name" wire:model.live="name" label="Nome completo"
                                width_basic="320" required />
                            <x-src.form.input type="email" name="email" wire:model.live="email" label="E-mail"
                                width_basic="320" required />
                            <x-src.form.input type="tel" name="phone" wire:model.live="phone"
                                label="Telefone (opcional)" width_basic="220" />
                        </div>
                    </section>

                    <section class="space-y-5">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <h4 class="text-base font-semibold text-sky-950">{{ __('Igreja oficial') }}</h4>
                                <p class="text-sm text-slate-600">
                                    {{ __('Selecione uma igreja cadastrada ou crie uma nova.') }}</p>
                            </div>
                            <x-src.btn-silver type="button" wire:click="openCreateChurchModal"
                                wire:loading.attr="disabled" wire:target="save,openCreateChurchModal">
                                {{ __('Criar igreja') }}
                            </x-src.btn-silver>
                        </div>

                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                            <label class="mb-1 block text-xs font-semibold text-slate-700">
                                {{ __('Buscar igreja') }}
                            </label>
                            <input type="text" wire:model.live.debounce.300ms="churchSearch"
                                placeholder="{{ __('Nome, cidade ou UF') }}"
                                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800" />

                            <div
                                class="mt-2 max-h-44 space-y-1 overflow-y-auto rounded-lg border border-slate-200 bg-white p-2">
                                @forelse ($churchResults as $churchResult)
                                    <button type="button"
                                        class="w-full rounded-md border border-slate-200 bg-white px-2 py-1 text-left text-xs text-slate-700 hover:bg-slate-100 cursor-pointer"
                                        wire:click="selectChurch({{ $churchResult->id }})"
                                        wire:loading.attr="disabled" wire:target="save,selectChurch">
                                        <span class="font-semibold">{{ $churchResult->name }}</span>
                                        <span class="text-slate-500">
                                            ({{ $churchResult->city ?? __('Cidade não informada') }}
                                            @if ($churchResult->state)
                                                /{{ $churchResult->state }}
                                            @endif)
                                        </span>
                                    </button>
                                @empty
                                    <div class="text-xs text-slate-500">
                                        {{ __('Nenhuma igreja encontrada.') }}
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        @if ($selectedChurchId && $selectedChurchName)
                            <div
                                class="inline-flex items-center rounded-md border border-emerald-200 bg-emerald-50 px-2 py-1 text-xs font-semibold text-emerald-700">
                                {{ __('Igreja selecionada') }}: {{ $selectedChurchName }}
                            </div>
                        @else
                            <div class="text-xs text-slate-500">
                                {{ __('Selecione uma igreja antes de salvar.') }}
                            </div>
                        @endif

                        @error('selectedChurchId')
                            <div class="text-xs font-semibold text-red-600">{{ $message }}</div>
                        @enderror
                    </section>
                </div>

                <div
                    class="sticky bottom-0 inset-x-0 mt-8 flex justify-between gap-2 border-t border-sky-950/20 bg-linear-to-br from-sky-950 via-sky-900 to-sky-950 px-6 py-4 text-sky-50">
                    <x-src.btn-silver type="button" wire:click="closeModal">
                        {{ __('Cancelar') }}
                    </x-src.btn-silver>
                    <x-src.btn-gold type="button" wire:click="save" wire:loading.attr="disabled"
                        wire:target="save,selectChurch,openCreateChurchModal">
                        {{ __('Salvar mentor') }}
                    </x-src.btn-gold>
                </div>
            </div>
        </div>
    </flux:modal>

    <livewire:shared.create-church-modal :trainingId="$trainingId"
        wire:key="create-mentor-church-modal-{{ $trainingId }}" />
</div>
