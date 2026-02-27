<div>
    <flux:modal name="edit-event-banner-modal" wire:model="showModal" class="max-w-4xl w-full bg-sky-950! p-0!">
        <div class="flex max-h-[90vh] flex-col overflow-hidden rounded-2xl">
            <div class="sticky top-0 z-20 border-b border-sky-800 bg-sky-950 px-6 py-4">
                <flux:heading size="lg"><span class="text-white!">{{ __('Banner do evento') }}</span></flux:heading>
                <flux:subheading>
                    <span class="text-white! opacity-80">
                        {{ __('Envie a arte de divulgação do evento para uso nas páginas públicas e materiais de compartilhamento.') }}
                    </span>
                </flux:subheading>
            </div>

            <div class="min-h-0 flex-1 overflow-y-auto bg-white/95 px-6 py-4">
                <div class="grid gap-6">
                    <div class="grid gap-4 rounded-xl border border-slate-300 bg-white/70 p-4">
                        <div class="text-sm font-semibold text-sky-950">
                            {{ __('Upload da arte de divulgação') }}
                        </div>

                        @if ($currentBannerUrl && !$bannerUpload)
                            <div
                                class="w-56 flex-auto flex justify-center rounded-lg border border-slate-300 bg-slate-50 p-2">
                                <img src="{{ $currentBannerUrl }}" alt="Banner atual do evento"
                                    class="max-h-52 w-auto rounded object-contain">
                            </div>
                        @endif

                        <div class="flex flex-wrap items-start gap-4">
                            <div class="min-w-0 flex-auto basis-48">
                                <input id="event-banner-upload-modal-{{ $training->id }}" type="file" accept="image/*"
                                    wire:model.live="bannerUpload" class="sr-only">

                                <label for="event-banner-upload-modal-{{ $training->id }}"
                                    class="inline-flex w-fit cursor-pointer items-center gap-2 rounded-lg border border-sky-950 bg-sky-950 px-4 py-2 text-sm font-semibold text-white transition hover:bg-sky-900">
                                    {{ __('Selecionar imagem do computador') }}
                                </label>

                                <div class="mt-2 text-xs text-slate-600">
                                    {{ __('Formatos aceitos: webp e PNG. Tamanho máximo: 10MB.') }}
                                </div>

                                @error('bannerUpload')
                                    <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            @if ($bannerUpload && str_starts_with($bannerUpload->getMimeType(), 'image/'))
                                <div
                                    class="w-56 flex-auto flex justify-center rounded-lg border border-slate-300 bg-slate-50 p-2">
                                    <img src="{{ $bannerUpload->temporaryUrl() }}" alt="Prévia do novo banner do evento"
                                        class="max-h-52 w-auto rounded object-contain">
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="sticky bottom-0 z-20 border-t border-sky-800 bg-sky-950 px-6 py-4">
                <div class="flex justify-end gap-3">
                    <x-src.btn-silver type="button" wire:click="closeModal" wire:loading.attr="disabled"
                        wire:target="save,bannerUpload">
                        {{ __('Fechar') }}
                    </x-src.btn-silver>
                    <x-src.btn-gold type="button" wire:click="save" wire:loading.attr="disabled"
                        wire:target="save,bannerUpload">
                        {{ __('Salvar banner') }}
                    </x-src.btn-gold>
                </div>
            </div>
        </div>
    </flux:modal>
</div>
