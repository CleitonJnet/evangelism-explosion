@php
    use Illuminate\Support\Facades\Storage;

    $themeColor = trim((string) ($course->color ?: '#4F4F4F'));

    $hexToRgb = static function (string $hex): array {
        $normalized = ltrim($hex, '#');

        if (strlen($normalized) === 3) {
            $normalized = collect(str_split($normalized))->map(fn(string $char): string => $char . $char)->implode('');
        }

        if (!preg_match('/^[A-Fa-f0-9]{6}$/', $normalized)) {
            $normalized = '4F4F4F';
        }

        return [
            hexdec(substr($normalized, 0, 2)),
            hexdec(substr($normalized, 2, 2)),
            hexdec(substr($normalized, 4, 2)),
        ];
    };

    $resolveBannerUrl = static function (?string $asset): ?string {
        $assetValue = trim((string) $asset);

        if ($assetValue === '') {
            return null;
        }

        if (str_starts_with($assetValue, 'http')) {
            return $assetValue;
        }

        $normalizedAsset = ltrim($assetValue, '/');

        return Storage::disk('public')->exists($normalizedAsset)
            ? Storage::disk('public')->url($normalizedAsset)
            : null;
    };

    [$red, $green, $blue] = $hexToRgb($themeColor);
    $tableThemeStyles = sprintf(
        '--course-head-start: rgba(%d, %d, %d, 0.14); --course-head-end: rgba(%d, %d, %d, 0.24); --course-row-odd: rgba(%d, %d, %d, 0.05); --course-row-even: rgba(%d, %d, %d, 0.10); --course-row-hover: rgba(%d, %d, %d, 0.18); --course-accent: %s;',
        $red,
        $green,
        $blue,
        $red,
        $green,
        $blue,
        $red,
        $green,
        $blue,
        $red,
        $green,
        $blue,
        $red,
        $green,
        $blue,
        $themeColor,
    );

    $eventTitle = trim(implode(' ', array_filter([$course->type, $course->name])));
    $ministryName = $course->ministry?->name ?: __('Ministério não informado');
@endphp

