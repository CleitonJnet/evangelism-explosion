<div>
    <flux:modal name="manage-mentors-modal" wire:model="showModal"
        class="max-w-5xl w-full bg-sky-900! p-0! max-h-[calc(100vh-50px)]! overflow-hidden">
        <div class="flex max-h-[calc(100vh-50px)] flex-col overflow-hidden">
            <div class="shrink-0 border-b border-sky-700 px-6 py-4">
                <flux:heading size="lg"><span class="text-white">{{ __('Mentores do treinamento') }}</span>
                </flux:heading>
                <flux:subheading>
                    <span
                        class="text-white/90">{{ __('Adicione ou remova mentores vinculados a este treinamento.') }}</span>
                </flux:subheading>
            </div>

            <div class="min-h-0 flex-1 overflow-y-auto bg-white/90 space-y-4 px-6 py-4">
                <div class="rounded-xl border border-sky-500 bg-sky-400 p-3">
                    <div class="mb-1 block text-xs font-semibold text-slate-700 uppercase">
                        {{ __('Buscar usuário existente') }}
                    </div>
                    <input type="text" wire:model.live.debounce.300ms="userSearch"
                        placeholder="{{ __('Digite nome ou e-mail') }}"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800" />

                    <div
                        class="mt-2 max-h-44 space-y-1 overflow-y-auto rounded-lg border border-slate-200 bg-white p-2">
                        @forelse ($searchResults as $searchResult)
                            <button type="button"
                                class="w-full rounded-md border border-slate-200 bg-white px-2 py-1 text-left text-xs text-slate-700 hover:bg-slate-100 cursor-pointer"
                                wire:click="addMentor({{ $searchResult->id }})" wire:loading.attr="disabled"
                                wire:target="addMentor,removeMentor,openCreateMentorUserModal">
                                <span class="font-semibold">{{ $searchResult->name }}</span>
                                <span class="text-slate-500">({{ $searchResult->email }})</span>
                            </button>
                        @empty
                            <div class="text-xs text-slate-500">
                                {{ __('Nenhum usuário encontrado para o filtro informado.') }}
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="flex justify-end">
                    <x-src.btn-silver type="button" wire:click="openCreateMentorUserModal" wire:loading.attr="disabled"
                        wire:target="addMentor,removeMentor,openCreateMentorUserModal">
                        {{ __('Criar novo usuário') }}
                    </x-src.btn-silver>
                </div>

                <div class="rounded-t-xl border border-amber-700 bg-amber-400">
                    <div
                        class="flex items-center justify-between gap-2 border-b border-slate-200 px-4 py-3 text-xs font-semibold uppercase text-amber-950">
                        <span>{{ __('Mentores no evento') }}</span>
                        <span>{{ $mentorUsers->count() }} {{ __('Mentores') }}</span>
                    </div>

                    <div class="bg-amber-50">
                        @forelse ($mentorUsers as $mentorUser)
                            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-amber-200 px-4 py-3 last:border-b-0"
                                wire:key="mentor-user-{{ $mentorUser->id }}">
                                <div class="flex min-w-0 items-start gap-3">
                                    <div
                                        class="inline-flex h-6 min-w-6 items-center justify-center rounded-md border border-amber-300 bg-white px-1 text-[11px] font-bold text-amber-900">
                                        {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                                    </div>
                                    <div class="min-w-0 text-sm">
                                        <div class="truncate font-semibold text-amber-950">{{ $mentorUser->name }}</div>
                                        <div class="truncate text-amber-900">{{ $mentorUser->email }}</div>
                                        <div class="truncate text-xs text-amber-700">
                                            {{ $mentorUser->church?->name ?? __('Sem igreja oficial') }}
                                        </div>
                                    </div>
                                </div>

                                <button type="button"
                                    class="inline-flex items-center justify-center text-xs font-semibold cursor-pointer"
                                    x-on:click.prevent="if (window.confirm('{{ __('Deseja realmente remover este mentor deste treinamento?') }}')) { $wire.removeMentor({{ $mentorUser->id }}) }"
                                    wire:loading.attr="disabled"
                                    wire:target="addMentor,removeMentor,openCreateMentorUserModal"
                                    aria-label="{{ __('Remover mentor') }}" title="{{ __('Remover mentor') }}">
                                    <svg version="1.1" id="remove" xmlns="http://www.w3.org/2000/svg"
                                        class="h-5 w-5 fill-red-500" xmlns:xlink="http://www.w3.org/1999/xlink"
                                        viewBox="0 0 459.739 459.739" xml:space="preserve">
                                        <path
                                            d="M229.869,0C102.917,0,0,102.917,0,229.869c0,126.952,102.917,229.869,229.869,229.869s229.869-102.917,229.869-229.869 C459.738,102.917,356.821,0,229.869,0z M313.676,260.518H146.063c-16.926,0-30.649-13.723-30.649-30.649 c0-16.927,13.723-30.65,30.649-30.65h167.613c16.925,0,30.649,13.723,30.649,30.65C344.325,246.795,330.601,260.518,313.676,260.518 z" />
                                    </svg>
                                </button>
                            </div>
                        @empty
                            <div class="px-4 py-4 text-sm text-slate-600">
                                {{ __('Nenhum mentor vinculado a este treinamento.') }}
                            </div>
                        @endforelse
                    </div>
                </div>

            </div>

            <div class="shrink-0 border-t border-sky-700 px-6 py-4 flex justify-end">
                <x-src.btn-gold wire:click="closeModal" wire:target="addMentor,removeMentor,openCreateMentorUserModal">
                    {{ __('Fechar') }}
                </x-src.btn-gold>
            </div>
        </div>
    </flux:modal>
</div>
