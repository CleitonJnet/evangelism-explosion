<div>
    <flux:modal name="edit-event-dates-modal" wire:model="showModal" class="max-w-5xl w-full">
        <div class="space-y-4">
            <div class="text-sm font-semibold text-heading">{{ __('Datas do treinamento') }}</div>
            <div class="flex items-center justify-end">
                <flux:button type="button" variant="outline" wire:click="addEventDate">
                    {{ __('Adicionar dia') }}
                </flux:button>
            </div>
            <div class="max-h-80 space-y-10 py-4">

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

            <div class="flex justify-end gap-3">
                <x-src.btn-silver type="button" wire:click="closeModal" wire:loading.attr="disabled"
                    wire:target="save">
                    {{ __('Close') }}
                </x-src.btn-silver>
                <x-src.btn-gold type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save">
                    <span wire:loading.remove wire:target="save">{{ __('Save') }}</span>
                    <span wire:loading wire:target="save">{{ __('Saving...') }}</span>
                </x-src.btn-gold>
            </div>
        </div>
    </flux:modal>
</div>
