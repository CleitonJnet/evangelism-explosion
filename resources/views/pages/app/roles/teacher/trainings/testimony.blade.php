@php
    use App\Services\Training\TestimonySanitizer;

    $training->loadMissing(['course.ministry', 'church']);

    $characterLimit = TestimonySanitizer::MAX_CHARACTERS;
    $initialNotes = TestimonySanitizer::sanitize((string) old('notes', $training->notes ?? '')) ?? '';
    $eventTitle = trim(
        implode(' ', array_filter([
            $training->course?->type,
            $training->course?->name,
        ])),
    );
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
            <x-src.toolbar.button :href="route('app.teacher.trainings.show', $training)" :label="__('Detalhes do Evento')" icon="eye" :tooltip="__('Voltar para o treinamento')" />
        </div>
    </x-src.toolbar.nav>

    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        @if (session('status'))
            <div class="mb-4 rounded-lg border border-emerald-300 bg-emerald-50 px-4 py-2 text-sm text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('app.teacher.trainings.testimony.update', $training) }}" id="training-testimony-form"
            class="space-y-4">
            @csrf
            @method('PUT')

            <input type="hidden" name="notes" id="testimony-notes" value="{{ $initialNotes }}">

            <div class="rounded-xl border border-slate-200">
                <div class="overflow-x-auto border-b border-slate-200 bg-slate-50 p-3">
                    <div class="flex min-w-max items-center gap-2">
                        <div class="flex h-16 shrink-0 items-center gap-2 rounded-lg border border-slate-300 bg-white px-2">
                        <select class="rounded-md border border-slate-300 px-2 py-1 text-sm" data-format-block>
                            <option value="p">{{ __('Parágrafo') }}</option>
                            <option value="h2">{{ __('Título') }}</option>
                            <option value="h3">{{ __('Subtítulo') }}</option>
                        </select>

                        <select class="rounded-md border border-slate-300 px-2 py-1 text-sm" data-font-family>
                            <option value="inherit">{{ __('Fonte') }}</option>
                            <option value="Georgia, serif">Georgia</option>
                            <option value="'Trebuchet MS', sans-serif">Trebuchet</option>
                            <option value="'Courier New', monospace">Courier</option>
                        </select>
                        <select class="rounded-md border border-slate-300 px-2 py-1 text-sm" data-font-size>
                            <option value="14px">{{ __('14px') }}</option>
                            <option value="16px" selected>{{ __('16px') }}</option>
                            <option value="18px">{{ __('18px') }}</option>
                            <option value="20px">{{ __('20px') }}</option>
                        </select>
                        <label class="inline-flex items-center gap-1 rounded-md border border-slate-300 bg-white px-2 py-1 text-sm">
                            {{ __('Cor') }}
                            <input type="color" value="#0f172a" data-color />
                        </label>
                    </div>

                        <button type="button" class="inline-flex h-16 w-20 shrink-0 flex-col items-center justify-center gap-0.5 rounded-md border border-slate-300 px-2 py-1 text-[11px] leading-tight text-center font-semibold"
                            data-command="bold">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 5h6a4 4 0 010 8H7zM7 13h7a3 3 0 010 6H7z" />
                        </svg>
                        <span>{{ __('Negrito') }}</span>
                    </button>
                        <button type="button" class="inline-flex h-16 w-20 shrink-0 flex-col items-center justify-center gap-0.5 rounded-md border border-slate-300 px-2 py-1 text-[11px] leading-tight text-center italic"
                            data-command="italic">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 4h8M3 20h8M14 4L10 20" />
                        </svg>
                        <span>{{ __('Itálico') }}</span>
                    </button>
                        <button type="button" class="inline-flex h-16 w-20 shrink-0 flex-col items-center justify-center gap-0.5 rounded-md border border-slate-300 px-2 py-1 text-[11px] leading-tight text-center underline"
                            data-command="underline">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 4v6a6 6 0 0012 0V4M4 20h16" />
                        </svg>
                        <span>{{ __('Sublinhado') }}</span>
                    </button>
                        <button type="button" class="inline-flex h-16 w-20 shrink-0 flex-col items-center justify-center gap-0.5 rounded-md border border-slate-300 px-2 py-1 text-[11px] leading-tight text-center line-through"
                            data-command="strikeThrough">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 12h16M7 7.5A3.5 3.5 0 0110.5 4h3A3.5 3.5 0 0117 7.5M7 16.5A3.5 3.5 0 0010.5 20h3a3.5 3.5 0 003.5-3.5" />
                        </svg>
                        <span>{{ __('Riscar') }}</span>
                    </button>
                        <button type="button" class="inline-flex h-16 w-20 shrink-0 flex-col items-center justify-center gap-0.5 rounded-md border border-slate-300 px-2 py-1 text-[11px] leading-tight text-center"
                            data-command="justifyLeft">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 10h10M4 14h16M4 18h10" />
                        </svg>
                        <span>{{ __('Esq') }}</span>
                    </button>
                        <button type="button" class="inline-flex h-16 w-20 shrink-0 flex-col items-center justify-center gap-0.5 rounded-md border border-slate-300 px-2 py-1 text-[11px] leading-tight text-center"
                            data-command="justifyCenter">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M7 10h10M4 14h16M7 18h10" />
                        </svg>
                        <span>{{ __('Centro') }}</span>
                    </button>
                        <button type="button" class="inline-flex h-16 w-20 shrink-0 flex-col items-center justify-center gap-0.5 rounded-md border border-slate-300 px-2 py-1 text-[11px] leading-tight text-center"
                            data-command="justifyRight">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M10 10h10M4 14h16M10 18h10" />
                        </svg>
                        <span>{{ __('Dir') }}</span>
                    </button>
                        <button type="button" class="inline-flex h-16 w-20 shrink-0 flex-col items-center justify-center gap-0.5 rounded-md border border-slate-300 px-2 py-1 text-[11px] leading-tight text-center"
                            data-command="justifyFull">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                        </svg>
                        <span>{{ __('Just') }}</span>
                    </button>
                    </div>
                </div>

                <div id="testimony-editor" contenteditable="true" class="min-h-80 w-full p-4 text-slate-800 focus:outline-hidden"
                    role="textbox" aria-label="{{ __('Relato do professor') }}"></div>
            </div>

            <div class="flex items-center justify-between text-xs text-slate-600">
                <div>
                    {{ __('Escreva um relato do que o Senhor fez no evento, com aprendizados e frutos para inspirar novos professores.') }}
                </div>
                <div id="testimony-counter" data-limit="{{ $characterLimit }}" class="font-semibold">0 / {{ $characterLimit }}</div>
            </div>

            @error('notes')
                <p class="text-sm text-red-600">{{ $message }}</p>
            @enderror

            <div class="flex justify-end">
                <button type="submit"
                    class="inline-flex items-center justify-center rounded-lg bg-sky-700 px-5 py-2 text-sm font-semibold text-white transition hover:bg-sky-800">
                    {{ __('Salvar relato') }}
                </button>
            </div>
        </form>
    </section>

    @push('js')
        <script>
            (function() {
                const form = document.getElementById('training-testimony-form');
                const editor = document.getElementById('testimony-editor');
                const hiddenInput = document.getElementById('testimony-notes');
                const counter = document.getElementById('testimony-counter');
                const limit = Number(counter?.dataset.limit || 0);
                const initialContent = @json($initialNotes);

                if (!form || !editor || !hiddenInput || !counter) {
                    return;
                }

                editor.innerHTML = initialContent;
                document.execCommand('styleWithCSS', false, true);

                const plainTextLength = () => editor.innerText.replace(/\s+/g, ' ').trim().length;

                const syncState = () => {
                    hiddenInput.value = editor.innerHTML.trim();
                    const length = plainTextLength();
                    counter.textContent = `${length} / ${limit}`;
                    counter.classList.toggle('text-red-600', length > limit);
                    counter.classList.toggle('text-slate-600', length <= limit);
                };

                const applyStyleToSelection = (property, value) => {
                    editor.focus();
                    const selection = window.getSelection();

                    if (!selection || selection.rangeCount === 0) {
                        return;
                    }

                    const range = selection.getRangeAt(0);

                    if (range.collapsed) {
                        return;
                    }

                    const span = document.createElement('span');
                    span.style[property] = value;

                    try {
                        range.surroundContents(span);
                    } catch (error) {
                        const selectedContent = range.extractContents();
                        span.appendChild(selectedContent);
                        range.insertNode(span);
                    }

                    syncState();
                };

                form.querySelectorAll('[data-command]').forEach((button) => {
                    button.addEventListener('click', () => {
                        const command = button.dataset.command;
                        if (!command) {
                            return;
                        }

                        editor.focus();
                        document.execCommand(command, false, null);
                        syncState();
                    });
                });

                const formatBlockSelect = form.querySelector('[data-format-block]');
                if (formatBlockSelect) {
                    formatBlockSelect.addEventListener('change', (event) => {
                        editor.focus();
                        document.execCommand('formatBlock', false, event.target.value);
                        syncState();
                    });
                }

                const colorInput = form.querySelector('[data-color]');
                if (colorInput) {
                    colorInput.addEventListener('input', (event) => {
                        editor.focus();
                        document.execCommand('foreColor', false, event.target.value);
                        syncState();
                    });
                }

                const fontFamilySelect = form.querySelector('[data-font-family]');
                if (fontFamilySelect) {
                    fontFamilySelect.addEventListener('change', (event) => {
                        if (event.target.value === 'inherit') {
                            return;
                        }

                        applyStyleToSelection('fontFamily', event.target.value);
                    });
                }

                const fontSizeSelect = form.querySelector('[data-font-size]');
                if (fontSizeSelect) {
                    fontSizeSelect.addEventListener('change', (event) => {
                        applyStyleToSelection('fontSize', event.target.value);
                    });
                }

                editor.addEventListener('input', syncState);
                editor.addEventListener('keyup', syncState);
                editor.addEventListener('mouseup', syncState);

                form.addEventListener('submit', (event) => {
                    syncState();

                    if (plainTextLength() > limit) {
                        event.preventDefault();
                    }
                });

                syncState();
            })();
        </script>
    @endpush
</x-layouts.app>
