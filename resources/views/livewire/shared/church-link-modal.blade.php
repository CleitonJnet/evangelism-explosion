<div>
    @if ($showChurchModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 px-4 py-8">
            <div class="w-full max-w-4xl overflow-hidden rounded-3xl bg-white shadow-xl">
                <div class="flex items-start justify-between gap-4 border-b border-slate-200 bg-slate-50 px-6 py-5">
                    <div>
                        <h3 class="text-lg font-extrabold text-slate-900">Selecione sua igreja</h3>
                        <p class="mt-1 text-sm text-slate-600">
                            Esse vínculo ajuda no acompanhamento da inscrição. Você pode fechar e continuar navegando.
                        </p>
                    </div>
                    <button type="button" wire:click="closeChurchModal"
                        class="rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-500 hover:text-slate-700">
                        Fechar
                    </button>
                </div>

                <div class="max-h-[70vh] space-y-6 overflow-y-auto px-6 py-6">
                    <form wire:submit="saveChurchSelection" class="space-y-6">
                        <x-src.form.input name="churchSearch" wire:model.live.debounce.300ms="churchSearch"
                            label="Buscar igreja" width_basic="900" />

                        <div class="max-h-80 space-y-2 overflow-y-auto">
                            @forelse ($this->churches as $church)
                                <div wire:key="church-option-{{ $church->id }}">
                                    <input type="radio" name="selectedChurch" class="peer sr-only"
                                        id="church-{{ $church->id }}" value="{{ $church->id }}"
                                        wire:model.live="selectedChurchId">
                                    <label for="church-{{ $church->id }}"
                                        class="block cursor-pointer select-none rounded-lg border-2 border-slate-300 px-4 py-2 transition-all hover:bg-white hover:shadow-[0_0_0_2px_#cad5e2] peer-checked:border-sky-900 peer-checked:[&_.church-check]:inline-flex">
                                        <div class="flex justify-between gap-2">
                                            <div class="font-bold">{{ $church->name }}</div>
                                            <div
                                                class="church-check hidden size-6 items-center justify-center rounded-full bg-sky-900 text-white">
                                                <div>&#x2713;</div>
                                            </div>
                                        </div>
                                        <div class="mb-1 border-b border-sky-950/20 pb-1 text-xs uppercase">
                                            {{ $church->pastor }}
                                        </div>
                                        <div class="text-xs opacity-80">{{ $church->district }}, {{ $church->city }},
                                            {{ $church->state }}</div>
                                    </label>
                                </div>
                            @empty
                                <div class="rounded-2xl border border-amber-200 bg-amber-50/70 p-4 text-sm text-amber-900">
                                    Não encontramos nenhuma igreja com esse filtro.
                                </div>
                            @endforelse
                        </div>

                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <button type="button" wire:click="openCreateChurchTempModal"
                                class="text-sm font-semibold text-sky-800 hover:underline">
                                I don't see my church
                            </button>

                            <div class="flex items-center gap-3">
                                <x-src.btn-silver label="Cancelar" type="button" wire:click="closeChurchModal"
                                    class="px-4 py-2" />
                                <x-src.btn-gold label="Salvar e continuar" type="submit" class="px-4 py-2" />
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <livewire:shared.create-church-temp-modal />
</div>
