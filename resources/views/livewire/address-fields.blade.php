<div class="flex flex-wrap gap-y-8 gap-x-4">

    <div class="relative z-0 max-w-full group" style="flex: 1 0 150px">
        <x-src.form.input name="postal_code" label="CEP" type="text" class="postal_code" width_basic="150"
            wire:model.blur="address.postal_code" wire:blur="lookupCep($event.target.value)" />

        <div class="flex-auto text-xs">
            <span class="text-sky-800" wire:loading wire:target="lookupCep">
                Consultando CEP...
            </span>

            @if ($cepError)
                <span class="font-semibold text-red-600">{{ $cepError }}</span>
            @endif
        </div>
    </div>

    <x-src.form.input type="text" name="street" wire:model="address.street" :value="old('address.street')" label="Logradouro"
        :note="$note['street']" width_basic="400"
        note='<span class="text-xs text-sky-800" wire:loading wire:target="lookupCep">Carregando endereço...</span>' />

    <x-src.form.input type="text" name="number" wire:model="address.number" :value="old('address.number')" label="Número"
        width_basic="150" />

    <x-src.form.input type="text" name="complement" wire:model="address.complement" :value="old('address.complement')"
        label="Complemento" width_basic="250" />

    <x-src.form.input type="text" name="district" wire:model="address.district" :value="old('address.district')" label="Bairro"
        :required="$requireDistrictCityState"
        note='<span class="text-xs text-sky-800" wire:loading wire:target="lookupCep">Carregando endereço...</span>'
        width_basic="250" />

    <x-src.form.input type="text" name="city" wire:model="address.city" :value="old('address.city')" label="Cidade"
        :required="$requireDistrictCityState"
        note='<span class="text-xs text-sky-800" wire:loading wire:target="lookupCep">Carregando endereço...</span>'
        width_basic="300" />

    <x-src.form.select name="state" wire:model="address.state" :value="old('address.state')" label="UF" :required="$requireDistrictCityState"
        note='<span class="text-xs text-sky-800" wire:loading wire:target="lookupCep">Carregando endereço...</span>'
        width_basic="200" :options="$stateOptions" />
</div>
