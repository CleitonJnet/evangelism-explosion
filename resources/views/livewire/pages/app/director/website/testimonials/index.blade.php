<div wire:loading.class="pointer-events-none" wire:target="saveEditedTestimonial,deleteSelectedTestimonial,toggleStatus">
    <livewire:pages.app.director.website.testimonials.create-modal wire:key="director-testimonials-create-modal" />

    <section class="flex flex-wrap gap-4">
        <article
            class="rounded-2xl border border-sky-950/45 bg-linear-to-br from-slate-100 via-white to-slate-200 px-4 py-3 min-w-fit flex-auto">
            <div class="text-xs text-slate-500">{{ __('Total de testemunhos') }}</div>
            <div class="text-3xl font-bold text-slate-900">{{ $totalTestimonials }}</div>
        </article>

        <article
            class="rounded-2xl border border-sky-950/45 bg-linear-to-br from-slate-100 via-white to-slate-200 px-4 py-3 min-w-fit flex-auto">
            <div class="text-xs text-slate-500">{{ __('Publicados no site') }}</div>
            <div class="text-3xl font-bold text-slate-900">{{ $activeTestimonials }}</div>
        </article>
    </section>

    <section class="mt-4 overflow-hidden rounded-2xl border border-slate-300 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-5xl text-left text-sm">
                <thead class="bg-linear-to-b from-sky-200 to-sky-300 text-xs uppercase text-slate-700">
                    <tr class="border-b border-slate-300">
                        <th class="px-3 py-2 w-14 text-center">{{ __('Mover') }}</th>
                        <th class="px-3 py-2 w-20 text-center">{{ __('Foto') }}</th>
                        <th class="px-3 py-2 w-56">{{ __('Nome') }}</th>
                        <th class="px-3 py-2">{{ __('Testemunho') }}</th>
                        <th class="px-3 py-2 w-36">{{ __('Status') }}</th>
                        <th class="px-3 py-2 w-44 text-center">{{ __('Acões') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 js-testimonials-list">
                    @forelse ($testimonials as $testimonial)
                        <tr class="odd:bg-white even:bg-slate-50/50 hover:bg-sky-50/40 js-testimonial-item"
                            wire:key="testimonial-row-{{ $testimonial['id'] }}"
                            data-item-id="{{ $testimonial['id'] }}">
                            <td class="px-3 py-2 text-center">
                                <span class="inline-flex text-slate-500 cursor-grab js-testimonial-drag-handle"
                                    title="{{ __('Arrastar para reordenar') }}">
                                    <img src="{{ asset('images/svg/dragAndDrop.svg') }}" alt="drag and drop"
                                        class="h-5 object-contain">
                                </span>
                            </td>
                            <td class="px-3 py-2">
                                <img src="{{ $testimonial['photo_url'] }}"
                                    alt="{{ __('Foto de :name', ['name' => $testimonial['name']]) }}"
                                    class="h-12 w-12 rounded-lg border border-slate-200 object-cover">
                            </td>
                            <td class="px-3 py-2">
                                <div class="font-semibold text-slate-900">{{ $testimonial['name'] }}</div>
                                <div class="text-xs text-slate-500">{{ $testimonial['meta'] ?: __('Sem complemento') }}
                                </div>
                            </td>
                            <td class="px-3 py-2">
                                <div x-data="{ expanded: false }" class="space-y-1">
                                    <p class="text-slate-700" x-show="!expanded">{{ $testimonial['quote_preview'] }}
                                    </p>
                                    <p class="text-slate-700" x-show="expanded" x-cloak>{{ $testimonial['quote'] }}</p>
                                    <button type="button"
                                        class="text-xs font-semibold text-sky-900 underline cursor-pointer"
                                        x-on:click="expanded = !expanded">
                                        <span
                                            x-text="expanded ? '{{ __('Mostrar menos') }}' : '{{ __('Mostrar mais') }}'"></span>
                                    </button>
                                </div>
                            </td>
                            <td class="px-3 py-2">
                                <x-app.switch-schedule :label="__('Ativo')" :key="'testimonial-status-' . $testimonial['id']" :checked="$testimonial['is_active']"
                                    wire:change="toggleStatus({{ $testimonial['id'] }}, $event.target.checked)"
                                    wire:loading.attr="disabled" wire:target="toggleStatus" />
                            </td>
                            <td class="px-3 py-2">
                                <div class="flex justify-end gap-2">
                                    <button type="button"
                                        class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-100 cursor-pointer"
                                        wire:click="openEditModal({{ $testimonial['id'] }})">
                                        {{ __('Editar') }}
                                    </button>

                                    <button type="button"
                                        class="inline-flex items-center justify-center rounded-lg border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700 transition hover:bg-red-100 cursor-pointer"
                                        wire:click="openDeleteModal({{ $testimonial['id'] }})">
                                        {{ __('Remover') }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-3 py-4 text-sm text-slate-600">
                                {{ __('Nenhum testemunho cadastrado. Use o botao Novo testemunho para iniciar.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <flux:modal name="director-edit-testimonial-modal" wire:model="showEditModal" class="max-w-4xl w-full">
        <div class="space-y-4">
            <div>
                <flux:heading size="lg">{{ __('Editar testemunho') }}</flux:heading>
                <flux:subheading>{{ $selectedTestimonialName }}</flux:subheading>
            </div>

            <div class="grid gap-4">
                <flux:field>
                    <flux:label>{{ __('Nome') }}</flux:label>
                    <flux:input wire:model.live="editName" />
                    <flux:error name="editName" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Cargo / igreja') }}</flux:label>
                    <flux:input wire:model.live="editMeta" />
                    <flux:error name="editMeta" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Testemunho') }}</flux:label>
                    <div x-data="{ text: @entangle('editQuote').live }" class="space-y-1">
                        <flux:textarea rows="6" maxlength="460" x-model="text" wire:model.live="editQuote" />
                        <div class="text-right text-xs text-slate-500">
                            <span x-text="(text ?? '').length"></span>/460
                        </div>
                    </div>
                    <flux:error name="editQuote" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Foto da pessoa') }}</flux:label>
                    <input type="file" accept=".webp,.jpeg,.webp,.webp" wire:model.live="editPhotoUpload"
                        class="w-full rounded-xl border border-neutral-200 bg-white p-2 text-sm text-neutral-700 file:me-4 file:rounded-lg file:border-0 file:bg-neutral-900 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white">
                    <div wire:loading.flex wire:target="editPhotoUpload"
                        class="items-center gap-2 text-xs font-semibold text-sky-900">
                        <span class="h-3 w-3 animate-spin rounded-full border-2 border-sky-300 border-t-sky-900"></span>
                        {{ __('Processando imagem...') }}
                    </div>
                    <div class="text-[11px] text-neutral-500">
                        {{ __('Formatos aceitos: webp, JPEG, PNG ou WEBP (até 5MB).') }}
                    </div>
                    <div class="flex items-center gap-3">
                        <img src="{{ $editPhotoUpload ? $editPhotoUpload->temporaryUrl() : $editCurrentPhotoUrl }}"
                            alt="{{ __('Pré-visualização da foto') }}"
                            class="h-24 w-24 rounded-lg border border-slate-200 object-cover">
                    </div>
                    <flux:error name="editPhotoUpload" />
                </flux:field>
            </div>

            <div class="flex justify-end gap-2">
                <flux:button type="button" variant="ghost" wire:click="closeEditModal">{{ __('Cancelar') }}
                </flux:button>
                <flux:button type="button" variant="primary" wire:click="saveEditedTestimonial"
                    wire:loading.attr="disabled" wire:target="saveEditedTestimonial">
                    {{ __('Salvar') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>

    <flux:modal name="director-delete-testimonial-modal" wire:model="showDeleteModal" class="max-w-md">
        <div class="space-y-4">
            <div>
                <flux:heading size="lg">{{ __('Remover testemunho') }}</flux:heading>
                <flux:text class="mt-2 text-sm text-slate-600">
                    {{ __('Tem certeza que deseja remover este testemunho? Esta acao nao pode ser desfeita.') }}
                </flux:text>
                <flux:text class="mt-1 text-sm font-semibold text-slate-900">{{ $selectedTestimonialName }}
                </flux:text>
            </div>

            <div class="flex justify-end gap-2">
                <flux:button type="button" variant="ghost" wire:click="closeDeleteModal">{{ __('Cancelar') }}
                </flux:button>
                <flux:button type="button" variant="danger" wire:click="deleteSelectedTestimonial"
                    wire:loading.attr="disabled" wire:target="deleteSelectedTestimonial">
                    {{ __('Confirmar remocao') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
