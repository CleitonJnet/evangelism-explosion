@php
    use App\Services\Training\TestimonySanitizer;

    $training->loadMissing(['course.ministry', 'church']);

    $characterLimit = TestimonySanitizer::MAX_CHARACTERS;
    $initialNotes = TestimonySanitizer::sanitize((string) old('notes', $training->notes ?? '')) ?? '';
    $eventTitle = trim(implode(' ', array_filter([$training->course?->type, $training->course?->name])));
    $ministryName = $training->course?->ministry?->name ?: __('Ministério não informado');
    $baseChurchName = $training->church?->name ?: __('Igreja base não informada');
@endphp

<x-layouts.app :title="__('Relato do Evento')">
    <x-src.toolbar.header :title="__('Relato do Evento')" :description="__('Registre testemunhos e aprendizados para inspirar novos professores e apoiar intercessão.')">
        <x-slot:right>
            <div class="hidden px-1 py-2 text-right md:block">
                <div class="text-sm font-bold text-slate-800">
                    {{ $eventTitle !== '' ? $eventTitle : __('Evento sem nome') }}
                </div>
                <div class="text-xs font-light text-slate-600">
                    {{ $ministryName }}
                </div>
                <div class="text-xs font-light text-slate-500">
                    {{ $baseChurchName }}
                </div>
            </div>
        </x-slot:right>
    </x-src.toolbar.header>

    <x-src.toolbar.nav justify="justify-between">
        <div class="flex flex-wrap items-center gap-2">
            <x-src.toolbar.button :href="route('app.teacher.trainings.show', $training)" :label="__('Detalhes do Evento')" icon="eye" :tooltip="__('Voltar para o treinamento')"
                class="!bg-sky-900 !text-slate-100 !border-sky-700 hover:!bg-sky-800" />
        </div>
    </x-src.toolbar.nav>

    <section class="rounded-2xl">
        <form method="POST" action="{{ route('app.teacher.trainings.testimony.update', $training) }}"
            id="training-testimony-form">
            @csrf
            @method('PUT')

            <input type="hidden" name="notes" id="testimony-notes" value="{{ $initialNotes }}">

            <div class="rounded-b-xl border border-slate-200 bg-white" data-testimony-editor-root data-autofocus="true">
                <div class="sticky top-16 z-30 border-b border-slate-200 bg-slate-50/95 backdrop-blur-xs">
                    <div class="overflow-x-auto p-3">
                        <div class="flex min-w-max flex-wrap items-center gap-0.5 sm:gap-1 md:gap-2">
                            <select class="rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700"
                                data-tiptap-select="heading">
                                <option value="paragraph">{{ __('Parágrafo') }}</option>
                                <option value="h2">{{ __('Título') }}</option>
                                <option value="h3">{{ __('Subtítulo') }}</option>
                            </select>

                            <select class="rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700"
                                data-tiptap-select="font-family">
                                <option value="inherit">{{ __('Fonte padrão') }}</option>
                                <option value="Georgia, serif">Georgia</option>
                                <option value="'Trebuchet MS', sans-serif">Trebuchet</option>
                                <option value="'Courier New', monospace">Courier</option>
                            </select>

                            <select class="rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700"
                                data-tiptap-select="font-size">
                                <option value="14px">{{ __('14px') }}</option>
                                <option value="16px" selected>{{ __('16px') }}</option>
                                <option value="18px">{{ __('18px') }}</option>
                                <option value="20px">{{ __('20px') }}</option>
                                <option value="24px">{{ __('24px') }}</option>
                            </select>

                            <label
                                class="inline-flex items-center gap-2 rounded-md border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-600">
                                {{ __('Cor') }}
                                <input type="color" value="#0f172a" data-tiptap-input="color" />
                            </label>

                            <div class="h-7 w-px bg-slate-300"></div>

                            <button type="button" class="tiptap-toolbar-button" data-tiptap-action="bold"
                                aria-label="{{ __('Negrito') }}" title="{{ __('Negrito') }}">
                                <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <path d="M7 5h6a4 4 0 0 1 0 8H7V5Zm0 10h7a3 3 0 1 1 0 6H7v-6Z" />
                                </svg>
                            </button>
                            <button type="button" class="tiptap-toolbar-button" data-tiptap-action="italic"
                                aria-label="{{ __('Itálico') }}" title="{{ __('Itálico') }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    aria-hidden="true">
                                    <path d="M14 4h6M4 20h6M13 4 9 20" />
                                </svg>
                            </button>
                            <button type="button" class="tiptap-toolbar-button" data-tiptap-action="underline"
                                aria-label="{{ __('Sublinhado') }}" title="{{ __('Sublinhado') }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    aria-hidden="true">
                                    <path d="M6 4v6a6 6 0 0 0 12 0V4M4 20h16" />
                                </svg>
                            </button>
                            <button type="button" class="tiptap-toolbar-button" data-tiptap-action="strike"
                                aria-label="{{ __('Riscado') }}" title="{{ __('Riscado') }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    aria-hidden="true">
                                    <path
                                        d="M4 12h16M8 7.5A3.5 3.5 0 0 1 11.5 4h1A3.5 3.5 0 0 1 16 7.5M8 16.5a3.5 3.5 0 0 0 3.5 3.5h1a3.5 3.5 0 0 0 3.5-3.5" />
                                </svg>
                            </button>

                            <div class="h-7 w-px bg-slate-300"></div>

                            <button type="button" class="tiptap-toolbar-button" data-tiptap-action="bullet-list"
                                aria-label="{{ __('Lista') }}" title="{{ __('Lista') }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    aria-hidden="true">
                                    <path d="M8 6h12M8 12h12M8 18h12" />
                                    <circle cx="4" cy="6" r="1.2" fill="currentColor" stroke="none" />
                                    <circle cx="4" cy="12" r="1.2" fill="currentColor"
                                        stroke="none" />
                                    <circle cx="4" cy="18" r="1.2" fill="currentColor"
                                        stroke="none" />
                                </svg>
                            </button>
                            <button type="button" class="tiptap-toolbar-button" data-tiptap-action="ordered-list"
                                aria-label="{{ __('Numerada') }}" title="{{ __('Numerada') }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    aria-hidden="true">
                                    <path d="M10 6h10M10 12h10M10 18h10M4 6h1v2M3.5 12h2L4 14h1.5M3.5 18h2L4 20h1.5" />
                                </svg>
                            </button>
                            <button type="button" class="tiptap-toolbar-button" data-tiptap-action="blockquote"
                                aria-label="{{ __('Citação') }}" title="{{ __('Citação') }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    aria-hidden="true">
                                    <path d="M9 7H5v6h4V7Zm10 0h-4v6h4V7ZM7 13c0 2-1 3-3 4m13-4c0 2-1 3-3 4" />
                                </svg>
                            </button>

                            <div class="h-7 w-px bg-slate-300"></div>

                            <button type="button" class="tiptap-toolbar-button" data-tiptap-action="align-left"
                                aria-label="{{ __('Alinhar à esquerda') }}" title="{{ __('Alinhar à esquerda') }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    aria-hidden="true">
                                    <path d="M4 6h16M4 10h10M4 14h16M4 18h10" />
                                </svg>
                            </button>
                            <button type="button" class="tiptap-toolbar-button" data-tiptap-action="align-center"
                                aria-label="{{ __('Centralizar') }}" title="{{ __('Centralizar') }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    aria-hidden="true">
                                    <path d="M4 6h16M7 10h10M4 14h16M7 18h10" />
                                </svg>
                            </button>
                            <button type="button" class="tiptap-toolbar-button" data-tiptap-action="align-right"
                                aria-label="{{ __('Alinhar à direita') }}" title="{{ __('Alinhar à direita') }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    aria-hidden="true">
                                    <path d="M4 6h16M10 10h10M4 14h16M10 18h10" />
                                </svg>
                            </button>
                            <button type="button" class="tiptap-toolbar-button" data-tiptap-action="align-justify"
                                aria-label="{{ __('Justificar') }}" title="{{ __('Justificar') }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    aria-hidden="true">
                                    <path d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                                </svg>
                            </button>

                            <div class="h-7 w-px bg-slate-300"></div>

                            <button type="button" class="tiptap-toolbar-button" data-tiptap-action="undo"
                                aria-label="{{ __('Desfazer') }}" title="{{ __('Desfazer') }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    aria-hidden="true">
                                    <path d="M9 9H5V5M5 9a8 8 0 1 1-1.7 8.8" />
                                </svg>
                            </button>
                            <button type="button" class="tiptap-toolbar-button" data-tiptap-action="redo"
                                aria-label="{{ __('Refazer') }}" title="{{ __('Refazer') }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    aria-hidden="true">
                                    <path d="M15 9h4V5m0 4a8 8 0 1 0 1.7 8.8" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="tiptap-editor-shell">
                    <div class="tiptap-editor-surface" data-tiptap-surface="editor" role="textbox"
                        aria-label="{{ __('Relato do professor') }}"></div>
                </div>
            </div>

            <div
                class="flex items-center justify-between text-xs text-slate-600 bg-slate-200 px-6 py-2 rounded-md mt-2 mb-10">
                <div>
                    {{ __('Escreva um relato do que o Senhor fez no evento, com aprendizados e frutos para inspirar novos professores.') }}
                </div>
                <div id="testimony-counter" data-limit="{{ $characterLimit }}" class="font-semibold">0 /
                    {{ $characterLimit }}</div>
            </div>

            @error('notes')
                <p class="text-sm text-red-600">{{ $message }}</p>
            @enderror

            <div class="fixed bottom-2.5 right-5">
                <x-src.btn-gold type="submit" :label="__('SALVAR RELATO')" />
            </div>

        </form>
    </section>

    @push('css')
        <link rel="preconnect" href="https://esm.sh" crossorigin>
        <script type="module">
            window.__trainingTestimonyTiptap = Promise.all([
                import('https://esm.sh/@tiptap/core'),
                import('https://esm.sh/@tiptap/starter-kit'),
                import('https://esm.sh/@tiptap/extension-underline'),
                import('https://esm.sh/@tiptap/extension-text-style'),
                import('https://esm.sh/@tiptap/extension-color'),
                import('https://esm.sh/@tiptap/extension-text-align'),
            ]).then(([core, starterKit, underline, textStyle, color, textAlign]) => ({
                ...core,
                StarterKit: starterKit.default,
                Underline: underline.default,
                TextStyle: textStyle.default,
                Color: color.default,
                TextAlign: textAlign.default,
            }));
        </script>
    @endpush

</x-layouts.app>
