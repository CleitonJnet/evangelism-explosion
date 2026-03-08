<flux:modal name="training-material-delivery-modal" wire:model="showModal" class="max-w-4xl">
    <div class="flex max-h-[85vh] flex-col overflow-hidden rounded-3xl bg-white">
        <div class="shrink-0 border-b border-slate-200 px-6 py-4">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <flux:heading size="lg">{{ __('Registrar entrega de material') }}</flux:heading>
                    <flux:subheading>
                        {{ $training->course?->name ?? __('Treinamento') }} · {{ $training->course?->ministry?->name ?? __('Ministério') }}
                    </flux:subheading>
                </div>

                <button type="button" class="text-sm font-semibold text-slate-500 transition hover:text-slate-700"
                    wire:click="closeModal">
                    {{ __('Fechar') }}
                </button>
            </div>
        </div>

        <div class="min-h-0 flex-1 overflow-y-auto px-6 py-5">
            <div class="grid gap-6 lg:grid-cols-[minmax(0,1.3fr)_minmax(18rem,1fr)]">
                <div class="space-y-4">
                    @error('delivery')
                        <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                            {{ $message }}
                        </div>
                    @enderror

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <x-src.form.select name="training-delivery-inventory" wire:model="inventory_id"
                                label="Estoque de origem" width_basic="240" :options="$inventoryOptions" required />
                            @error('inventory_id')
                                <div class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <x-src.form.select name="training-delivery-material" wire:model.live="material_id"
                                label="Material ou kit" width_basic="240" :options="$materialOptions" required />
                            @error('material_id')
                                <div class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <x-src.form.select name="training-delivery-participant" wire:model.live="participant_id"
                                label="Participante inscrito" width_basic="240" :options="$participantOptions" />
                            @error('participant_id')
                                <div class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">{{ __('Participante avulso') }}</label>
                            <x-src.form.input name="training-delivery-participant-note" wire:model="participant_note"
                                label="Participante avulso"
                                placeholder="{{ __('Use quando a entrega não estiver vinculada a um inscrito') }}" />
                            @error('participant_note')
                                <div class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-[12rem_minmax(0,1fr)]">
                        <div>
                            <x-src.form.input name="training-delivery-quantity" type="number" min="1"
                                wire:model="quantity" label="Quantidade" />
                            @error('quantity')
                                <div class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <x-src.form.textarea name="training-delivery-notes" wire:model="notes" label="Observação"
                                placeholder="{{ __('Ex.: entrega no credenciamento, reposição de material, uso em sala.') }}" />
                            @error('notes')
                                <div class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <aside class="space-y-4 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                    <div>
                        <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-900">
                            {{ __('Apoio operacional') }}
                        </h3>
                        <p class="mt-1 text-xs text-slate-600">
                            {{ __('Os materiais disponíveis aqui seguem o vínculo do curso com o treinamento. O financeiro continua separado da entrega física.') }}
                        </p>
                    </div>

                    @if ($selectedMaterial)
                        <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3">
                            <div class="text-sm font-semibold text-slate-900">{{ $selectedMaterial->name }}</div>
                            <div class="mt-1 text-xs font-semibold uppercase text-sky-700">
                                {{ $selectedMaterial->isComposite() ? __('Material composto') : __('Material simples') }}
                            </div>

                            @if ($selectedMaterial->isComposite())
                                <div class="mt-3 space-y-2">
                                    <div class="text-xs font-semibold uppercase text-slate-500">{{ __('Componentes do kit') }}</div>
                                    @foreach ($selectedMaterial->components as $component)
                                        <div class="flex items-center justify-between gap-3 text-sm text-slate-700">
                                            <span>{{ $component->componentMaterial?->name ?? __('Componente removido') }}</span>
                                            <span class="font-semibold">x{{ $component->quantity }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-4 py-4 text-sm text-slate-600">
                            {{ __('Selecione um material para ver o contexto operacional do treinamento.') }}
                        </div>
                    @endif

                    <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs text-amber-900">
                        {{ __('Se o material selecionado for composto, a saída baixa o kit e seus componentes no mesmo estoque, preservando o vínculo com este treinamento.') }}
                    </div>
                </aside>
            </div>
        </div>

        <div class="shrink-0 border-t border-slate-200 px-6 py-4">
            <div class="flex items-center justify-end gap-3">
                <x-src.btn-silver type="button" wire:click="closeModal">
                    {{ __('Cancelar') }}
                </x-src.btn-silver>

                <x-src.btn-gold type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save">
                    <span wire:loading.remove wire:target="save">{{ __('Registrar entrega') }}</span>
                    <span wire:loading wire:target="save">{{ __('Registrando...') }}</span>
                </x-src.btn-gold>
            </div>
        </div>
    </div>
</flux:modal>
