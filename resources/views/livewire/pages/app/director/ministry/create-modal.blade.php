<div>
    <flux:modal name="director-create-ministry-modal" wire:model="showModal" class="max-w-3xl w-[calc(100%-4px)] mx-auto bg-sky-950! p-0! max-h-[calc(100vh-4px)]! overflow-hidden">
        <div class="flex max-h-[90vh] flex-col overflow-hidden rounded-2xl">
            <header class="sticky top-0 z-20 border-b border-sky-800 bg-sky-950 px-6 py-4 text-sky-50">
                <h3 class="text-lg font-semibold">{{ __('Cadastrar novo ministério') }}</h3>
                <p class="text-sm opacity-90">
                    {{ __('Preencha os dados do ministério para disponibilizar cursos e treinamentos relacionados.') }}
                </p>
            </header>

            <div class="min-h-0 flex-1 overflow-y-auto bg-white px-6 py-6">
                <div class="space-y-6">
                    <section class="space-y-5">
                        <div>
                            <h4 class="text-base font-semibold text-sky-950">{{ __('Dados do ministério') }}</h4>
                            <p class="text-sm text-slate-600">{{ __('Informações principais de identificação.') }}</p>
                        </div>

                        <div class="grid gap-6 lg:grid-cols-12">
                            <div class="lg:col-span-4">
                                <div class="grid justify-items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 p-4">
                                    <input id="director-ministry-logo-upload" type="file" accept="image/*"
                                        wire:model.live="logoUpload" class="sr-only">

                                    <label for="director-ministry-logo-upload"
                                        class="cursor-pointer overflow-hidden rounded-xl border border-slate-300 bg-slate-100 p-1">
                                        <img src="{{ $logoPreviewUrl }}" alt="{{ __('Logo do ministério') }}"
                                            class="h-28 w-28 rounded-lg object-cover">
                                    </label>

                                    <p class="text-center text-xs text-slate-600">
                                        {{ __('Clique na imagem para enviar a logo.') }}
                                    </p>

                                    @error('logoUpload')
                                        <p class="text-xs font-semibold text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="space-y-6 lg:col-span-8">
                                <div class="flex flex-wrap gap-x-4 gap-y-8">
                                    <x-src.form.input name="initials" wire:model.live="initials" label="Sigla"
                                        type="text" width_basic="160" required />
                                    <x-src.form.input name="name" wire:model.live="name" label="Nome do ministério"
                                        type="text" width_basic="280" required />
                                    <div class="relative z-0 max-w-full group" style="flex: 1 0 220px">
                                        <input id="director-ministry-color" type="color" wire:model.live="color"
                                            class="sr-only">
                                        <label for="director-ministry-color"
                                            class="flex h-11 w-full cursor-pointer items-center justify-between rounded-md border border-slate-300 px-3 shadow-xs transition hover:border-sky-500"
                                            style="background-color: {{ $color ?: '#4F4F4F' }}">
                                            <span class="rounded bg-black/35 px-2 py-0.5 text-xs font-semibold text-white">
                                                {{ __('Cor do ministério') }} *
                                            </span>
                                            <span
                                                class="rounded bg-black/35 px-2 py-0.5 text-xs font-semibold uppercase text-white">
                                                {{ $color ?: '#4F4F4F' }}
                                            </span>
                                        </label>

                                        @error('color')
                                            <p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="space-y-5">
                        <div>
                            <h4 class="text-base font-semibold text-sky-950">{{ __('Descrição') }}</h4>
                            <p class="text-sm text-slate-600">{{ __('Breve resumo sobre o ministério.') }}</p>
                        </div>

                        <div class="flex flex-wrap gap-x-4 gap-y-8">
                            <x-src.form.textarea name="description" wire:model.live="description" label="Descrição"
                                rows="3" />
                        </div>
                    </section>
                </div>
            </div>

            <footer class="sticky bottom-0 z-20 border-t border-sky-800 bg-sky-950 px-6 py-4 text-sky-50">
                <div class="flex justify-between gap-3">
                    <x-src.btn-silver type="button" wire:click="closeModal" wire:loading.attr="disabled"
                        wire:target="save">
                        {{ __('Cancelar') }}
                    </x-src.btn-silver>
                    <x-src.btn-gold type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save">
                        {{ __('Salvar') }}
                    </x-src.btn-gold>
                </div>
            </footer>
        </div>
    </flux:modal>
</div>
