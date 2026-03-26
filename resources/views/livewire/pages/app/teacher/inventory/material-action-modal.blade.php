<div>
    <flux:modal name="teacher-material-action-modal" wire:model="showModal" class="max-w-5xl w-[calc(100%-4px)] mx-auto bg-sky-950! p-0! max-h-[calc(100vh-4px)]! overflow-hidden">
        @php
            $badgeTextColor = static function (?string $hexColor): string {
                $color = trim((string) $hexColor);

                if (!preg_match('/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/', $color)) {
                    return '#0f172a';
                }

                $normalized = ltrim($color, '#');

                if (strlen($normalized) === 3) {
                    $normalized = collect(str_split($normalized))
                        ->map(fn(string $char): string => $char . $char)
                        ->implode('');
                }

                $red = hexdec(substr($normalized, 0, 2));
                $green = hexdec(substr($normalized, 2, 2));
                $blue = hexdec(substr($normalized, 4, 2));

                $luminance = ($red * 299 + $green * 587 + $blue * 114) / 1000;

                return $luminance > 150 ? '#0f172a' : '#f8fafc';
            };

            $badgeBackground = static function (?string $hexColor): string {
                $color = trim((string) $hexColor);

                if (!preg_match('/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/', $color)) {
                    return '#e2e8f0';
                }

                return $color;
            };
        @endphp
        <div class="flex max-h-[90vh] flex-col overflow-hidden rounded-2xl">
            <header class="sticky top-0 z-20 border-b border-sky-800 bg-sky-950 px-6 py-4 text-sky-50">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="space-y-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="text-lg font-semibold">{{ $material->name ?: __('Produto sem nome') }}</h3>
                            @if (!$material->is_active)
                                <span class="rounded-full bg-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-700">
                                    {{ __('Inativo') }}
                                </span>
                            @endif
                        </div>
                        <div class="pt-1">
                            <div class="mt-2 flex flex-wrap gap-2">
                                @forelse ($material->courses as $course)
                                    @php($courseTooltip = $course->type ? $course->type . ': ' . $course->name : $course->name)
                                    <span class="rounded-full px-3 py-1 text-xs font-semibold"
                                        title="{{ $courseTooltip }}" aria-label="{{ $courseTooltip }}"
                                        style="background-color: {{ $badgeBackground($course->color) }}; color: {{ $badgeTextColor($course->color) }};">
                                        {{ $course->initials ?: $course->name }}
                                    </span>
                                @empty
                                    <span class="text-sm text-sky-100/75">{{ __('Nenhum curso vinculado') }}</span>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="pt-1 text-right">
                        <div
                            class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $material->isComposite() ? 'bg-amber-100 text-amber-900' : 'bg-sky-100 text-sky-900' }}">
                            {{ $material->isComposite() ? __('Produto composto') : __('Item simples') }}
                        </div>
                        <div class="mt-2 text-sm text-sky-100/80">
                            {{ __('Estoque delegado: :inventory', ['inventory' => $inventory->name]) }}
                        </div>
                        <div
                            class="mt-2 inline-flex rounded-full bg-white/10 px-3 py-1 text-xs font-semibold text-sky-50">
                            @if ($material->isComposite())
                                {{ __('Pode compor: :quantity', ['quantity' => $composableQuantity]) }}
                            @else
                                {{ __('Saldo atual: :quantity', ['quantity' => $currentQuantity]) }}
                            @endif
                        </div>
                    </div>
                </div>
            </header>

            <div class="min-h-0 flex-1 overflow-y-auto bg-white px-6 py-6">
                @if (count($availableTabs) > 1)
                    <div class="mb-6 overflow-x-auto border-b border-slate-200 pb-4">
                        <div class="flex min-w-max gap-2 rounded-2xl bg-slate-100/80 p-2 md:min-w-0 md:w-full">
                            @if (in_array('entry', $availableTabs, true))
                                <button type="button" wire:click="selectTab('entry')"
                                    class="min-w-36 whitespace-nowrap md:flex-1 {{ $activeTab === 'entry' ? 'rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-800 shadow-sm' : 'rounded-xl border border-transparent bg-white px-4 py-2 text-sm font-semibold text-slate-600 hover:border-emerald-100 hover:bg-emerald-50/60 hover:text-emerald-800' }}">
                                    {{ __('Entrada manual') }}
                                </button>
                            @endif
                            @if (in_array('exit', $availableTabs, true))
                                <button type="button" wire:click="selectTab('exit')"
                                    class="min-w-36 whitespace-nowrap md:flex-1 {{ $activeTab === 'exit' ? 'rounded-xl border border-amber-200 bg-amber-50 px-4 py-2 text-sm font-semibold text-amber-800 shadow-sm' : 'rounded-xl border border-transparent bg-white px-4 py-2 text-sm font-semibold text-slate-600 hover:border-amber-100 hover:bg-amber-50/60 hover:text-amber-800' }}">
                                    {{ __('Saída manual') }}
                                </button>
                            @endif
                            @if (in_array('adjustment', $availableTabs, true))
                                <button type="button" wire:click="selectTab('adjustment')"
                                    class="min-w-36 whitespace-nowrap md:flex-1 {{ $activeTab === 'adjustment' ? 'rounded-xl border border-stone-200 bg-stone-50 px-4 py-2 text-sm font-semibold text-stone-700 shadow-sm' : 'rounded-xl border border-transparent bg-white px-4 py-2 text-sm font-semibold text-slate-600 hover:border-stone-100 hover:bg-stone-50/70 hover:text-stone-700' }}">
                                    {{ __('Ajuste') }}
                                </button>
                            @endif
                            @if (in_array('loss', $availableTabs, true))
                                <button type="button" wire:click="selectTab('loss')"
                                    class="min-w-36 whitespace-nowrap md:flex-1 {{ $activeTab === 'loss' ? 'rounded-xl border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-800 shadow-sm' : 'rounded-xl border border-transparent bg-white px-4 py-2 text-sm font-semibold text-slate-600 hover:border-rose-100 hover:bg-rose-50/60 hover:text-rose-800' }}">
                                    {{ __('Perda') }}
                                </button>
                            @endif
                            @if (in_array('composition', $availableTabs, true))
                                <button type="button" wire:click="selectTab('composition')"
                                    class="min-w-36 whitespace-nowrap md:flex-1 {{ $activeTab === 'composition' ? 'rounded-xl border border-amber-200 bg-amber-50 px-4 py-2 text-sm font-semibold text-amber-800 shadow-sm' : 'rounded-xl border border-transparent bg-white px-4 py-2 text-sm font-semibold text-slate-600 hover:border-amber-100 hover:bg-amber-50/60 hover:text-amber-800' }}">
                                    {{ __('Composição') }}
                                </button>
                            @endif
                        </div>
                    </div>
                @endif

                @if ($activeTab === 'composition')
                    <section class="space-y-4">
                        <div>
                            <h4 class="text-base font-semibold text-sky-950">{{ __('Itens do produto composto') }}</h4>
                            <p class="text-sm text-slate-600">
                                {{ __('Confira abaixo os itens simples que compõem este produto e o saldo atual disponível no estoque delegado.') }}
                            </p>
                        </div>

                        <div class="overflow-x-auto rounded-xl border border-amber-200 bg-white">
                            <table class="w-full text-left text-sm">
                                <thead class="bg-amber-100 text-xs uppercase text-amber-900">
                                    <tr>
                                        <th class="px-4 py-3">{{ __('Item') }}</th>
                                        <th class="w-40 px-4 py-3 text-center">{{ __('Qtd. no composto') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($compositionItems as $component)
                                        <tr class="border-t border-amber-100 odd:bg-white even:bg-amber-50/40">
                                            <td class="px-4 py-3 font-medium text-slate-900">
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <span>{{ $component['name'] }}</span>
                                                    @if (!$component['is_active'])
                                                        <span
                                                            class="rounded-full bg-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-700">
                                                            {{ __('Inativo') }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 text-center font-semibold text-slate-700">
                                                {{ $component['quantity'] }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="px-4 py-5 text-center text-sm text-slate-500">
                                                {{ __('Este produto composto ainda não possui itens vinculados.') }}
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </section>
                @elseif ($activeTab === 'entry')
                    <section class="space-y-5">
                        <div>
                            <h4 class="text-base font-semibold text-sky-950">{{ __('Entrada manual do produto') }}</h4>
                            <p class="text-sm text-slate-600">
                                {{ __('Informe quantas unidades devem entrar neste estoque. A quantidade informada será somada ao saldo atual do produto.') }}
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-x-4 gap-y-8">
                            <x-src.form.input name="teacher-material-entry-quantity-{{ $material->id }}"
                                wire:model.live="entry_quantity" label="Quantidade de entrada" type="number"
                                width_basic="180" min="1" required />
                            <x-src.form.textarea name="teacher-material-entry-notes-{{ $material->id }}"
                                wire:model.live="entry_notes" label="Observação" rows="4" />
                        </div>
                    </section>
                @elseif ($activeTab === 'exit')
                    <section class="space-y-5">
                        <div>
                            <h4 class="text-base font-semibold text-sky-950">{{ __('Saída manual do produto') }}</h4>
                            <p class="text-sm text-slate-600">
                                {{ __('Informe quantas unidades devem sair deste estoque. A quantidade informada será subtraída do saldo atual do produto. Produtos compostos também baixam seus componentes automaticamente.') }}
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-x-4 gap-y-8">
                            <x-src.form.input name="teacher-material-exit-quantity-{{ $material->id }}"
                                wire:model.live="exit_quantity" label="Quantidade de saída" type="number"
                                width_basic="180" min="1" autofocus required />
                            <x-src.form.textarea name="teacher-material-exit-notes-{{ $material->id }}"
                                wire:model.live="exit_notes" label="Observação" rows="4" />
                        </div>
                    </section>
                @elseif ($activeTab === 'adjustment')
                    <section class="space-y-5">
                        <div>
                            <h4 class="text-base font-semibold text-sky-950">{{ __('Ajuste de saldo do produto') }}
                            </h4>
                            <p class="text-sm text-slate-600">
                                {{ __('Defina o saldo final consolidado deste produto no estoque atual.') }}
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-x-4 gap-y-8">
                            <x-src.form.input name="teacher-material-adjustment-target-quantity-{{ $material->id }}"
                                wire:model.live="adjustment_target_quantity" label="Saldo alvo" type="number"
                                width_basic="180" min="0" required />
                            <x-src.form.textarea name="teacher-material-adjustment-notes-{{ $material->id }}"
                                wire:model.live="adjustment_notes" label="Observação" rows="4" />
                        </div>
                    </section>
                @else
                    <section class="space-y-5">
                        <div>
                            <h4 class="text-base font-semibold text-sky-950">{{ __('Registrar perda ou avaria') }}</h4>
                            <p class="text-sm text-slate-600">
                                {{ __('Use esta guia para registrar perdas, danos ou baixas não recuperáveis deste produto.') }}
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-x-4 gap-y-8">
                            <x-src.form.input name="teacher-material-loss-quantity-{{ $material->id }}"
                                wire:model.live="loss_quantity" label="Quantidade perdida" type="number"
                                width_basic="180" min="1" required />
                            <x-src.form.textarea name="teacher-material-loss-notes-{{ $material->id }}"
                                wire:model.live="loss_notes" label="Observação" rows="4" />
                        </div>
                    </section>
                @endif
            </div>

            <footer class="sticky bottom-0 z-20 border-t border-sky-800 bg-sky-950 px-6 py-4 text-sky-50">
                <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <div class="text-sm text-sky-100/80">
                        {{ __('Ao confirmar, esta movimentação será registrada imediatamente no estoque e no histórico auditável.') }}
                    </div>

                    <div class="flex justify-between gap-3 md:justify-end">
                        <x-src.btn-silver type="button" wire:click="closeModal" wire:loading.attr="disabled"
                            wire:target="saveEntry,saveExit,saveAdjustment,saveLoss">
                            {{ __('Fechar') }}
                        </x-src.btn-silver>
                        @if ($activeTab === 'entry')
                            <x-src.btn-gold type="button" wire:click="saveEntry" wire:loading.attr="disabled"
                                wire:target="saveEntry">
                                {{ __('Registrar entrada agora') }}
                            </x-src.btn-gold>
                        @elseif ($activeTab === 'exit')
                            <x-src.btn-gold type="button" wire:click="saveExit" wire:loading.attr="disabled"
                                wire:target="saveExit">
                                {{ __('Registrar saída agora') }}
                            </x-src.btn-gold>
                        @elseif ($activeTab === 'adjustment')
                            <x-src.btn-gold type="button" wire:click="saveAdjustment" wire:loading.attr="disabled"
                                wire:target="saveAdjustment">
                                {{ __('Aplicar ajuste agora') }}
                            </x-src.btn-gold>
                        @else
                            <x-src.btn-gold type="button" wire:click="saveLoss" wire:loading.attr="disabled"
                                wire:target="saveLoss">
                                {{ __('Registrar perda agora') }}
                            </x-src.btn-gold>
                        @endif
                    </div>
                </div>
            </footer>
        </div>
    </flux:modal>
</div>
