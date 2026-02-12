<form wire:submit="submit">
    <div class="flex flex-wrap mt-6 gap-y-8 gap-x-4">
        <x-src.form.input name="initials" wire:model="initials" label="Sigla" type="text" width_basic="200" required />

        <x-src.form.input name="name" wire:model="name" label="Nome do Ministério" type="text" width_basic="320"
            required />

        <x-src.form.input name="logo" wire:model="logo" label="Logo (URL ou caminho)" type="text"
            width_basic="320" />

        <x-src.form.input name="color" wire:model="color" label="Cor" type="text" width_basic="200" />

        <x-src.form.textarea name="description" wire:model="description" label="Descrição" rows="2" />
    </div>

    <flux:button variant="primary" type="submit" class="w-full">
        {{ __('Save') }}
    </flux:button>
</form>
