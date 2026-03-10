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

    public function mount(): void
    {
        $this->selectedUserId = User::query()->orderBy('name')->value('id');

        if ($this->selectedUserId !== null) {
            $this->updatedSelectedUserId();
        }
    }

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

    public function getSelectedUserProperty(): ?User
    {
        if ($this->selectedUserId === null) {
            return null;
        }

        return User::query()->with('roles')->find($this->selectedUserId);
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

<div>
    <x-src.toolbar.header :title="__('Setup do sistema')"
        :description="__('Gerencie permissões de acesso e mantenha as funções dos usuários alinhadas com a operação do sistema.')"
        fixed-route-name="app.director.setup" />

    <x-src.toolbar.nav>
        <x-src.toolbar.button :href="route('app.director.dashboard')" :label="__('Dashboard')" icon="layout-grid"
            :tooltip="__('Voltar para o painel principal')" />
        <div class="ml-auto flex items-center gap-3 rounded-full bg-slate-900 px-4 py-2 text-xs font-semibold text-slate-100">
            <span>{{ __('Usuário selecionado') }}</span>
            <span class="rounded-full bg-white/10 px-3 py-1 text-amber-200">
                {{ $this->selectedUser?->name ?? __('Nenhum') }}
            </span>
        </div>
    </x-src.toolbar.nav>

    <section
        class="rounded-2xl border border-slate-200 bg-linear-to-br from-slate-50 via-white to-slate-100 p-5 shadow-lg sm:p-6">
        <div class="grid gap-6 xl:grid-cols-[minmax(0,1.3fr)_360px]">
            <div class="space-y-6">
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex flex-col gap-2 border-b border-slate-200 pb-4">
                        <h2 class="text-xl font-semibold text-slate-900" style="font-family: 'Cinzel', serif;">
                            {{ __('Configuração de acessos') }}
                        </h2>
                        <p class="text-sm text-slate-600">
                            {{ __('Selecione um usuário, revise o perfil encontrado e ajuste as funções liberadas para uso dentro da plataforma.') }}
                        </p>
                    </div>

                    <div class="mt-6 grid gap-4 lg:grid-cols-2">
                        <flux:input wire:model.live.debounce.300ms="search" :label="__('Buscar usuário')"
                            :placeholder="__('Digite nome ou e-mail')" />

                        <flux:select wire:model.live="selectedUserId" :label="__('Usuário')"
                            :placeholder="__('Selecione um usuário')">
                            @foreach ($this->users as $user)
                                <option value="{{ $user->id }}">
                                    {{ $user->name }} ({{ $user->email }})
                                </option>
                            @endforeach
                        </flux:select>
                    </div>

                    @error('selectedUserId')
                        <flux:text class="mt-3 text-sm text-red-600">{{ $message }}</flux:text>
                    @enderror

                    <div class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        @if ($this->selectedUser)
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                <div class="space-y-1">
                                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">
                                        {{ __('Perfil selecionado') }}
                                    </p>
                                    <h3 class="text-lg font-semibold text-slate-900">{{ $this->selectedUser->name }}</h3>
                                    <p class="text-sm text-slate-600">{{ $this->selectedUser->email }}</p>
                                </div>

                                <div class="rounded-full bg-sky-100 px-3 py-1 text-sm font-semibold text-sky-800">
                                    {{ __('Funções ativas: :count', ['count' => count($selectedRoleIds)]) }}
                                </div>
                            </div>
                        @else
                            <div class="rounded-xl border border-dashed border-slate-300 bg-white px-4 py-5 text-sm text-slate-500">
                                {{ __('Nenhum usuário encontrado para os filtros informados.') }}
                            </div>
                        @endif
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex flex-col gap-2 border-b border-slate-200 pb-4 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <h2 class="text-xl font-semibold text-slate-900" style="font-family: 'Cinzel', serif;">
                                {{ __('Funções disponíveis') }}
                            </h2>
                            <p class="text-sm text-slate-600">
                                {{ __('Marque apenas as funções necessárias para esse usuário. As alterações são aplicadas automaticamente.') }}
                            </p>
                        </div>

                        <div class="rounded-full bg-emerald-100 px-3 py-1 text-sm font-semibold text-emerald-800">
                            {{ __('Total: :count', ['count' => $this->roles->count()]) }}
                        </div>
                    </div>

                    <div class="mt-6 grid gap-3 md:grid-cols-2">
                        @foreach ($this->roles as $role)
                            <label wire:key="role-{{ $role->id }}"
                                class="flex items-start gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 transition hover:border-sky-200 hover:bg-sky-50/60">
                                <flux:checkbox wire:model.live="selectedRoleIds" value="{{ $role->id }}"
                                    :disabled="$selectedUserId === null" />
                                <div class="space-y-1">
                                    <span class="block text-sm font-semibold text-slate-900">{{ $role->name }}</span>
                                    <span class="block text-xs text-slate-500">
                                        {{ __('Permissão liberada para navegação e uso das rotinas vinculadas a esta função.') }}
                                    </span>
                                </div>
                            </label>
                        @endforeach
                    </div>

                    @error('selectedRoleIds.*')
                        <flux:text class="mt-3 text-sm text-red-600">{{ $message }}</flux:text>
                    @enderror

                    <div class="mt-6 flex items-center justify-between gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3">
                        <p class="text-sm text-emerald-800">
                            {{ __('As permissões são sincronizadas em tempo real assim que uma função é marcada ou removida.') }}
                        </p>

                        <x-app.action-message on="roles-updated">
                            {{ __('Configurações atualizadas.') }}
                        </x-app.action-message>
                    </div>
                </div>
            </div>

            <aside class="space-y-6">
                <div class="rounded-2xl border border-slate-200 bg-slate-950 p-6 text-slate-100 shadow-lg">
                    <p class="text-xs font-semibold uppercase tracking-[0.25em] text-amber-300">
                        {{ __('Resumo operacional') }}
                    </p>
                    <h2 class="mt-3 text-2xl font-semibold" style="font-family: 'Cinzel', serif;">
                        {{ __('Controle central de acessos') }}
                    </h2>
                    <p class="mt-3 text-sm leading-6 text-slate-300">
                        {{ __('Use esta área para manter o setup de perfis consistente, evitar acessos indevidos e acelerar o suporte interno da equipe.') }}
                    </p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h3 class="text-lg font-semibold text-slate-900">{{ __('Boas práticas') }}</h3>
                    <div class="mt-4 space-y-3 text-sm text-slate-600">
                        <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                            {{ __('Revise os acessos sempre que um usuário mudar de responsabilidade ou ministério.') }}
                        </div>
                        <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                            {{ __('Evite conceder funções além do necessário para reduzir risco operacional.') }}
                        </div>
                        <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                            {{ __('Após qualquer ajuste, confirme com o usuário se a navegação esperada foi liberada.') }}
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </section>
</div>
