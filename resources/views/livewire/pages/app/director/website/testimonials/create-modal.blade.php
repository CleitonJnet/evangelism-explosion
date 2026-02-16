<div>
    @teleport('#director-testimonials-toolbar')
        <x-src.toolbar.button :href="'#'" :label="__('Novo testemunho')" icon="plus" :tooltip="__('Adicionar novo testemunho')"
            wire:click.prevent="openModal" />
    @endteleport

    <flux:modal name="director-create-testimonial-modal" wire:model="showModal" class="max-w-4xl w-full">
        <div class="space-y-4">
            <div>
                <flux:heading size="lg">{{ __('Novo testemunho') }}</flux:heading>
                <flux:subheading>{{ __('Preencha os dados para publicar no site.') }}</flux:subheading>
            </div>

            <div class="grid gap-4">
                <flux:field>
                    <flux:label>{{ __('Nome') }}</flux:label>
                    <flux:input wire:model.live="name" />
                    <flux:error name="name" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Cargo / igreja') }}</flux:label>
                    <flux:input wire:model.live="meta" />
                    <flux:error name="meta" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Visivel no site') }}</flux:label>
                    <div class="pt-2">
                        <x-app.switch-schedule :label="__('Ativo')" key="create-testimonial-active" :checked="$isActive"
                            wire:change="$set('isActive', $event.target.checked)" />
                    </div>
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Testemunho') }}</flux:label>
                    <div x-data="{ text: @entangle('quote').live }" class="space-y-1">
                        <flux:textarea rows="6" maxlength="250" x-model="text" wire:model.live="quote" />
                        <div class="text-right text-xs text-slate-500">
                            <span x-text="(text ?? '').length"></span>/250
                        </div>
                    </div>
                    <flux:error name="quote" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Foto da pessoa') }}</flux:label>
                    <input type="file" accept=".jpg,.jpeg,.png,.webp" wire:model.live="photoUpload"
                        class="w-full rounded-xl border border-neutral-200 bg-white p-2 text-sm text-neutral-700 file:me-4 file:rounded-lg file:border-0 file:bg-neutral-900 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white">
                    <div class="text-[11px] text-neutral-500">
                        {{ __('Formatos aceitos: JPG, JPEG, PNG ou WEBP (até 5MB).') }}
                    </div>
                    @if ($photoUpload)
                        <img src="{{ $photoUpload->temporaryUrl() }}" alt="{{ __('Pré-visualização da foto') }}"
                            class="h-24 w-24 rounded-lg border border-slate-200 object-cover">
                    @endif
                    <flux:error name="photoUpload" />
                </flux:field>
            </div>

            <div class="flex justify-end gap-2">
                <flux:button type="button" variant="ghost" wire:click="closeModal">{{ __('Cancelar') }}</flux:button>
                <flux:button type="button" variant="primary" wire:click="save" wire:loading.attr="disabled"
                    wire:target="save">
                    {{ __('Salvar') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
