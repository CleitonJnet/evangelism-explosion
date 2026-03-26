<div>
    <flux:modal name="director-course-create-modal" wire:model="showModal" class="max-w-5xl w-[calc(100%-4px)] mx-auto bg-sky-950! p-0! max-h-[calc(100vh-4px)]! overflow-hidden">
        <div class="flex max-h-[90vh] flex-col overflow-hidden rounded-2xl">
            <header class="sticky top-0 z-20 border-b border-sky-800 bg-sky-950 px-6 py-4 text-sky-50">
                <h3 class="text-lg font-semibold">{{ __('Cadastrar novo curso') }}</h3>
                <p class="text-sm opacity-90">
                    {{ __('Preencha os dados do curso para vinculá-lo ao ministério selecionado.') }}
                </p>
            </header>

            <div class="min-h-0 flex-1 overflow-y-auto bg-white px-6 py-6">
                <div class="space-y-8">
                    <section class="space-y-5">
                        <div>
                            <h4 class="text-base font-semibold text-sky-950">{{ __('Identidade visual') }}</h4>
                            <p class="text-sm text-slate-600">{{ __('Logo e banner usados na apresentação do curso.') }}
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-6">
                            <div
                                class="grid justify-items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 p-4 flex-auto basis-40">
                                <input id="director-course-logo-upload" type="file" accept="image/*"
                                    wire:model.live="logoUpload" class="sr-only">

                                <label for="director-course-logo-upload"
                                    class="cursor-pointer overflow-hidden rounded-xl flex justify-center items-center p-1">
                                    <img src="{{ $logoPreviewUrl }}" alt="{{ __('Logo do curso') }}"
                                        class="h-28 w-28 rounded-lg object-cover">
                                </label>

                                <p class="text-center text-xs text-slate-600">
                                    {{ __('Clique na imagem para enviar a logo.') }}
                                </p>

                                @error('logoUpload')
                                    <p class="text-xs font-semibold text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div
                                class="grid gap-2 rounded-xl border border-slate-200 bg-slate-50 p-4 flex-auto basis-100">
                                <input id="director-course-banner-upload" type="file" accept="image/*"
                                    wire:model.live="bannerUpload" class="sr-only">

                                <label for="director-course-banner-upload"
                                    class="cursor-pointer overflow-hidden rounded-xl border border-slate-300 bg-slate-100 p-1">
                                    <div class="aspect-[21/9] w-full overflow-hidden rounded-lg">
                                        <img src="{{ $bannerPreviewUrl }}" alt="{{ __('Banner do curso') }}"
                                            class="h-full w-full object-cover">
                                    </div>
                                </label>

                                <p class="text-center text-xs text-slate-600">
                                    {{ __('Clique na imagem para enviar o banner.') }}
                                </p>

                                @error('bannerUpload')
                                    <p class="text-xs font-semibold text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </section>

                    <section class="space-y-5">
                        <div>
                            <h4 class="text-base font-semibold text-sky-950">{{ __('Configuração do curso') }}</h4>
                            <p class="text-sm text-slate-600">
                                {{ __('Dados estruturais e de classificação do curso.') }}</p>
                        </div>

                        <div class="flex flex-wrap gap-x-4 gap-y-8">
                            <x-src.form.input name="type" wire:model.live="type" label="Tipo do curso" type="text"
                                width_basic="220" required />
                            <x-src.form.input name="name" wire:model.live="name" label="Nome do curso" type="text"
                                width_basic="320" required />
                            <x-src.form.input name="initials" wire:model.live="initials" label="Sigla" type="text"
                                width_basic="140" required />
                            <x-src.form.select name="execution" wire:model.live="execution" label="Execução"
                                width_basic="180" :options="$executionOptions" required />
                            <x-src.form.select name="min_stp_sessions" wire:model.live="min_stp_sessions"
                                label="Sessões mínimas STP" width_basic="180" :options="$stpSessionOptions" />
                            <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3" style="flex: 1 0 220px">
                                <div class="flex items-center justify-between gap-3">
                                    <div>
                                        <div class="text-sm font-semibold text-slate-900">{{ __('Curso credenciável') }}</div>
                                        <div class="text-xs text-slate-500">{{ __('Permite credenciar alunos neste curso.') }}</div>
                                    </div>
                                    <flux:switch wire:model.live="is_accreditable" />
                                </div>

                                @error('is_accreditable')
                                    <p class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <x-src.form.input name="price" wire:model.live="price" label="Preço" type="text"
                                width_basic="160" inputmode="decimal" autocomplete="off"
                                oninput="this.value = this.value.replace(/[^0-9,.-]/g, '')" />
                            <x-src.form.input name="learnMoreLink" wire:model.live="learnMoreLink"
                                label="Link saiba mais" type="url" width_basic="320" />
                            <div class="relative z-0 max-w-full group" style="flex: 1 0 220px">
                                <input id="director-course-color" type="color" wire:model.live="color"
                                    class="sr-only">
                                <label for="director-course-color"
                                    class="flex h-11 w-full cursor-pointer items-center justify-between rounded-md border border-slate-300 px-3 shadow-xs transition hover:border-sky-500"
                                    style="background-color: {{ $color ?: '#4F4F4F' }}">
                                    <span class="rounded bg-black/35 px-2 py-0.5 text-xs font-semibold text-white">
                                        {{ __('Cor temática') }} *
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
                            <x-src.form.input name="slogan" wire:model.live="slogan" label="Slogan" type="text"
                                width_basic="1200" />
                        </div>
                    </section>

                    <section class="space-y-5">
                        <div>
                            <h4 class="text-base font-semibold text-sky-950">{{ __('Conteúdo e posicionamento') }}</h4>
                            <p class="text-sm text-slate-600">
                                {{ __('Informações usadas na apresentação e descrição do curso.') }}</p>
                        </div>

                        <div class="flex flex-wrap gap-x-4 gap-y-8">
                            <x-src.form.textarea name="targetAudience" wire:model.live="targetAudience"
                                label="Público-alvo" rows="3" />
                            <x-src.form.textarea name="knowhow" wire:model.live="knowhow" label="Conhecimento"
                                rows="3" />
                            <x-src.form.textarea name="description" wire:model.live="description" label="Descrição"
                                rows="4" />
                        </div>
                    </section>
                </div>
            </div>

            <footer class="sticky bottom-0 z-20 border-t border-sky-800 bg-sky-950 px-6 py-4 text-sky-50">
                <div class="flex justify-between gap-3">
                    <x-src.btn-silver type="button" wire:click="closeModal" wire:loading.attr="disabled"
                        wire:target="save,logoUpload,bannerUpload">
                        {{ __('Cancelar') }}
                    </x-src.btn-silver>
                    <x-src.btn-gold type="button" wire:click="save" wire:loading.attr="disabled"
                        wire:target="save,logoUpload,bannerUpload">
                        {{ __('Salvar') }}
                    </x-src.btn-gold>
                </div>
            </footer>
        </div>
    </flux:modal>
</div>
