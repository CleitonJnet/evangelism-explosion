<div>
    <flux:modal name="director-inventory-edit-modal" wire:model="showModal" class="max-w-5xl w-[calc(100%-4px)] mx-auto bg-sky-950! p-0! max-h-[calc(100vh-4px)]! overflow-hidden">
        <div class="flex h-[min(90vh,52rem)] flex-col overflow-hidden rounded-2xl">
            @php
                $isActive = $status === 'active';
                $headerStatusLabel = $isActive ? __('Ativo') : __('Inativo');
                $headerStatusClasses = $isActive
                    ? 'border-emerald-400/40 bg-emerald-500/15 text-emerald-100'
                    : 'border-rose-400/40 bg-rose-500/15 text-rose-100';
            @endphp

            <header class="sticky top-0 z-20 border-b border-sky-800 bg-sky-950 px-6 py-4 text-sky-50">
                <div class="flex items-start justify-between gap-4">
                    <div class="space-y-1">
                        <h3 class="text-lg font-semibold">{{ __('Editar estoque') }}</h3>
                        <p class="text-sm opacity-90">
                            {{ __('Atualize as informações principais do estoque e mantenha os dados operacionais sempre corretos.') }}
                        </p>
                    </div>

                    <div class="shrink-0 rounded-full border px-3 py-1 text-sm font-semibold {{ $headerStatusClasses }}">
                        {{ $headerStatusLabel }}
                    </div>
                </div>
            </header>

            <div class="min-h-0 flex-1 overflow-y-auto bg-slate-50 px-6 py-6">
                <div class="space-y-6 pb-2">
                    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="mb-5 flex flex-col gap-1">
                            <h4 class="text-base font-semibold text-slate-900">{{ __('1. Identificação do estoque') }}</h4>
                            <p class="text-sm text-slate-600">
                                {{ __('Revise como este estoque é identificado e qual é o seu tipo de operação dentro da área de materiais.') }}
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-x-4 gap-y-8">
                            <x-src.form.input name="director-inventory-edit-name" wire:model.live="name" label="Nome"
                                type="text" width_basic="320" autofocus required />
                            <x-src.form.select name="director-inventory-edit-kind" wire:model.live="kind" label="Tipo"
                                width_basic="220" :options="$kindOptions" required />
                        </div>
                    </section>

                    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="mb-5 flex flex-col gap-1">
                            <h4 class="text-base font-semibold text-slate-900">{{ __('2. Responsável e contato') }}</h4>
                            <p class="text-sm text-slate-600">
                                {{ __('Atualize o responsável pelo estoque e os canais de contato usados pela equipe para comunicação rápida.') }}
                            </p>
                        </div>

                        @if ($kind === 'teacher')
                            <div class="mb-5 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                                {{ __('Como este estoque é do tipo professor, mantenha aqui o responsável correto.') }}
                            </div>
                        @endif

                        <div class="flex flex-wrap gap-x-4 gap-y-8">
                            @if ($kind === 'teacher')
                                <x-src.form.select name="director-inventory-edit-user" wire:model.live="user_id"
                                    label="Professor responsável" width_basic="320" :options="$teacherOptions" required />
                            @endif
                            <x-src.form.input name="director-inventory-edit-phone" wire:model.live="phone"
                                label="Telefone" type="text" width_basic="200" />
                            <x-src.form.input name="director-inventory-edit-email" wire:model.live="email"
                                label="Email" type="email" width_basic="280" />
                        </div>
                    </section>

                    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="mb-5 flex flex-col gap-1">
                            <h4 class="text-base font-semibold text-slate-900">{{ __('3. Endereço do estoque') }}</h4>
                            <p class="text-sm text-slate-600">
                                {{ __('Mantenha a localização física atualizada para apoiar entregas, retiradas e conferências presenciais.') }}
                            </p>
                        </div>

                        <livewire:address-fields wire:model="address" title="Endereço do estoque"
                            wire:key="director-inventory-edit-address-fields-{{ $inventoryId }}" />
                    </section>

                    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="mb-5 flex flex-col gap-1">
                            <h4 class="text-base font-semibold text-slate-900">{{ __('4. Observações operacionais') }}</h4>
                            <p class="text-sm text-slate-600">
                                {{ __('Ajuste orientações, detalhes de acesso ou qualquer observação útil para a rotina deste estoque.') }}
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-x-4 gap-y-8">
                            <x-src.form.textarea name="director-inventory-edit-notes" wire:model.live="notes"
                                label="Observações" rows="4" />
                        </div>
                    </section>
                </div>
            </div>

            <footer class="sticky bottom-0 z-20 border-t border-sky-800 bg-sky-950 px-6 py-4 text-sky-50">
                @php
                    $statusButtonClasses = $isActive
                        ? 'border-amber-200 bg-amber-50 text-amber-900 hover:bg-amber-100'
                        : 'border-emerald-200 bg-emerald-50 text-emerald-800 hover:bg-emerald-100';
                    $statusButtonLabel = $isActive ? __('Desativar estoque') : __('Ativar estoque');
                @endphp

                <div class="flex items-center justify-between gap-3">
                    <x-src.btn-silver type="button" wire:click="closeModal" wire:loading.attr="disabled"
                        wire:target="save">
                        {{ __('Cancelar') }}
                    </x-src.btn-silver>
                    <div class="flex items-center gap-3">
                        <button type="button"
                            class="inline-flex items-center justify-center rounded-xl border px-4 py-2 text-sm font-semibold transition cursor-pointer {{ $statusButtonClasses }}"
                            wire:click="promptStatusToggle" wire:loading.attr="disabled" wire:target="promptStatusToggle,confirmStatusToggle,save">
                            {{ $statusButtonLabel }}
                        </button>
                        <x-src.btn-gold type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save">
                            <span wire:loading.remove wire:target="save">{{ __('Salvar alterações') }}</span>
                            <span wire:loading wire:target="save">{{ __('Salvando...') }}</span>
                        </x-src.btn-gold>
                    </div>
                </div>
            </footer>
        </div>
    </flux:modal>

    <flux:modal name="director-inventory-edit-status-confirmation-modal" wire:model="showStatusConfirmationModal"
        class="max-w-md w-[calc(100%-4px)] mx-auto">
        @php
            $pendingIsActive = $pendingStatus === 'active';
        @endphp

        <div class="space-y-4">
            <div>
                <flux:heading size="lg">{{ __('Confirmar mudança de status') }}</flux:heading>
                <flux:text class="mt-2 text-sm text-slate-600">
                    {{ $pendingIsActive
                        ? __('Deseja ativar este estoque? Ele voltará a aparecer como disponível para operação.')
                        : __('Deseja desativar este estoque? Ele continuará cadastrado, mas ficará marcado como inativo.') }}
                </flux:text>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
                <div class="font-semibold text-slate-900">{{ __('Novo status') }}</div>
                <div>{{ $pendingIsActive ? __('Ativo') : __('Inativo') }}</div>
            </div>

            <div class="flex justify-end gap-2">
                <flux:button type="button" variant="ghost" wire:click="cancelStatusToggle">
                    {{ __('Cancelar') }}
                </flux:button>
                <flux:button type="button" variant="primary" wire:click="confirmStatusToggle"
                    wire:loading.attr="disabled" wire:target="confirmStatusToggle">
                    {{ __('Confirmar mudança') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
