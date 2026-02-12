<form wire:submit='submit'>
    <div class="flex flex-wrap mt-6 gap-y-8 gap-x-4">
        <x-src.form.select name="church_id" wire:model="church_id" :value="old('church_id')" :label="__('Igreja Base')"
            width_basic="250" :options="$churches" required />

        <x-src.form.input name="since_date" wire:model='since_date' label="Desde" type="date" width_basic="150" />

        <x-src.form.textarea name="notes" wire:model='notes' label="Anotações" rows="2" />

    </div>

    <flux:button variant="primary" type="submit" class="w-full">
        {{ __('Save') }}
    </flux:button>
</form>
