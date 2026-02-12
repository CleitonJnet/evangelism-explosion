<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Collection;
use Livewire\Volt\Component;

new class extends Component {
    public string $search = '';
    public ?int $selectedUserId = null;
    public array $selectedRoleIds = [];
    public bool $isHydratingRoles = false;

    public function getUsersProperty(): Collection
    {
        return User::query()
            ->when(
                $this->search !== '',
                fn($query) => $query->where(function ($query): void {
                    $query->where('name', 'like', "%{$this->search}%")->orWhere('email', 'like', "%{$this->search}%");
                }),
            )
            ->orderBy('name')
            ->limit(15)
            ->get();
    }

    public function getRolesProperty(): Collection
    {
        return Role::query()->orderBy('id')->get();
    }

    public function updatedSearch(): void
    {
        $this->selectedUserId = $this->users->first()?->id;
        $this->updatedSelectedUserId();
    }

    public function updatedSelectedUserId(): void
    {
        $user = $this->selectedUserId ? User::query()->with('roles')->find($this->selectedUserId) : null;

        $this->isHydratingRoles = true;
        $this->selectedRoleIds = $user?->roles->pluck('id')->all() ?? [];
        $this->isHydratingRoles = false;
    }

    public function updatedSelectedRoleIds(): void
    {
        if ($this->isHydratingRoles) {
            return;
        }

        if (!$this->selectedUserId) {
            return;
        }

        $user = User::query()->find($this->selectedUserId);

        if (!$user) {
            return;
        }

        $roleIds = Role::query()->whereIn('id', $this->selectedRoleIds)->pluck('id')->all();

        $this->selectedRoleIds = $roleIds;

        $user->roles()->sync($roleIds);

        $this->dispatch('roles-updated');
    }
}; ?>

<section class="w-full">
    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">{{ __('Setup do sistema') }}</flux:heading>
        <flux:subheading size="lg" class="mb-6">
            {{ __('Configure as regras de acesso e ajuste de funções por usuário.') }}
        </flux:subheading>
        <flux:separator variant="subtle" />
    </div>

    <div class="flex w-full max-w-3xl flex-col gap-6">
        <div class="rounded-2xl border border-[color:var(--ee-app-border)] bg-[color:var(--ee-app-surface)] p-6">
            <flux:heading size="sm" level="2">{{ __('Configurações de acesso') }}</flux:heading>
            <flux:text class="mt-2 text-sm text-[color:var(--ee-app-muted)]">
                {{ __('Selecione um usuário e defina as funções que ele pode acessar.') }}
            </flux:text>

            <div class="mt-6 grid gap-4">
                <flux:input wire:model.live.debounce.300ms="search" :label="__('Buscar usuário')"
                    :placeholder="__('Digite o nome ou e-mail')" />

                <flux:select wire:model.live="selectedUserId" :label="__('Usuário')"
                    :placeholder="__('Selecione um usuário')">
                    @foreach ($this->users as $user)
                        <option value="{{ $user->id }}">
                            {{ $user->name }} ({{ $user->email }})
                        </option>
                    @endforeach
                </flux:select>

                @error('selectedUserId')
                    <flux:text class="text-sm text-red-600 dark:text-red-400">{{ $message }}</flux:text>
                @enderror
            </div>
        </div>

        <div class="rounded-2xl border border-[color:var(--ee-app-border)] bg-[color:var(--ee-app-surface)] p-6">
            <flux:heading size="sm" level="2">{{ __('Funções disponíveis') }}</flux:heading>
            <flux:text class="mt-2 text-sm text-[color:var(--ee-app-muted)]">
                {{ __('Marque as funções que o usuário selecionado poderá utilizar.') }}
            </flux:text>
            <flux:text class="mt-1 text-xs text-[color:var(--ee-app-muted)]">
                {{ __('As alterações são salvas automaticamente.') }}
            </flux:text>

            <div class="mt-4 grid gap-3">
                @foreach ($this->roles as $role)
                    <flux:checkbox wire:model.live="selectedRoleIds" value="{{ $role->id }}"
                        wire:key="role-{{ $role->id }}" :label="$role->name"
                        :disabled="$selectedUserId === null" />
                @endforeach
            </div>

            @error('selectedRoleIds.*')
                <flux:text class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</flux:text>
            @enderror

            <div class="mt-6">
                <x-app.action-message on="roles-updated">
                    {{ __('Configurações atualizadas.') }}
                </x-app.action-message>
            </div>
        </div>
    </div>
</section>
