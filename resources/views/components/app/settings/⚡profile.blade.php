<div class="flex w-full flex-col gap-8">
    <div class="flex flex-col gap-6 rounded-3xl border border-(--ee-app-border) bg-(--ee-app-surface) p-6 shadow-sm">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                <flux:avatar :name="$user->name" :src="null" :initials="$user->initials()"
                    size="xl" />

                <div class="flex flex-col gap-2">
                    <flux:heading size="xl" level="1">
                        <div class="flex items-center gap-1">
                            @if ($this->isPastor)
                                <flux:badge color="slate">Pastor</flux:badge>
                            @endif
                            {{ $user->name }}
                        </div>
                    </flux:heading>
                    <flux:text class="text-sm text-(--ee-app-muted)">{{ $user->email }}</flux:text>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <flux:button variant="outline" wire:click="$set('showPasswordModal', true)"
                    data-test="profile-change-password">
                    {{ __('Trocar senha') }}
                </flux:button>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">

            @foreach ($user->roles as $role)
                <flux:badge color="blue" wire:key="role-pill-{{ $role->id }}">
                    {{ $role->name }}
                </flux:badge>
            @endforeach
        </div>

    </div>

    <div class="flex gap-6 flex-wrap">
        <div
            class="basis-120 flex-auto flex flex-col gap-6 rounded-2xl border border-(--ee-app-border) bg-(--ee-app-surface) p-6">
            <div class="flex items-center justify-between gap-4">
                <flux:heading size="sm" level="2">
                    <flux:badge color="zinc">#{{ $user->id }}</flux:badge>
                    {{ __('Dados pessoais') }}
                </flux:heading>

                <flux:button variant="primary" wire:click="$set('showPersonalModal', true)"
                    data-test="profile-edit-personal">
                    {{ __('Editar dados pessoais') }}
                </flux:button>

            </div>

            <dl class="grid gap-4 sm:grid-cols-2">
                <div class="space-y-1">
                    <dt class="text-xs uppercase text-(--ee-app-muted)">{{ __('Email') }}</dt>
                    <dd class="text-sm font-medium">
                        {{ $this->formatValue($user->email) }}
                    </dd>
                </div>
                <div class="space-y-1">
                    <dt class="text-xs uppercase text-(--ee-app-muted)">{{ __('Telefone') }}</dt>
                    <dd class="text-sm font-medium">{{ $this->formatValue($user->phone) }}</dd>
                </div>
                <div class="space-y-1">
                    <dt class="text-xs uppercase text-(--ee-app-muted)">{{ __('Nascimento') }}</dt>
                    <dd class="text-sm font-medium">{{ $this->formatValue($user->birthdate) }}</dd>
                </div>
                <div class="space-y-1">
                    <dt class="text-xs uppercase text-(--ee-app-muted)">{{ __('Gênero') }}</dt>
                    <dd class="text-sm font-medium">
                        @if ($user?->gender == 'M')
                            {{ __('Male') }}
                        @elseif ($user?->gender == 'F')
                            {{ __('Female') }}
                        @else
                            {{ __('Não Informado') }}
                        @endif
                    </dd>
                </div>
            </dl>

            <div class="rounded-2xl border border-dashed border-(--ee-app-border) p-4">
                <flux:text class="text-xs uppercase text-(--ee-app-muted)">{{ __('Observações') }}
                </flux:text>
                <flux:text class="mt-2 text-sm text-(--ee-app-text) whitespace-pre-line">
                    {{ $this->formatValue($user->notes) }}
                </flux:text>
            </div>
        </div>

        <div
            class="basis-120 flex-auto flex flex-col gap-6 rounded-2xl border border-(--ee-app-border) bg-(--ee-app-surface) p-6">
            <div class="flex items-center justify-between gap-4">
                <flux:heading size="sm" level="2">{{ __('Endereço do usuário') }}</flux:heading>
                <flux:button variant="outline" wire:click="$set('showAddressModal', true)"
                    data-test="profile-edit-address">
                    {{ __('Editar endereço') }}
                </flux:button>
            </div>

            <dl class="grid gap-4 sm:grid-cols-2">
                <div class="space-y-1">
                    <dt class="text-xs uppercase text-(--ee-app-muted)">{{ __('Logradouro') }}</dt>
                    <dd class="text-sm font-medium">{{ $this->formatValue($user->street) }}</dd>
                </div>
                <div class="space-y-1">
                    <dt class="text-xs uppercase text-(--ee-app-muted)">{{ __('Número') }}</dt>
                    <dd class="text-sm font-medium">{{ $this->formatValue($user->number) }}</dd>
                </div>
                <div class="space-y-1">
                    <dt class="text-xs uppercase text-(--ee-app-muted)">{{ __('Complemento') }}</dt>
                    <dd class="text-sm font-medium">{{ $this->formatValue($user->complement) }}</dd>
                </div>
                <div class="space-y-1">
                    <dt class="text-xs uppercase text-(--ee-app-muted)">{{ __('Bairro') }}</dt>
                    <dd class="text-sm font-medium">{{ $this->formatValue($user->district) }}</dd>
                </div>
                <div class="space-y-1">
                    <dt class="text-xs uppercase text-(--ee-app-muted)">{{ __('Cidade') }}</dt>
                    <dd class="text-sm font-medium">{{ $this->formatValue($user->city) }}</dd>
                </div>
                <div class="space-y-1">
                    <dt class="text-xs uppercase text-(--ee-app-muted)">{{ __('UF') }}</dt>
                    <dd class="text-sm font-medium">{{ $this->formatValue($user->state) }}</dd>
                </div>
                <div class="space-y-1">
                    <dt class="text-xs uppercase text-(--ee-app-muted)">{{ __('CEP') }}</dt>
                    <dd class="text-sm font-medium">{{ $this->formatValue($user->postal_code) }}</dd>
                </div>
                {{-- <div class="space-y-1">
                    <dt class="text-xs uppercase text-(--ee-app-muted)">{{ __('Endereço completo') }}
                    </dt>
                    <dd class="text-sm font-medium">
                        {{ $this->formatAddress([
                            'street' => $user->street,
                            'number' => $user->number,
                            'complement' => $user->complement,
                            'district' => $user->district,
                            'city' => $user->city,
                            'state' => $user->state,
                            'postal_code' => $user->postal_code,
                        ]) }}
                    </dd>
                </div> --}}
            </dl>
        </div>

        <div
            class="basis-120 flex-auto flex flex-col gap-6 rounded-2xl border border-(--ee-app-border) bg-(--ee-app-surface) p-6 lg:col-span-2">
            <flux:heading size="sm" level="2">{{ __('Sua Igreja') }}</flux:heading>

            <div class="flex flex-col gap-3">
                <flux:heading size="sm" level="3">
                    {{ $user->church?->name ?? __('Sem igreja vinculada') }}
                </flux:heading>
                <flux:text class="text-sm text-(--ee-app-muted)">
                    {{ $user->church?->pastor ? __('Pastor:') . ' ' . $user->church?->pastor : __('Pastor não informado') }}
                </flux:text>
                <flux:text class="text-sm text-(--ee-app-muted)">
                    {{ $this->formatAddress([
                        'street' => $user->church?->street,
                        'number' => $user->church?->number,
                        'complement' => $user->church?->complement,
                        'district' => $user->church?->district,
                        'city' => $user->church?->city,
                        'state' => $user->church?->state,
                        'postal_code' => $user->church?->postal_code,
                    ]) }}
                </flux:text>
            </div>

            <div class="flex justify-end">
                <flux:button variant="outline" wire:click="openChurchModal" data-test="change-church">
                    {{ __('Trocar igreja') }}
                </flux:button>
            </div>
        </div>

        <div class="basis-120 flex-auto">
            @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::twoFactorAuthentication()))
                <livewire:settings.two-factor :embedded="true" />
            @endif
        </div>

        <div class="basis-120 flex-auto">
            <livewire:settings.appearance :embedded="true" />
        </div>
        <div class="basis-120 flex-auto">
            <livewire:settings.delete-user-form :embedded="true" />
        </div>
    </div>

    <flux:modal name="profile-personal-modal" class="max-w-2xl" @close="closePersonalModal"
        wire:model="showPersonalModal">
        <form class="space-y-6" wire:submit="updatePersonal">
            <div class="space-y-2">
                <flux:heading size="lg">{{ __('Editar dados pessoais') }}</flux:heading>
                <flux:text class="text-sm text-(--ee-app-muted)">
                    {{ __('Atualize suas informações pessoais e contato principal.') }}
                </flux:text>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <flux:input wire:model="personal.name" :label="__('Nome')" required />
                <flux:input wire:model="personal.email" :label="__('Email')" type="email" required />
                <flux:input wire:model="personal.phone" :label="__('Telefone')" type="tel" />
                <flux:input wire:model="personal.birthdate" :label="__('Nascimento')" type="date" />
                <flux:select wire:model="personal.gender" :label="__('Gênero')" :placeholder="__('Selecione')">
                    <option value="Masculino">{{ __('Masculino') }}</option>
                    <option value="Feminino">{{ __('Feminino') }}</option>
                </flux:select>
                <flux:select wire:model="personal.pastor" :label="__('É um pastor')" :placeholder="__('Selecione')">
                    <option value="N">{{ __('No') }}</option>
                    <option value="Y">{{ __('Yes') }}</option>
                </flux:select>
            </div>

            <flux:textarea wire:model="personal.notes" :label="__('Observações')" rows="4" />

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
                <flux:heading size="lg">{{ __('Editar endereço') }}</flux:heading>
                <flux:text class="text-sm text-(--ee-app-muted)">
                    {{ __('Atualize o endereço de contato do usuário.') }}
                </flux:text>
            </div>

            <livewire:address-fields wire:model="address" title="Endereço do usuário" wire:key="profile-address" />

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
                    {{ __('Endereço atualizado.') }}
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
