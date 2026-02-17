<div>
    <flux:modal name="church-temp-review-modal" wire:model="showModal" class="max-w-5xl w-full">
        <div class="space-y-4">
            <div>
                <flux:heading size="lg">{{ __('Validação de igrejas pendentes') }}</flux:heading>
                <flux:subheading>
                    {{ __('Revise as igrejas temporárias utilizadas pelos inscritos deste treinamento.') }}
                </flux:subheading>
            </div>

            @if (!$hasPendingTemps)
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900">
                    {{ __('Nenhuma igreja pendente para validar neste treinamento.') }}
                </div>
            @else
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <label class="mb-1 block text-xs font-semibold text-slate-700">
                        {{ __('Filtrar igrejas pendentes') }}
                    </label>
                    <input type="text" wire:model.live.debounce.300ms="pendingSearch"
                        placeholder="{{ __('Nome, pastor, cidade, UF, endereço...') }}"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800" />
                </div>

                @if ($pendingTemps === [])
                    <div class="rounded-xl border border-slate-200 bg-white p-4 text-sm text-slate-700">
                        {{ __('Nenhuma igreja pendente encontrada para o filtro informado.') }}
                    </div>
                @else
                    <div class="max-h-[70vh] space-y-4 overflow-y-auto pr-1">
                        @foreach ($pendingTemps as $pendingTemp)
                            <article class="rounded-xl border border-slate-200 bg-white p-4">
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div>
                                        <div class="text-base font-semibold text-slate-900">{{ $pendingTemp['name'] }}
                                        </div>
                                        <div class="text-xs text-slate-600">
                                            {{ $pendingTemp['city'] ?: __('Cidade não informada') }}
                                            @if ($pendingTemp['state'])
                                                / {{ $pendingTemp['state'] }}
                                            @endif
                                        </div>
                                        <div class="mt-1 text-xs font-semibold text-amber-800">
                                            {{ $pendingTemp['users_count'] }} {{ __('inscrito(s) vinculado(s)') }}
                                        </div>
                                        @if ($pendingTemp['has_possible_match'])
                                            <div
                                                class="mt-1 inline-flex items-center rounded-md border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-700">
                                                {{ __('Possible match') }}
                                            </div>
                                        @endif
                                    </div>

                                    <div class="flex flex-wrap items-center gap-2">
                                        @if ($pendingTemp['quick_merge_church_id'] && $pendingTemp['quick_merge_church_name'])
                                            <button type="button"
                                                class="inline-flex items-center justify-center rounded-lg border border-emerald-200 bg-emerald-50 px-2 py-1 text-xs font-semibold text-emerald-800 transition hover:bg-emerald-100 cursor-pointer"
                                                wire:click="quickMergeTemp({{ $pendingTemp['id'] }})"
                                                wire:loading.attr="disabled"
                                                wire:target="openApproveReview,mergeTemp,quickMergeTemp">
                                                {{ __('Merge into') }} {{ $pendingTemp['quick_merge_church_name'] }}
                                            </button>
                                        @endif
                                        <x-src.btn-gold type="button"
                                            wire:click="openApproveReview({{ $pendingTemp['id'] }})"
                                            wire:loading.attr="disabled"
                                            wire:target="openApproveReview,mergeTemp,quickMergeTemp">
                                            {{ __('Review & Approve') }}
                                        </x-src.btn-gold>
                                    </div>
                                </div>

                                <div class="mt-4 flex flex-wrap items-end gap-3">
                                    <div class="min-w-72 flex-1">
                                        <label class="mb-1 block text-xs font-semibold text-slate-700">
                                            {{ __('Buscar igreja oficial para mesclar') }}
                                        </label>
                                        <input type="text"
                                            wire:model.live.debounce.300ms="mergeChurchSearch.{{ $pendingTemp['id'] }}"
                                            placeholder="{{ __('Digite para buscar igreja oficial') }}"
                                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800" />

                                        <div
                                            class="mt-2 max-h-32 space-y-1 overflow-y-auto rounded-lg border border-slate-200 p-2">
                                            @forelse ($this->mergeChurchSearchResults($pendingTemp['id']) as $churchOption)
                                                <button type="button"
                                                    class="w-full rounded-md border border-slate-200 bg-white px-2 py-1 text-left text-xs text-slate-700 hover:bg-slate-100 cursor-pointer"
                                                    wire:click="selectMergeTarget({{ $pendingTemp['id'] }}, {{ $churchOption['id'] }})"
                                                    wire:loading.attr="disabled"
                                                    wire:target="selectMergeTarget,mergeTemp,quickMergeTemp">
                                                    {{ $churchOption['label'] }}
                                                </button>
                                            @empty
                                                <div class="text-xs text-slate-500">
                                                    {{ __('Nenhuma igreja encontrada.') }}
                                                </div>
                                            @endforelse
                                        </div>

                                        @if (!empty($mergeTargets[$pendingTemp['id']]))
                                            <div class="mt-1 text-xs font-semibold text-emerald-700">
                                                {{ __('Igreja selecionada') }}:
                                                {{ collect($churchOptions)->firstWhere('id', $mergeTargets[$pendingTemp['id']])['label'] ?? '' }}
                                            </div>
                                        @else
                                            <div class="mt-1 text-xs text-slate-500">
                                                {{ __('Selecione uma igreja na lista acima.') }}
                                            </div>
                                        @endif

                                        @error('mergeTargets.' . $pendingTemp['id'])
                                            <div class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <x-src.btn-silver type="button" wire:click="mergeTemp({{ $pendingTemp['id'] }})"
                                        wire:loading.attr="disabled"
                                        wire:target="selectMergeTarget,openApproveReview,mergeTemp,quickMergeTemp">
                                        {{ __('Merge') }}
                                    </x-src.btn-silver>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            @endif

            <div class="flex justify-end">
                <flux:button type="button" variant="ghost" wire:click="closeModal">
                    {{ __('Fechar') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
