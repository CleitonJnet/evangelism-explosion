<div>
    <flux:modal name="manage-mentors-modal" wire:model="showModal" class="max-w-5xl w-full">
        <div class="space-y-4">
            <div>
                <flux:heading size="lg">{{ __('Mentores do treinamento') }}</flux:heading>
                <flux:subheading>
                    {{ __('Adicione ou remova mentores vinculados a este treinamento.') }}
                </flux:subheading>
            </div>

            <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                <div class="mb-1 block text-xs font-semibold text-slate-700">
                    {{ __('Buscar usuário existente') }}
                </div>
                <input type="text" wire:model.live.debounce.300ms="userSearch"
                    placeholder="{{ __('Digite nome ou e-mail') }}"
                    class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800" />

                <div class="mt-2 max-h-44 space-y-1 overflow-y-auto rounded-lg border border-slate-200 bg-white p-2">
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

            <div class="rounded-xl border border-slate-200 bg-white">
                <div class="border-b border-slate-200 px-4 py-3 text-xs font-semibold uppercase text-slate-600">
                    {{ __('Mentores atuais') }}
                </div>

                <div class="max-h-[52vh] overflow-y-auto">
                    @forelse ($mentorUsers as $mentorUser)
                        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-4 py-3 last:border-b-0"
                            wire:key="mentor-user-{{ $mentorUser->id }}">
                            <div class="text-sm text-slate-700">
                                <div class="font-semibold text-slate-900">{{ $mentorUser->name }}</div>
                                <div>{{ $mentorUser->email }}</div>
                                <div class="text-xs text-slate-500">
                                    {{ $mentorUser->church?->name ?? __('Sem igreja oficial') }}
                                </div>
                            </div>

                            <button type="button"
                                class="inline-flex items-center justify-center rounded-lg border border-red-200 bg-red-50 px-2 py-1 text-xs font-semibold text-red-700 transition hover:bg-red-100 cursor-pointer"
                                x-on:click.prevent="if (window.confirm('{{ __('Deseja realmente remover este mentor deste treinamento?') }}')) { $wire.removeMentor({{ $mentorUser->id }}) }"
                                wire:loading.attr="disabled"
                                wire:target="addMentor,removeMentor,openCreateMentorUserModal">
                                {{ __('Remover') }}
                            </button>
                        </div>
                    @empty
                        <div class="px-4 py-4 text-sm text-slate-600">
                            {{ __('Nenhum mentor vinculado a este treinamento.') }}
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="flex justify-end">
                <flux:button type="button" variant="ghost" wire:click="closeModal">
                    {{ __('Fechar') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
