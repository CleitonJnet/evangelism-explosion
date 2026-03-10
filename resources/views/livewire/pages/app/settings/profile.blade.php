@php
    $profilePhotoUrl = $this->profilePhotoUrl();
@endphp

<div class="relative">
    <div wire:loading.flex wire:target="profilePhotoUpload,updateProfilePhoto"
        class="fixed inset-0 z-[120] items-center justify-center bg-slate-950/45 backdrop-blur-[1px]">
        <div class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-2xl">
            <flux:icon.loading class="text-sky-900" />
            <div class="space-y-1">
                <p class="text-sm font-semibold text-slate-900">{{ __('Processando foto do perfil') }}</p>
                <p class="text-xs text-slate-600">{{ __('Aguarde o upload e a gravacao no storage serem concluidos.') }}
                </p>
            </div>
        </div>
    </div>

    <x-src.toolbar.header :title="__('Perfil do Usuário')" :description="__('Dados pessoais, seguranca da conta e vinculo ministerial em um unico lugar.')" :breadcrumb="false" />

    <x-src.toolbar.nav>
        <x-src.toolbar.button :label="__('Foto do perfil')" icon="pencil" :tooltip="__('Atualizar foto do perfil')"
            x-on:click.prevent="$wire.openPhotoModal()" />
        <x-src.toolbar.button :label="__('Dados pessoais')" icon="users" :tooltip="__('Editar dados pessoais')"
            x-on:click.prevent="$wire.set('showPersonalModal', true)" data-test="profile-edit-personal" />
        <x-src.toolbar.button :label="__('Endereco')" icon="home" :tooltip="__('Editar endereco')"
            x-on:click.prevent="$wire.set('showAddressModal', true)" data-test="profile-edit-address" />
        <x-src.toolbar.button :label="__('Senha')" icon="document-text" :tooltip="__('Trocar senha')"
            x-on:click.prevent="$wire.set('showPasswordModal', true)" data-test="profile-change-password" />
        @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::twoFactorAuthentication()))
            <x-src.toolbar.button :label="__('2 fatores')" icon="check" :tooltip="__('Gerenciar autenticacao em dois fatores')"
                x-on:click.prevent="$flux.modal('profile-two-factor-modal').show()" data-test="profile-two-factor" />
        @endif
        <x-src.toolbar.button :label="__('Igreja')" icon="church" :tooltip="__('Trocar igreja vinculada')"
            x-on:click.prevent="$wire.openChurchModal()" data-test="change-church" />
        <div class="ml-auto"></div>
        <x-src.toolbar.button :label="__('Excluir conta')" icon="trash" :tooltip="__('Excluir conta do usuario')"
            x-on:click.prevent="$flux.modal('profile-delete-account-modal').show()"
            data-test="profile-delete-account" />
    </x-src.toolbar.nav>

    <section
        class="overflow-hidden rounded-2xl border border-slate-200/80 bg-linear-to-br from-sky-950 via-sky-900 to-slate-900 text-slate-100 shadow-sm">
        <div class="grid gap-6 p-6 sm:p-8">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
                <div class="flex items-center gap-4">
                    <div class="relative shrink-0">
                        @if ($profilePhotoUrl)
                            <img src="{{ $profilePhotoUrl }}"
                                alt="{{ __('Foto de perfil de :name', ['name' => $user->name]) }}"
                                class="h-36 w-36 rounded-2xl border border-white/20 object-cover shadow-lg">
                        @else
                            <div
                                class="flex h-36 w-36 items-center justify-center rounded-2xl border border-white/15 bg-amber-300 text-3xl font-semibold tracking-[0.2em] text-sky-950 shadow-lg">
                                {{ $user->initials() }}
                            </div>
                        @endif

                        <div wire:loading.flex wire:target="profilePhotoUpload,updateProfilePhoto"
                            class="absolute inset-0 items-center justify-center rounded-2xl bg-sky-950/75 px-3 text-center text-xs font-semibold text-white">
                            {{ __('Atualizando foto...') }}
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div class="space-y-1">
                            <p class="text-xs uppercase tracking-[0.24em] text-sky-100/60">{{ __('Perfil do usuario') }}
                            </p>
                            <h2 class="text-3xl font-semibold tracking-tight">{{ $user->name }}</h2>
                            <p class="text-sm text-sky-100/75">{{ $user->phone }} - {{ $user->email }}</p>
                            <p class="text-sm text-sky-100/75">{{ $user->gender ? __('Masculino') : __('Feminino') }}
                            </p>
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
                                    wire:key="role-pill-{{ $role->id }}">
                                    {{ $role->name }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- <div
                    class="grid gap-2 rounded-2xl border border-amber-300/20 bg-linear-to-br from-amber-300/12 to-white/8 px-5 py-4 text-left shadow-lg shadow-sky-950/20 sm:min-w-72">
                    <span
                        class="text-xs uppercase tracking-[0.2em] text-amber-100/70">{{ __('Igreja vinculada') }}</span>
                    <span class="text-base font-semibold text-white">
                        {{ $user->church?->name ?? __('Sem igreja vinculada') }}
                    </span>
                    <span class="text-sm text-sky-100/70">
                        {{ $user->church?->city ? $user->church->city . ($user->church->state ? ' / ' . $user->church->state : '') : __('Sem local definido') }}
                    </span>
                    <span class="text-xs text-sky-100/55">
                        {{ $user->church?->pastor ? __('Pastor: :name', ['name' => $user->church->pastor]) : __('Pastor nao informado') }}
                    </span>
                </div> --}}

                <div class="rounded-2xl border border-white/10 bg-white/6 p-4">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                        <div class="space-y-1">
                            <p class="text-xs uppercase tracking-wide text-sky-100/55">{{ __('Endereco residencial') }}
                            </p>
                            <p class="text-sm leading-6 text-slate-100/88">{{ $this->formatAddress($address) }}</p>
                        </div>

                        @if ($user->postal_code ?? null)
                            <div class="text-xs text-sky-100/60 sm:text-right">
                                {{ $user->postal_code }}
                            </div>
                        @endif
                    </div>
                </div>

            </div>

            {{-- <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                <article class="rounded-2xl border border-white/10 bg-white/6 p-4">
                    <p class="text-xs uppercase tracking-wide text-sky-100/55">{{ __('Telefone') }}</p>
                    <p class="mt-2 text-sm font-semibold text-white">{{ $this->formatValue($user->phone) }}</p>
                </article>

                <article class="rounded-2xl border border-white/10 bg-white/6 p-4">
                    <p class="text-xs uppercase tracking-wide text-sky-100/55">{{ __('Nascimento') }}</p>
                    <p class="mt-2 text-sm font-semibold text-white">{{ $this->formatDate($user->birthdate) }}</p>
                </article>

                <article class="rounded-2xl border border-white/10 bg-white/6 p-4">
                    <p class="text-xs uppercase tracking-wide text-sky-100/55">{{ __('Genero') }}</p>
                    <p class="mt-2 text-sm font-semibold text-white">{{ $user->gender_label ?? __('Nao informado') }}
                    </p>
                </article>

                <article class="rounded-2xl border border-white/10 bg-white/6 p-4">
                    <p class="text-xs uppercase tracking-wide text-sky-100/55">{{ __('Identificacao') }}</p>
                    <p class="mt-2 text-sm font-semibold text-white">#{{ $user->id }}</p>
                </article>
            </div> --}}

            <div
                class="grid gap-2 rounded-2xl border border-amber-300/20 bg-linear-to-br from-amber-300/12 to-white/8 px-5 py-4 text-left shadow-lg shadow-sky-950/20 sm:min-w-72">
                <span class="text-xs uppercase tracking-[0.2em] text-amber-100/70">{{ __('Igreja vinculada') }}</span>
                <span class="text-base font-semibold text-white">
                    {{ $user->church?->name ?? __('Sem igreja vinculada') }}
                </span>
                <span class="text-sm text-sky-100/70">
                    {{ $user->church?->city ? $user->church->city . ($user->church->state ? ' / ' . $user->church->state : '') : __('Sem local definido') }}
                </span>
                <span class="text-xs text-sky-100/55">
                    {{ $user->church?->pastor ? __('Pastor: :name', ['name' => $user->church->pastor]) : __('Pastor nao informado') }}
                </span>
            </div>

            {{-- <div class="rounded-2xl border border-white/10 bg-white/6 p-4">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                    <div class="space-y-1">
                        <p class="text-xs uppercase tracking-wide text-sky-100/55">{{ __('Endereco residencial') }}</p>
                        <p class="text-sm leading-6 text-slate-100/88">{{ $this->formatAddress($address) }}</p>
                    </div>

                    @if (($user->postal_code ?? null) || ($user->city ?? null) || ($user->state ?? null))
                        <div class="text-xs text-sky-100/60 sm:text-right">
                            {{ collect([$user->postal_code, $user->city, $user->state])->filter()->implode(' • ') }}
                        </div>
                    @endif
                </div>
            </div> --}}

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

    <flux:modal name="profile-photo-modal" wire:model="showPhotoModal" class="max-w-4xl w-full bg-sky-950! p-0!"
        @close="closePhotoModal">
        <div class="flex max-h-[90vh] flex-col overflow-hidden rounded-2xl">
            <div class="sticky top-0 z-20 border-b border-sky-800 bg-sky-950 px-6 py-4">
                <flux:heading size="lg"><span class="text-white!">{{ __('Foto do perfil') }}</span>
                </flux:heading>
                <flux:subheading>
                    <span class="text-white! opacity-80">
                        {{ __('Envie uma imagem clara para representar o usuario nas telas do sistema.') }}
                    </span>
                </flux:subheading>
            </div>

            <div class="min-h-0 flex-1 overflow-y-auto bg-white/95 px-6 py-5">
                <div class="grid gap-6">
                    <div class="grid gap-4 rounded-xl border border-slate-300 bg-white/70 p-4">
                        <div class="text-sm font-semibold text-sky-950">{{ __('Upload da foto do perfil') }}</div>

                        <div class="flex flex-wrap items-start gap-6">
                            <div class="grid gap-2">
                                <input id="profile-photo-upload" type="file" accept="image/*"
                                    wire:model.live="profilePhotoUpload" class="sr-only">

                                <label for="profile-photo-upload"
                                    class="group relative cursor-pointer overflow-hidden rounded-2xl border border-slate-300 bg-slate-100 p-1">
                                    @if ($profilePhotoUrl)
                                        <img src="{{ $profilePhotoUrl }}" alt="{{ __('Previa da foto do perfil') }}"
                                            class="h-32 w-32 rounded-xl object-cover">
                                    @else
                                        <div
                                            class="flex h-32 w-32 items-center justify-center rounded-xl bg-slate-200 text-3xl font-semibold tracking-[0.2em] text-slate-700">
                                            {{ $user->initials() }}
                                        </div>
                                    @endif

                                    <div wire:loading.flex wire:target="profilePhotoUpload,updateProfilePhoto"
                                        class="absolute inset-1 items-center justify-center rounded-xl bg-sky-950/70 px-3 text-center text-xs font-semibold text-white">
                                        {{ __('Atualizando foto...') }}
                                    </div>
                                </label>

                                <p class="text-xs text-slate-600">
                                    {{ __('Clique na imagem para selecionar um novo arquivo.') }}</p>

                                @error('profilePhotoUpload')
                                    <p class="text-sm font-semibold text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="min-w-0 flex-1 space-y-3">
                                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-700">
                                    {{ __('Formatos aceitos: JPG, PNG e WEBP. Tamanho maximo: 5 MB.') }}
                                </div>

                                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-700">
                                    {{ __('Ao remover a foto, o sistema exibira automaticamente as iniciais do nome do usuario.') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="sticky bottom-0 z-20 border-t border-sky-800 bg-sky-950 px-6 py-4">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="flex flex-wrap items-center gap-3">
                        @if ($user->profile_photo_url)
                            <x-src.btn-silver type="button" wire:click="removeProfilePhoto"
                                wire:loading.attr="disabled"
                                wire:target="removeProfilePhoto,updateProfilePhoto,profilePhotoUpload">
                                {{ __('Excluir foto') }}
                            </x-src.btn-silver>
                        @endif
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <x-src.btn-silver type="button" wire:click="closePhotoModal" wire:loading.attr="disabled"
                            wire:target="removeProfilePhoto,updateProfilePhoto,profilePhotoUpload">
                            {{ __('Cancelar') }}
                        </x-src.btn-silver>
                        <x-src.btn-gold type="button" wire:click="updateProfilePhoto" wire:loading.attr="disabled"
                            wire:target="updateProfilePhoto,profilePhotoUpload">
                            <span wire:loading.remove wire:target="updateProfilePhoto,profilePhotoUpload">
                                {{ __('Salvar foto') }}
                            </span>
                            <span wire:loading wire:target="updateProfilePhoto,profilePhotoUpload">
                                {{ __('Salvando...') }}
                            </span>
                        </x-src.btn-gold>
                    </div>
                </div>
            </div>
        </div>
    </flux:modal>

    @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::twoFactorAuthentication()))
        <flux:modal name="profile-two-factor-modal" class="max-w-3xl">
            <div class="space-y-6">
                <div class="space-y-2">
                    <flux:heading size="lg">{{ __('Autenticacao em dois fatores') }}</flux:heading>
                    <flux:text class="text-sm text-(--ee-app-muted)">
                        {{ __('Ative ou revise a protecao adicional exigida no login da sua conta.') }}
                    </flux:text>
                </div>

                <livewire:settings.two-factor :modal="true" wire:key="profile-two-factor-settings" />
            </div>
        </flux:modal>
    @endif

    <flux:modal name="profile-delete-account-modal" class="max-w-2xl">
        <div class="space-y-6">
            <div class="space-y-2">
                <flux:heading size="lg">{{ __('Excluir conta') }}</flux:heading>
                <flux:text class="text-sm text-(--ee-app-muted)">
                    {{ __('Esta acao e irreversivel e remove permanentemente o acesso e os dados da conta.') }}
                </flux:text>
            </div>

            <livewire:settings.delete-user-form :modal="true" wire:key="profile-delete-account-settings" />
        </div>
    </flux:modal>

    <flux:modal name="profile-personal-modal" class="max-w-2xl" @close="closePersonalModal"
        wire:model="showPersonalModal">
        <form class="space-y-6" wire:submit="updatePersonal">
            <div class="space-y-2">
                <flux:heading size="lg">{{ __('Editar dados pessoais') }}</flux:heading>
                <flux:text class="text-sm text-(--ee-app-muted)">
                    {{ __('Atualize suas informacoes pessoais e contato principal.') }}
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

    <flux:modal name="profile-address-modal" class="max-w-3xl" @close="closeAddressModal"
        wire:model="showAddressModal">
        <form class="space-y-6" wire:submit="updateAddress">
            <div class="space-y-2">
                <flux:heading size="lg">{{ __('Editar endereco') }}</flux:heading>
                <flux:text class="text-sm text-(--ee-app-muted)">
                    {{ __('Atualize o endereco de contato do usuario.') }}
                </flux:text>
            </div>

            <livewire:address-fields wire:model="address" title="Endereco do usuario" wire:key="profile-address" />

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

    <flux:modal name="profile-password-modal" class="max-w-lg" @close="closePasswordModal"
        wire:model="showPasswordModal">
        <form class="space-y-6" wire:submit="updatePassword">
            <div class="space-y-2">
                <flux:heading size="lg">{{ __('Trocar senha') }}</flux:heading>
                <flux:text class="text-sm text-(--ee-app-muted)">
                    {{ __('Use uma senha forte e exclusiva para manter a conta segura.') }}
                </flux:text>
            </div>

            <div class="grid gap-4">
                <flux:input wire:model="current_password" :label="__('Senha atual')" type="password" required
                    autocomplete="current-password" />
                <flux:input wire:model="password" :label="__('Nova senha')" type="password" required
                    autocomplete="new-password" />
                <flux:input wire:model="password_confirmation" :label="__('Confirmar senha')" type="password"
                    required autocomplete="new-password" />
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3">
                <x-app.action-message on="profile-password-updated">
                    {{ __('Senha atualizada.') }}
                </x-app.action-message>

                <div class="flex items-center gap-3">
                    <flux:button variant="outline" type="button" wire:click="closePasswordModal">
                        {{ __('Cancelar') }}
                    </flux:button>
                    <flux:button variant="primary" type="submit" wire:loading.attr="disabled">
                        {{ __('Salvar') }}
                    </flux:button>
                </div>
            </div>
        </form>
    </flux:modal>
</div>