<div>
    <x-src.toolbar.header :title="__('Unidades do curso')" :description="__('Organize a sequência e os detalhes das unidades do curso selecionado.')" justify="justify-between"
        fixedRouteName="app.director.ministry.course.sections">
        <x-slot:right>
            <div class="hidden px-1 py-2 text-right md:block">
                <div class="text-sm font-bold text-slate-800">
                    {{ $eventTitle !== '' ? $eventTitle : __('Curso sem nome') }}
                </div>
                <div class="text-xs font-light text-slate-600">
                    {{ $ministryName }}
                </div>
            </div>
        </x-slot:right>
    </x-src.toolbar.header>

    <x-src.toolbar.nav :title="__('Unidades do curso')" :description="__('Organize a sequência e os detalhes das unidades do curso selecionado.')" justify="justify-between">
        <div class="flex flex-wrap items-center gap-2">
            <x-src.toolbar.button :href="route('app.director.ministry.course.show', [
                'ministry' => $course->ministry_id,
                'course' => $course->id,
            ])" :label="__('Detalhes do curso')" icon="eye" :tooltip="__('Voltar para os detalhes do curso')"
                class="!bg-sky-900 !text-slate-100 !border-sky-700 hover:!bg-sky-800" />
            <x-src.toolbar.button :href="'#'" :label="__('Adicionar unidade')" icon="plus" :tooltip="__('Cadastrar nova unidade')"
                onclick="window.Livewire.find('{{ $this->getId() }}').openCreateSectionModal(); return false;" />
        </div>
    </x-src.toolbar.nav>

    <section class="rounded-2xl border border-slate-200 bg-white/95 p-6 shadow-sm">
        <div class="flex items-center justify-between gap-4 border-b border-slate-200 pb-4">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">{{ __('Tabela de unidades') }}</h2>
                <p class="text-sm text-slate-600">
                    {{ __('Arraste pelo primeiro bloco da linha para reordenar. Clique na linha para editar a unidade.') }}
                </p>
            </div>
        </div>

        <div class="mt-6 overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm"
            style="{{ $tableThemeStyles }}">
            <table class="w-full text-left text-sm">
                <colgroup>
                    <col class="w-24">
                    <col class="w-24">
                    <col>
                    <col class="w-36">
                    <col class="w-24">
                </colgroup>
                <thead class="text-xs uppercase text-slate-700"
                    style="background-image: linear-gradient(to bottom, var(--course-head-start), var(--course-head-end));">
                    <tr class="border-b border-slate-200">
                        <th class="px-3 py-3 text-center">{{ __('Ordem') }}</th>
                        <th class="px-3 py-3 text-center">{{ __('Banner') }}</th>
                        <th class="px-3 py-3">{{ __('Unidade') }}</th>
                        <th class="px-3 py-3 text-center">{{ __('Duração') }}</th>
                        <th class="px-3 py-3 text-center">{{ __('Ações') }}</th>
                    </tr>
                </thead>
                <tbody class="js-course-sections-list divide-y divide-slate-200">
                    @forelse ($sections as $section)
                        @php
                            $bannerUrl = $resolveBannerUrl($section->banner) ?: asset('images/cover.webp');
                            $rowBackground = $loop->odd ? 'var(--course-row-odd)' : 'var(--course-row-even)';
                        @endphp
                        <tr wire:key="course-section-{{ $section->id }}" data-item-id="{{ $section->id }}"
                            class="js-course-section-item cursor-pointer text-slate-800 transition-colors hover:[background-color:var(--course-row-hover)]"
                            style="background-color: {{ $rowBackground }};"
                            x-on:click="if (! $event.target.closest('.js-section-delete-button')) { $wire.openEditSectionModal({{ $section->id }}) }">
                            <td class="js-section-drag-handle cursor-grab px-3 py-3 active:cursor-grabbing">
                                <div class="flex items-center justify-center gap-2 font-semibold">
                                    <span class="text-slate-400">::</span>
                                    <span>{{ $section->order ?? '-' }}</span>
                                </div>
                            </td>
                            <td class="px-3 py-3">
                                <div
                                    class="mx-auto h-12 w-12 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                                    <img src="{{ $bannerUrl }}" alt="{{ __('Banner da unidade') }}"
                                        class="h-full w-full object-cover">
                                </div>
                            </td>
                            <td class="px-3 py-3">
                                <div class="font-semibold text-slate-900">{{ $section->name }}</div>
                                @if ($section->devotional)
                                    <div class="mt-1 text-xs font-medium text-slate-600">
                                        {{ $section->devotional }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-3 py-3 text-center text-slate-700">{{ $section->duration ?? '-' }}</td>
                            <td class="px-3 py-3">
                                <div class="flex items-center justify-center whitespace-nowrap">
                                    <flux:button class="js-section-delete-button shrink-0 px-2" size="sm"
                                        :square="false" variant="danger" icon="trash" icon:variant="outline"
                                        aria-label="{{ __('Remover') }}"
                                        wire:click="openDeleteSectionModal({{ $section->id }})" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="px-3 py-4 text-sm text-slate-600" colspan="5">
                                {{ __('Nenhuma unidade cadastrada para este curso.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <flux:modal name="section-modal" wire:model="showSectionModal" class="max-w-4xl w-full bg-sky-950! p-0!">
        <form wire:submit="saveSection">
            <div class="flex max-h-[90vh] flex-col overflow-hidden rounded-2xl">
                <header class="sticky top-0 z-20 border-b border-sky-800 bg-sky-950 px-6 py-4 text-sky-50">
                    <h3 class="text-lg font-semibold">
                        {{ $editingSectionId ? __('Editar unidade') : __('Adicionar unidade') }}
                    </h3>
                    <p class="text-sm opacity-90">
                        {{ __('Atualize os dados pedagógicos e a apresentação visual da unidade selecionada.') }}
                    </p>
                </header>

                <div class="min-h-0 flex-1 overflow-y-auto bg-white px-6 py-6">
                    <div class="space-y-8">
                        <section class="space-y-5">
                            <div>
                                <h4 class="text-base font-semibold text-sky-950">{{ __('Identificação da unidade') }}
                                </h4>
                                <p class="text-sm text-slate-600">
                                    {{ __('Dados principais para exibição e ordenação da unidade no curso.') }}</p>
                            </div>

                            <div class="grid gap-6 lg:grid-cols-12">
                                <div class="lg:col-span-4">
                                    <div
                                        class="grid justify-items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 p-4">
                                        <input id="director-course-section-banner-upload" type="file"
                                            accept="image/*" wire:model.live="bannerUpload" class="sr-only">

                                        <label for="director-course-section-banner-upload"
                                            class="cursor-pointer overflow-hidden rounded-xl border border-slate-300 bg-slate-100 p-1">
                                            <div class="h-32 w-32 overflow-hidden rounded-lg">
                                                <img src="{{ $this->sectionBannerPreviewUrl() }}"
                                                    alt="{{ __('Banner da unidade') }}"
                                                    class="h-full w-full object-cover">
                                            </div>
                                        </label>

                                        <p class="text-center text-xs text-slate-600">
                                            {{ __('Clique na imagem para enviar o banner quadrado da unidade.') }}
                                        </p>

                                        @error('bannerUpload')
                                            <p class="text-xs font-semibold text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <div class="space-y-6 lg:col-span-8">
                                    <div class="flex flex-wrap gap-x-4 gap-y-8">
                                        <x-src.form.input name="sectionForm.name" wire:model.live="sectionForm.name"
                                            label="Nome da unidade" type="text" width_basic="460" required />
                                        <x-src.form.input name="sectionForm.devotional"
                                            wire:model.live="sectionForm.devotional" label="Título da devocional"
                                            type="text" width_basic="420" />
                                        <div class="space-y-2" style="flex: 0 0 250px">
                                            <label for="sectionForm.duration"
                                                class="text-sm text-body duration-300 origin-[0]">
                                                {{ __('Duração') }}
                                            </label>
                                            <div x-data="{
                                                step(delta) {
                                                    const input = this.$refs.durationInput;
                                                    let value = parseInt(input.value, 10);

                                                    if (Number.isNaN(value)) {
                                                        value = {{ (int) ($sectionForm['duration'] ?? 0) }};
                                                    }

                                                    value = Math.max(0, value + delta);
                                                    value = Math.round(value / 5) * 5;
                                                    value = Math.max(0, value);
                                                    input.value = String(value);
                                                    input.dispatchEvent(new Event('input', { bubbles: true }));
                                                }
                                            }"
                                                class="flex items-center gap-2">
                                                <div
                                                    class="flex items-center rounded-md border border-(--ee-app-border) bg-white/60">
                                                    <button type="button"
                                                        class="inline-flex h-8 w-8 items-center justify-center text-slate-600 transition hover:bg-slate-100 disabled:opacity-50"
                                                        x-on:click="step(-5)"
                                                        aria-label="{{ __('Subtrair 5 minutos') }}">
                                                        <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                                            viewBox="0 0 20 20" class="h-3 w-3 fill-current">
                                                            <path d="M10 14 4 6h12l-6 8Z" />
                                                        </svg>
                                                    </button>
                                                    <input id="sectionForm.duration" type="text" inputmode="numeric"
                                                        pattern="[0-9]*" x-ref="durationInput"
                                                        x-on:input="
                                                            $el.value = $el.value.replace(/[^0-9]/g, '');
                                                            if ($el.value === '') { return; }
                                                            const value = parseInt($el.value, 10);
                                                            if (!Number.isNaN(value)) {
                                                                let roundedValue = Math.round(value / 5) * 5;
                                                                roundedValue = Math.max(0, roundedValue);
                                                                $el.value = String(roundedValue);
                                                            }
                                                        "
                                                        class="w-14 border-x border-(--ee-app-border) py-1 text-center text-sm bg-white focus-within:bg-white"
                                                        wire:model.live.debounce.300ms="sectionForm.duration"
                                                        wire:loading.attr="disabled" wire:target="sectionForm.duration" />
                                                    <button type="button"
                                                        class="inline-flex h-8 w-8 items-center justify-center text-slate-600 transition hover:bg-slate-100 disabled:opacity-50"
                                                        x-on:click="step(5)"
                                                        aria-label="{{ __('Adicionar 5 minutos') }}">
                                                        <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                                            viewBox="0 0 20 20" class="h-3 w-3 fill-current">
                                                            <path d="M10 6 4 14h12L10 6Z" />
                                                        </svg>
                                                    </button>
                                                </div>
                                                <span class="text-xs text-(--ee-app-muted)">{{ __('minutos') }}</span>
                                            </div>
                                            @error('sectionForm.duration')
                                                <p class="text-xs font-semibold text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section class="space-y-5">
                            <div>
                                <h4 class="text-base font-semibold text-sky-950">{{ __('Conteúdo da unidade') }}</h4>
                                <p class="text-sm text-slate-600">
                                    {{ __('Registre a devocional e os textos de apoio usados nesta etapa do curso.') }}
                                </p>
                            </div>

                            <div class="flex flex-wrap gap-x-4 gap-y-8">
                                <x-src.form.textarea name="sectionForm.description"
                                    wire:model.live="sectionForm.description" label="Descrição" rows="4" />
                                <x-src.form.textarea name="sectionForm.knowhow" wire:model.live="sectionForm.knowhow"
                                    label="Conhecimento" rows="4" />
                            </div>
                        </section>
                    </div>
                </div>

                <footer class="sticky bottom-0 z-20 border-t border-sky-800 bg-sky-950 px-6 py-4 text-sky-50">
                    <div class="flex justify-between gap-3">
                        <x-src.btn-silver type="button" wire:click="closeSectionModal" wire:loading.attr="disabled"
                            wire:target="saveSection">
                            {{ __('Cancelar') }}
                        </x-src.btn-silver>
                        <x-src.btn-gold type="submit" wire:loading.attr="disabled" wire:target="saveSection">
                            {{ $editingSectionId ? __('Salvar alterações') : __('Salvar') }}
                        </x-src.btn-gold>
                    </div>
                </footer>
            </div>
        </form>
    </flux:modal>

    <flux:modal name="delete-section-modal" wire:model="showDeleteSectionModal" class="max-w-md">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Remover unidade') }}</flux:heading>
                <flux:subheading>
                    {{ __('Esta ação é permanente. Deseja continuar?') }}
                </flux:subheading>
            </div>

            <div class="flex flex-wrap justify-end gap-2">
                <flux:button variant="ghost" wire:click="closeDeleteSectionModal">
                    {{ __('Cancelar') }}
                </flux:button>
                <flux:button variant="danger" wire:click="confirmDeleteSection">
                    {{ __('Remover') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
