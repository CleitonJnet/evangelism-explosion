<div>
    <flux:modal name="edit-event-dates-modal" wire:model="showModal" class="max-w-5xl w-full bg-sky-950! text-white! p-0!">
        <div class="flex max-h-[90vh] flex-col overflow-hidden rounded-2xl">
            <div class="sticky top-0 z-20 border-b border-sky-800 bg-sky-950 px-8 py-4">
                <div class="text-lg font-semibold text-white">{{ __('Datas do treinamento') }}</div>
            </div>
            <div class="min-h-0 flex-1 overflow-y-auto bg-slate-50 px-8 py-4 text-slate-950">
                <div class="flex items-center justify-end">
                    <x-src.btn-silver wire:click="addEventDate" :label="__('Adicionar dia')" />
                </div>

                <div class="grid gap-8 py-4">

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

            <div class="sticky bottom-0 z-20 border-t border-sky-800 bg-sky-950 px-8 py-4">
                <div class="flex justify-end gap-3">
                    <x-src.btn-silver type="button" wire:click="closeModal" wire:loading.attr="disabled"
                        wire:target="save">
                        {{ __('Fechar') }}
                    </x-src.btn-silver>
                    <x-src.btn-gold type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save">
                        <span wire:loading.remove wire:target="save">{{ __('Salvar') }}</span>
                        <span wire:loading wire:target="save">{{ __('Saving...') }}</span>
                    </x-src.btn-gold>
                </div>
            </div>
        </div>
    </flux:modal>
</div>
