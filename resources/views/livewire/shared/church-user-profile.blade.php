<div class="relative">
    <x-src.toolbar.header :title="__('Perfil do participante')" :description="__('Consulta do cadastro do usuário dentro do contexto de igrejas.')" :breadcrumb="false" />

    <x-src.toolbar.nav>
        <x-src.toolbar.button :href="$backUrl" :label="$backLabel" icon="list" :tooltip="__('Voltar para a listagem')" />
        <x-src.toolbar.button :label="__('Dados pessoais')" icon="users" :tooltip="__('Editar dados pessoais')"
            x-on:click.prevent="$wire.openPersonalModal()" />
        <x-src.toolbar.button :label="__('Igreja')" icon="church" :tooltip="__('Alterar igreja vinculada')"
            x-on:click.prevent="$wire.openChurchModal()" />
        <x-src.toolbar.button :label="__('Endereco')" icon="home" :tooltip="__('Editar endereco')"
            x-on:click.prevent="$wire.openAddressModal()" />
    </x-src.toolbar.nav>

    <section
        class="overflow-hidden rounded-2xl border border-slate-200/80 bg-linear-to-br from-sky-950 via-sky-900 to-slate-900 text-slate-100 shadow-sm">
        <div class="grid gap-6 p-6 sm:p-8">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
                <div class="flex items-center gap-4">
                    <div class="relative shrink-0">
                        @if ($user->profile_photo_url)
                            <img src="{{ $user->profile_photo_url }}"
                                alt="{{ __('Foto de perfil de :name', ['name' => $user->name]) }}"
                                class="h-36 w-36 rounded-2xl border border-white/20 object-cover shadow-lg">
                        @else
                            <div
                                class="flex h-36 w-36 items-center justify-center rounded-2xl border border-white/15 bg-amber-300 text-3xl font-semibold tracking-[0.2em] text-sky-950 shadow-lg">
                                {{ $user->initials() }}
                            </div>
                        @endif
                    </div>

                    <div class="space-y-3">
                        <div class="space-y-1">
                            <p class="text-xs uppercase tracking-[0.24em] text-sky-100/60">{{ __('Perfil do usuario') }}
                            </p>
                            <h2 class="text-3xl font-semibold tracking-tight">{{ $user->name }}</h2>
                            <p class="text-sm text-sky-100/75">{{ $this->formatValue($user->phone) }} - {{ $user->email }}</p>
                            <p class="text-sm text-sky-100/75">{{ $user->gender_label ?? __('Não informado') }}</p>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            @if ($this->isPastor())
                                <span
                                    class="inline-flex items-center rounded-full border border-amber-300/30 bg-amber-300/15 px-3 py-1 text-xs font-semibold text-amber-100">
                                    {{ __('Pastor') }}
                                </span>
                            @endif

                            @foreach ($user->roles as $role)
                                <span
                                    class="inline-flex items-center rounded-full border border-white/10 bg-white/10 px-3 py-1 text-xs font-semibold text-slate-100"
                                    wire:key="church-profile-role-pill-{{ $role->id }}">
                                    {{ $role->name }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-white/10 bg-white/6 p-4">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                        <div class="space-y-1">
                            <p class="text-xs uppercase tracking-wide text-sky-100/55">{{ __('Endereco residencial') }}
                            </p>
                            <p class="text-sm leading-6 text-slate-100/88">{{ $this->formatAddress() }}</p>
                        </div>

                        @if ($user->postal_code ?? null)
                            <div class="text-xs text-sky-100/60 sm:text-right">
                                {{ $user->postal_code }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div
                class="grid gap-2 rounded-2xl border border-amber-300/20 bg-linear-to-br from-amber-300/12 to-white/8 px-5 py-4 text-left shadow-lg shadow-sky-950/20 sm:min-w-72">
                <span class="text-xs uppercase tracking-[0.2em] text-amber-100/70">{{ __('Igreja vinculada') }}</span>
                <span class="text-base font-semibold text-white">
                    {{ $user->church?->name ?? $user->church_temp?->name ?? __('Sem igreja vinculada') }}
                </span>
                <span class="text-sm text-sky-100/70">
                    @if ($user->church?->city)
                        {{ $user->church->city . ($user->church->state ? ' / ' . $user->church->state : '') }}
                    @elseif ($user->church_temp?->city)
                        {{ $user->church_temp->city . ($user->church_temp->state ? ' / ' . $user->church_temp->state : '') }}
                    @else
                        {{ __('Sem local definido') }}
                    @endif
                </span>
                <span class="text-xs text-sky-100/55">
                    @if ($user->church?->pastor)
                        {{ __('Pastor: :name', ['name' => $user->church->pastor]) }}
                    @else
                        {{ __('Pastor nao informado') }}
                    @endif
                </span>
            </div>

            <div class="grid gap-3 rounded-2xl border border-white/10 bg-white/6 p-5">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs uppercase tracking-wide text-sky-100/55">{{ __('Observacoes') }}</p>
                        <p class="mt-1 text-sm text-sky-100/65">{{ __('Anotacoes e contexto adicional do cadastro.') }}
                        </p>
                    </div>
                </div>
                <p class="whitespace-pre-line text-sm leading-6 text-slate-100/90">
                    {{ $this->formatValue($user->notes) }}</p>
            </div>
        </div>
    </section>

    <flux:modal name="church-profile-personal-modal" class="max-w-2xl" @close="closePersonalModal"
        wire:model="showPersonalModal">
        <form class="space-y-6" wire:submit="updatePersonal">
            <div class="space-y-2">
                <flux:heading size="lg">{{ __('Editar dados pessoais') }}</flux:heading>
                <flux:text class="text-sm text-(--ee-app-muted)">
                    {{ __('Atualize as informacoes pessoais e o contato principal do usuário.') }}
                </flux:text>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <flux:input wire:model="personal.name" :label="__('Nome')" required />
                <flux:input wire:model="personal.email" :label="__('Email')" type="email" required />
                <flux:input wire:model="personal.phone" :label="__('Telefone')" type="tel" />
                <flux:input wire:model="personal.birthdate" :label="__('Nascimento')" type="date" />
                <flux:select wire:model="personal.gender" :label="__('Genero')" :placeholder="__('Selecione')">
                    <option value="1">{{ __('Masculino') }}</option>
                    <option value="2">{{ __('Feminino') }}</option>
                </flux:select>
                <flux:select wire:model="personal.is_pastor" :label="__('E um pastor')" :placeholder="__('Selecione')">
                    <option value="0">{{ __('Nao') }}</option>
                    <option value="1">{{ __('Sim') }}</option>
                </flux:select>
            </div>

            <flux:textarea wire:model="personal.notes" :label="__('Observacoes')" rows="4" />

            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <flux:button variant="outline" type="button" wire:click="closePersonalModal">
                        {{ __('Cancelar') }}
                    </flux:button>
                    <flux:button variant="primary" type="submit" wire:loading.attr="disabled">
                        {{ __('Salvar') }}
                    </flux:button>
                </div>

                <x-app.action-message on="profile-personal-updated">
                    {{ __('Dados pessoais atualizados.') }}
                </x-app.action-message>
            </div>
        </form>
    </flux:modal>

    <flux:modal name="church-profile-address-modal" class="max-w-3xl" @close="closeAddressModal"
        wire:model="showAddressModal">
        <form class="space-y-6" wire:submit="updateAddress">
            <div class="space-y-2">
                <flux:heading size="lg">{{ __('Editar endereco') }}</flux:heading>
                <flux:text class="text-sm text-(--ee-app-muted)">
                    {{ __('Atualize o endereco de contato do usuário.') }}
                </flux:text>
            </div>

            <livewire:address-fields wire:model="address" title="Endereco do usuario" wire:key="church-profile-address-{{ $user->id }}" />

            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <flux:button variant="outline" type="button" wire:click="closeAddressModal">
                        {{ __('Cancelar') }}
                    </flux:button>
                    <flux:button variant="primary" type="submit" wire:loading.attr="disabled">
                        {{ __('Salvar') }}
                    </flux:button>
                </div>

                <x-app.action-message on="profile-address-updated">
                    {{ __('Endereco atualizado.') }}
                </x-app.action-message>
            </div>
        </form>
    </flux:modal>

    <flux:modal name="church-profile-church-modal" wire:model="showChurchModal" class="max-w-4xl w-full bg-sky-950! p-0!"
        @close="closeChurchModal">
        <div class="flex max-h-[90vh] flex-col overflow-hidden rounded-2xl">
            <div class="sticky top-0 z-20 border-b border-sky-800 bg-sky-950 px-6 py-4">
                <flux:heading size="lg"><span class="text-white!">{{ __('Editar igreja vinculada') }}</span>
                </flux:heading>
                <flux:subheading>
                    <span class="text-white! opacity-80">
                        {{ __('Atualize a igreja oficial associada a este usuário.') }}
                    </span>
                </flux:subheading>
            </div>

            <div class="min-h-0 flex-1 overflow-y-auto bg-white/95 px-6 py-5">
                <div class="space-y-6">
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <div class="text-sm font-semibold text-slate-800">{{ $user->name }}</div>
                        <div class="mt-1 text-sm text-slate-600">
                            {{ __('Igreja atual: :church', ['church' => $user->church?->name ?? $user->church_temp?->name ?? __('Sem igreja vinculada')]) }}
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div class="space-y-2">
                            <label for="church-profile-search" class="block text-sm font-medium text-slate-700">
                                {{ __('Buscar igreja') }}
                            </label>
                            <div class="flex items-stretch overflow-hidden rounded-xl border border-slate-300 bg-white shadow-sm">
                                <input
                                    id="church-profile-search"
                                    type="text"
                                    wire:model.live.debounce.300ms="churchSearch"
                                    placeholder="{{ __('Digite nome, cidade ou UF') }}"
                                    class="min-w-0 flex-1 border-0 bg-transparent px-4 py-2.5 text-sm text-slate-700 placeholder:text-slate-400 focus:outline-none focus:ring-0"
                                />

                                @if (filled($churchSearch))
                                    <button
                                        type="button"
                                        wire:click="clearChurchSearch"
                                        class="inline-flex items-center justify-center px-3 text-slate-400 transition hover:text-slate-700"
                                        aria-label="{{ __('Limpar filtro') }}"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                @endif
                            </div>
                        </div>

                        <div class="max-h-80 overflow-y-auto rounded-xl border border-slate-200 bg-white p-2">
                            @forelse ($churchOptions as $church)
                                <label wire:key="church-profile-option-{{ $church->id }}"
                                    class="mb-1 flex cursor-pointer items-center gap-3 rounded-lg px-2 py-2 transition hover:bg-slate-50">
                                    <input type="radio" wire:model.live="selectedChurchId" value="{{ $church->id }}"
                                        class="h-4 w-4 border-slate-300 text-sky-700 focus:ring-sky-300">
                                    <span class="min-w-0">
                                        <span class="block truncate text-sm font-semibold text-slate-900">
                                            {{ $church->name }}
                                        </span>
                                        <span class="block truncate text-xs text-slate-500">
                                            {{ $church->pastor ?: __('Pastor nao informado') }}
                                        </span>
                                        <span class="block truncate text-xs text-slate-500">
                                            {{ $church->city ?: __('Cidade não informada') }} / {{ $church->state ?: __('UF não informada') }}
                                        </span>
                                    </span>
                                </label>
                            @empty
                                <p class="px-2 py-3 text-sm text-slate-600">{{ __('Nenhuma igreja encontrada para este filtro.') }}</p>
                            @endforelse
                        </div>

                        @error('selectedChurchId')
                            <p class="text-xs font-semibold text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="sticky bottom-0 z-20 border-t border-sky-800 bg-sky-950 px-6 py-4">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <x-app.action-message on="profile-church-updated">
                        {{ __('Igreja atualizada.') }}
                    </x-app.action-message>

                    <div class="flex items-center gap-3">
                        <flux:button variant="outline" type="button" wire:click="closeChurchModal">
                            {{ __('Cancelar') }}
                        </flux:button>
                        <flux:button variant="primary" type="button" wire:click="updateChurch" wire:loading.attr="disabled">
                            {{ __('Salvar') }}
                        </flux:button>
                    </div>
                </div>
            </div>
        </div>
    </flux:modal>
</div>
