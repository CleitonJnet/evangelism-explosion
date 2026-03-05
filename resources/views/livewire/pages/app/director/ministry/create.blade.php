<form wire:submit="submit">
    <div class="mt-6 grid gap-6 lg:grid-cols-12">
        <div class="lg:col-span-4">
            <div class="grid justify-items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 p-4">
                <input id="director-ministry-create-logo-upload" type="file" accept="image/*"
                    wire:model.live="logoUpload" class="sr-only">

                <label for="director-ministry-create-logo-upload"
                    class="cursor-pointer overflow-hidden rounded-xl border border-slate-300 bg-slate-100 p-1">
                    <img src="{{ $logoUpload ? $logoUpload->temporaryUrl() : asset('images/logo/ee-gold.webp') }}"
                        alt="{{ __('Logo do ministério') }}" class="h-28 w-28 rounded-lg object-cover">
                </label>

                <p class="text-center text-xs text-slate-600">{{ __('Clique na imagem para enviar a logo.') }}</p>

                @error('logoUpload')
                    <p class="text-xs font-semibold text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="space-y-6 lg:col-span-8">
            <div class="flex flex-wrap gap-x-4 gap-y-8">
                <x-src.form.input name="initials" wire:model="initials" label="Sigla" type="text" width_basic="160"
                    required />

                <x-src.form.input name="name" wire:model="name" label="Nome do Ministério" type="text"
                    width_basic="280" required />

                <div class="relative z-0 max-w-full group" style="flex: 1 0 220px">
                    <input id="director-ministry-create-color" type="color" wire:model="color" class="sr-only">
                    <label for="director-ministry-create-color"
                        class="flex h-11 w-full cursor-pointer items-center justify-between rounded-md border border-slate-300 px-3 shadow-xs transition hover:border-sky-500"
                        style="background-color: {{ $color ?: '#4F4F4F' }}">
                        <span class="rounded bg-black/35 px-2 py-0.5 text-xs font-semibold text-white">
                            {{ __('Cor') }}
                        </span>
                        <span class="rounded bg-black/35 px-2 py-0.5 text-xs font-semibold uppercase text-white">
                            {{ $color ?: '#4F4F4F' }}
                        </span>
                    </label>
                    @error('color')
                        <p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex flex-wrap gap-x-4 gap-y-8">
                <x-src.form.textarea name="description" wire:model="description" label="Descrição" rows="2" />
            </div>
        </div>
    </div>

    <flux:button variant="primary" type="submit" class="w-full">
        {{ __('Save') }}
    </flux:button>
</form>
