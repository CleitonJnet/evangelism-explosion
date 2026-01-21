<form wire:submit='submit'>
    <div class="flex flex-wrap mt-6 gap-y-8 gap-x-4">

        <x-src.form.input name="church_name" wire:model='church_name' label="Nome completo da Igreja" type="text"
            width_basic="300" required />

        <x-src.form.input name="pastor_name" wire:model='pastor_name' label="Nome do pastor titular" type="text"
            width_basic="300" required />

        <x-src.form.input type="tel" name="phone_church" wire:model='phone_church'
            label="Telefone &#10023; WhatsApp" width_basic="300" required />

        <x-src.form.input type="email" name="church_email" wire:model='church_email' label="E-mail da Igreja"
            width_basic="300" />

        <x-src.form.input name="church_contact" wire:model='church_contact' label="Nome completo do Contato"
            type="text" width_basic="300" required />

        <x-src.form.input name="church_contact_phone" wire:model='church_contact_phone' label="Telefone do Contato"
            type="tel" width_basic="300" required />

        <x-src.form.input name="church_contact_email" wire:model='church_contact_email' type="email"
            label="Email do Contato" width_basic="300" required />

        <livewire:address-fields wire:model="churchAddress" title="Endereço da Igreja" wire:key="address-church" />

        <x-src.form.textarea name="church_notes" wire:model='church_notes' label="Comentários sobre a Igreja"
            rows="1" required />

    </div>

    <flux:button variant="primary" type="submit" class="w-full">
        {{ __('Save') }}
    </flux:button>
</form>
