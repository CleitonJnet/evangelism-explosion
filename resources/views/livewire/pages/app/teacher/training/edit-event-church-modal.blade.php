<div>
    <flux:modal name="edit-event-church-modal" wire:model="showModal" class="max-w-6xl w-full bg-sky-950! p-0!">
        <div class="space-y-4">
            <div class="px-6 pt-4">
                <flux:heading size="lg"><span class="text-white!">{{ __('Igreja sede e endereço do evento') }}</span>
                </flux:heading>
                <flux:subheading>
                    <span class="text-white! opacity-80">
                        {{ __('Selecione ou cadastre a igreja sede e ajuste os dados de endereço e liderança conforme a necessidade do evento.') }}
                    </span>
                </flux:subheading>
            </div>

            <div class="max-h-[calc(100vh-220px)] space-y-6 overflow-y-auto bg-white/95 px-6 py-4">
                <div class="flex flex-wrap gap-4">
                    <div class="flex-1">
                        <img src="{{ asset(path: 'images/banner-create-training-base.png') }}"
                            alt="Ilustração de seleção da igreja base"
                            class="mb-4 h-32 w-full rounded-lg border border-sky-950/10 object-cover" />
                        <div class="text-base font-semibold text-sky-950">{{ __('Escolha a igreja base do evento') }}</div>
                        <div class="text-slate-700 text-justify ">
                            {{ __('Use a busca para localizar a igreja anfitriã e selecioná-la na lista. Se a igreja ainda não existir no sistema, use o botão abaixo para cadastrar e continuar sem sair deste registro.') }}

                            <livewire:pages.app.teacher.training.create-church-modal wire:model="newChurchSelection"
                                :training-course-id="$training->course_id"
                                wire:key="teacher-training-edit-church-create-church-modal-{{ $training->id }}" />
                        </div>
                    </div>
                    <div class="flex-1 grid gap-4">
                        <x-src.form.input name="churchSearch" wire:model.live="churchSearch" label="Buscar igreja"
                            width_basic="900" autofocus="" />

                        <div class="max-h-80 space-y-2 overflow-y-auto">
                            @foreach ($churches as $church)
                                <div wire:key="edit-church-option-{{ $church->id }}">
                                    <input type="radio" name="church" class="peer sr-only"
                                        id="edit-church-{{ $church->id }}" value="{{ $church->id }}"
                                        wire:click="selectChurch({{ $church->id }})"
                                        @checked((int) $church_id === (int) $church->id)>
                                    <label for="edit-church-{{ $church->id }}"
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

                <div class="grid gap-4 rounded-xl border border-slate-300 bg-white/70 p-4">
                    <div class="text-sm font-semibold text-sky-950">
                        {{ __('Liderança do evento') }}
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <x-src.form.input name="leader" wire:model.live="leader" label="Líder do Evento"
                            width_basic="900" required />
                        <x-src.form.input name="coordinator" wire:model.live="coordinator"
                            label="Coordenador do Evento" width_basic="900" required />
                    </div>
                    <div class="text-sm font-semibold text-sky-950">
                        {{ __('Contato para informações do evento') }}
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <x-src.form.input type="tel" name="phone" wire:model.live="phone" label="Telefone"
                            width_basic="240" />
                        <x-src.form.input type="email" name="email" wire:model.live="email" label="E-mail"
                            width_basic="320" />
                    </div>
                </div>

                <div class="grid gap-4 rounded-xl border border-slate-300 bg-white/70 p-4">
                    <div class="text-sm font-semibold text-sky-950">
                        {{ __('Endereço do evento') }}
                    </div>
                    <livewire:address-fields wire:model="address" title="Endereço do evento"
                        wire:key="teacher-training-edit-event-address-{{ $training->id }}-{{ $church_id ?? 'none' }}" />
                </div>
            </div>

            <div class="flex justify-end gap-3 px-6 pb-4">
                <x-src.btn-silver type="button" wire:click="closeModal" wire:loading.attr="disabled"
                    wire:target="save">
                    {{ __('Fechar') }}
                </x-src.btn-silver>
                <x-src.btn-gold type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save">
                    {{ __('Salvar') }}
                </x-src.btn-gold>
            </div>
        </div>
    </flux:modal>
</div>
