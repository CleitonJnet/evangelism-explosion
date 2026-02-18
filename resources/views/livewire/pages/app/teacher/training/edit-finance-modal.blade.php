<div>
    <flux:modal name="edit-finance-modal" wire:model="showModal" class="max-w-5xl w-full">
        <div class="space-y-4">
            <div>
                <flux:heading size="lg">{{ __('Pagamento do treinamento') }}</flux:heading>
                <flux:subheading>
                    {{ __('Atualize despesas extras, desconto e dados PIX sem alterar o preço base.') }}
                </flux:subheading>
            </div>

            <div class="grid gap-10">
                <div class="flex justify-between gap-0.5s">
                    <div>{{ __('O custo do treinamento selecionado é:') }}</div>
                    <div class="border-b border-dashed border-sky-950 flex-auto"></div>
                    <div>{{ __('R$') }} {{ $price }}</div>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <x-src.form.input name="price_church" wire:model.live="price_church" label="Despesas extras"
                        class="text-right" width_basic="10" />
                    <x-src.form.input name="discount" wire:model.live="discount" label="Desconto por inscrição"
                        class="text-right" width_basic="10" />
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="grid gap-3 rounded-xl border border-slate-300 bg-white/70 p-4">
                        <div class="text-sm font-semibold text-sky-950">
                            {{ __('QR Code PIX da igreja sede (opcional)') }}
                        </div>

                        @if ($currentPixQrCodeUrl)
                            <div
                                class="w-40 flex-auto flex justify-center rounded-lg border border-slate-300 bg-slate-50 p-2">
                                <img src="{{ $currentPixQrCodeUrl }}" alt="QR Code PIX atual"
                                    class="max-h-32 w-auto rounded object-contain">
                            </div>
                        @endif

                        <input id="event-church-pix-qr-upload-modal-{{ $training->id }}" type="file" accept="image/*"
                            wire:model.live="pixQrCodeUpload" class="sr-only">

                        <label for="event-church-pix-qr-upload-modal-{{ $training->id }}"
                            class="inline-flex w-fit cursor-pointer items-center gap-2 rounded-lg border border-sky-950 bg-sky-950 px-4 py-2 text-sm font-semibold text-white transition hover:bg-sky-900">
                            {{ __('Selecionar novo QR Code') }}
                        </label>

                        <div class="text-xs text-slate-600">
                            {{ __('Se não enviar imagem, o sistema mantém o QR Code atual ou usa o padrão do Ministério de Evangelismo Explosivo.') }}
                        </div>

                        @error('pixQrCodeUpload')
                            <p class="text-sm font-semibold text-red-600">{{ $message }}</p>
                        @enderror

                        @if ($pixQrCodeUpload && str_starts_with($pixQrCodeUpload->getMimeType(), 'image/'))
                            <div
                                class="w-40 flex-auto flex justify-center rounded-lg border border-slate-300 bg-slate-50 p-2">
                                <img src="{{ $pixQrCodeUpload->temporaryUrl() }}" alt="Novo QR Code PIX"
                                    class="max-h-32 w-auto rounded object-contain">
                            </div>
                        @endif
                    </div>

                    <div class="grid content-start gap-2">
                        <x-src.form.input name="pix_key" wire:model.live="pix_key" label="Chave PIX da igreja sede"
                            width_basic="900" />
                        <div class="text-xs text-slate-600">
                            {{ __('Se não informar a chave, o sistema usa a chave PIX padrão do Ministério de Evangelismo Explosivo.') }}
                        </div>
                    </div>
                </div>

                <div class="flex justify-between gap-0.5 font-bold">
                    <div>{{ __('Valor final para cada inscrição:') }}</div>
                    <div class="border-b border-dashed border-sky-950 flex-auto"></div>
                    <div>{{ __('R$') }} {{ $this->finalPricePerRegistration }}</div>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <x-src.btn-silver type="button" wire:click="closeModal" wire:loading.attr="disabled"
                    wire:target="save,pixQrCodeUpload">
                    {{ __('Close') }}
                </x-src.btn-silver>
                <x-src.btn-gold type="button" wire:click="save" wire:loading.attr="disabled"
                    wire:target="save,pixQrCodeUpload">
                    {{ __('Save') }}
                </x-src.btn-gold>
            </div>
        </div>
    </flux:modal>
</div>
