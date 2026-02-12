<div class="flex w-full flex-col gap-8">
    <div
        class="flex flex-col gap-6 rounded-3xl border border-[color:var(--ee-app-border)] bg-[color:var(--ee-app-surface)] p-6 shadow-sm">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                <flux:avatar :name="$user->name"
                    :src="$user->profile_photo_path ? asset('storage/' . $user->profile_photo_path) : null"
                    :initials="$user->initials()" size="2xl" />

                <div class="flex flex-col gap-2">
                    <flux:heading size="xl" level="1">
                        <div class="flex items-center gap-1">
                            @if ($this->isPastor)
                                <flux:badge color="slate">Pastor</flux:badge>
                            @endif
                            {{ $user->name }}
                        </div>
                    </flux:heading>
                    <flux:text class="text-sm text-[color:var(--ee-app-muted)]">{{ $user->email }}</flux:text>

                    <div class="flex flex-wrap items-center gap-2">

                        @foreach ($user->roles as $role)
                            <flux:badge color="blue" wire:key="role-pill-{{ $role->id }}">
                                {{ $role->name }}
                            </flux:badge>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <flux:button variant="outline" wire:click="$set('showPasswordModal', true)"
                    data-test="profile-change-password">
                    {{ __('Trocar senha') }}
                </flux:button>
            </div>
        </div>

    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <div
            class="flex flex-col gap-6 rounded-2xl border border-[color:var(--ee-app-border)] bg-[color:var(--ee-app-surface)] p-6">
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
                {{-- <div class="space-y-1">
                    <dt class="text-xs uppercase text-[color:var(--ee-app-muted)]">{{ __('Nome') }}</dt>
                    <dd class="text-sm font-medium">{{ $this->formatValue($user->name) }}</dd>
                </div> --}}
                <div class="space-y-1">
                    <dt class="text-xs uppercase text-[color:var(--ee-app-muted)]">{{ __('Email') }}</dt>
                    <dd class="text-sm font-medium">
                        {{ $this->formatValue($user->email) }}
                        {{-- <a class="text-sm text-amber-600 font-light"
                            href="#">{{ $user->email_verified_at ? __('Verificado') : __('Não verificado') }}</a> --}}
                    </dd>
                </div>
                <div class="space-y-1">
                    <dt class="text-xs uppercase text-[color:var(--ee-app-muted)]">{{ __('Telefone') }}</dt>
                    <dd class="text-sm font-medium">{{ $this->formatValue($user->phone) }}</dd>
                </div>
                <div class="space-y-1">
                    <dt class="text-xs uppercase text-[color:var(--ee-app-muted)]">{{ __('Nascimento') }}</dt>
                    <dd class="text-sm font-medium">{{ $this->formatValue($user->birthdate) }}</dd>
                </div>
                <div class="space-y-1">
                    <dt class="text-xs uppercase text-[color:var(--ee-app-muted)]">{{ __('Gênero') }}</dt>
                    {{-- <dd class="text-sm font-medium">{{ $this->formatValue($user->gender) }}</dd> --}}
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
                {{-- <div class="space-y-1">
                    <dt class="text-xs uppercase text-[color:var(--ee-app-muted)]">{{ __('Foto de perfil') }}</dt>
                    <dd class="text-sm font-medium">
                        {{ $this->formatValue($user->profile_photo_path) }}
                    </dd>
                </div> --}}
                {{-- <div class="space-y-1">
                    <dt class="text-xs uppercase text-[color:var(--ee-app-muted)]">{{ __('Email verificado em') }}
                    </dt>
                    <dd class="text-sm font-medium">{{ $this->formatDateTime($user->email_verified_at) }}</dd>
                </div> --}}
            </dl>

            <div class="rounded-2xl border border-dashed border-[color:var(--ee-app-border)] p-4">
                <flux:text class="text-xs uppercase text-[color:var(--ee-app-muted)]">{{ __('Observações') }}
                </flux:text>
                <flux:text class="mt-2 text-sm text-[color:var(--ee-app-text)] whitespace-pre-line">
                    {{ $this->formatValue($user->notes) }}
                </flux:text>
            </div>
        </div>

        <div
            class="flex flex-col gap-6 rounded-2xl border border-[color:var(--ee-app-border)] bg-[color:var(--ee-app-surface)] p-6">
            <div class="flex items-center justify-between gap-4">
                <flux:heading size="sm" level="2">{{ __('Endereço do usuário') }}</flux:heading>
                <flux:button variant="outline" wire:click="$set('showAddressModal', true)"
                    data-test="profile-edit-address">
                    {{ __('Editar endereço') }}
                </flux:button>
            </div>

            <dl class="grid gap-4 sm:grid-cols-2">
                <div class="space-y-1">
                    <dt class="text-xs uppercase text-[color:var(--ee-app-muted)]">{{ __('Logradouro') }}</dt>
                    <dd class="text-sm font-medium">{{ $this->formatValue($user->street) }}</dd>
                </div>
                <div class="space-y-1">
                    <dt class="text-xs uppercase text-[color:var(--ee-app-muted)]">{{ __('Número') }}</dt>
                    <dd class="text-sm font-medium">{{ $this->formatValue($user->number) }}</dd>
                </div>
                <div class="space-y-1">
                    <dt class="text-xs uppercase text-[color:var(--ee-app-muted)]">{{ __('Complemento') }}</dt>
                    <dd class="text-sm font-medium">{{ $this->formatValue($user->complement) }}</dd>
                </div>
                <div class="space-y-1">
                    <dt class="text-xs uppercase text-[color:var(--ee-app-muted)]">{{ __('Bairro') }}</dt>
                    <dd class="text-sm font-medium">{{ $this->formatValue($user->district) }}</dd>
                </div>
                <div class="space-y-1">
                    <dt class="text-xs uppercase text-[color:var(--ee-app-muted)]">{{ __('Cidade') }}</dt>
                    <dd class="text-sm font-medium">{{ $this->formatValue($user->city) }}</dd>
                </div>
                <div class="space-y-1">
                    <dt class="text-xs uppercase text-[color:var(--ee-app-muted)]">{{ __('UF') }}</dt>
                    <dd class="text-sm font-medium">{{ $this->formatValue($user->state) }}</dd>
                </div>
                <div class="space-y-1">
                    <dt class="text-xs uppercase text-[color:var(--ee-app-muted)]">{{ __('CEP') }}</dt>
                    <dd class="text-sm font-medium">{{ $this->formatValue($user->postal_code) }}</dd>
                </div>
                <div class="space-y-1">
                    <dt class="text-xs uppercase text-[color:var(--ee-app-muted)]">{{ __('Endereço completo') }}
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
                </div>
            </dl>
        </div>

        <div
            class="flex flex-col gap-6 rounded-2xl border border-[color:var(--ee-app-border)] bg-[color:var(--ee-app-surface)] p-6 lg:col-span-2">
            <flux:heading size="sm" level="2">{{ __('Sua Igreja') }}</flux:heading>

            <div class="flex flex-col gap-3">
                <flux:heading size="sm" level="3">
                    {{ $user->church?->name ?? __('Sem igreja vinculada') }}
                </flux:heading>
                <flux:text class="text-sm text-[color:var(--ee-app-muted)]">
                    {{ $user->church?->pastor ? __('Pastor:') . ' ' . $user->church?->pastor : __('Pastor não informado') }}
                </flux:text>
                <flux:text class="text-sm text-[color:var(--ee-app-muted)]">
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

        <div
            class="flex flex-col gap-6 rounded-2xl border border-[color:var(--ee-app-border)] bg-[color:var(--ee-app-surface)] p-6">
            <flux:heading size="sm" level="2">{{ __('Funções e ensino') }}</flux:heading>

            <div class="flex flex-col gap-4">
                <div class="flex items-center gap-2">
                    <flux:badge color="{{ $user->hasRole('Teacher') ? 'emerald' : 'zinc' }}">
                        {{ $user->hasRole('Teacher') ? __('Professor ativo') : __('Não é professor') }}
                    </flux:badge>
                    <flux:text class="text-sm text-[color:var(--ee-app-muted)]">
                        {{ __('Funções:') }} {{ $user->roles->pluck('name')->implode(', ') ?: __('Sem funções') }}
                    </flux:text>
                </div>

                <div class="flex flex-col gap-3">
                    <flux:text class="text-xs uppercase text-[color:var(--ee-app-muted)]">
                        {{ __('Cursos em que atua como professor') }}
                    </flux:text>
                    @forelse ($user->courseAsTeacher as $course)
                        <div class="flex items-center justify-between gap-4 rounded-xl border border-dashed border-[color:var(--ee-app-border)] p-3"
                            wire:key="course-teacher-{{ $course->id }}">
                            <flux:text class="text-sm font-medium">{{ $course->name }}</flux:text>
                            <flux:badge color="zinc">{{ $course->pivot?->status ?? __('Status não informado') }}
                            </flux:badge>
                        </div>
                    @empty
                        <flux:text class="text-sm text-[color:var(--ee-app-muted)]">
                            {{ __('Nenhum curso vinculado como professor.') }}
                        </flux:text>
                    @endforelse
                </div>
            </div>
        </div>

        <div
            class="flex flex-col gap-6 rounded-2xl border border-[color:var(--ee-app-border)] bg-[color:var(--ee-app-surface)] p-6">
            <flux:heading size="sm" level="2">{{ __('Treinamentos como professor titular') }}
            </flux:heading>

            <div class="flex flex-col gap-3">
                @forelse ($user->trainingsAsTeacher as $training)
                    <div class="rounded-xl border border-dashed border-[color:var(--ee-app-border)] p-4"
                        wire:key="training-teacher-{{ $training->id }}">
                        <div class="flex flex-col gap-2">
                            <flux:heading size="sm" level="3">
                                {{ $training->course?->name ?? __('Treinamento sem curso') }}
                            </flux:heading>
                            <flux:text class="text-xs text-[color:var(--ee-app-muted)]">
                                {{ __('Igreja:') }} {{ $training->church?->name ?? __('Não informada') }}
                            </flux:text>
                            <flux:text class="text-xs text-[color:var(--ee-app-muted)]">
                                {{ __('Status:') }} {{ $training->statusKey() }}
                            </flux:text>
                        </div>
                    </div>
                @empty
                    <flux:text class="text-sm text-[color:var(--ee-app-muted)]">
                        {{ __('Nenhum treinamento como professor titular.') }}
                    </flux:text>
                @endforelse
            </div>
        </div>

        <div
            class="flex flex-col gap-6 rounded-2xl border border-[color:var(--ee-app-border)] bg-[color:var(--ee-app-surface)] p-6 lg:col-span-2">
            <flux:heading size="sm" level="2">{{ __('Treinamentos como aluno') }}</flux:heading>

            <div class="grid gap-4 md:grid-cols-2">
                @forelse ($user->trainingsAsStudent as $training)
                    <div class="rounded-xl border border-dashed border-[color:var(--ee-app-border)] p-4"
                        wire:key="training-student-{{ $training->id }}">
                        <div class="flex flex-col gap-2">
                            <flux:heading size="sm" level="3">
                                {{ $training->course?->name ?? __('Treinamento sem curso') }}
                            </flux:heading>
                            <flux:text class="text-xs text-[color:var(--ee-app-muted)]">
                                {{ __('Igreja:') }} {{ $training->church?->name ?? __('Não informada') }}
                            </flux:text>
                            <div class="flex flex-wrap items-center gap-2 text-xs text-[color:var(--ee-app-muted)]">
                                <flux:badge color="{{ $training->pivot?->accredited ? 'emerald' : 'zinc' }}">
                                    {{ $training->pivot?->accredited ? __('Credenciado') : __('Não credenciado') }}
                                </flux:badge>
                                <flux:badge color="{{ $training->pivot?->kit ? 'emerald' : 'zinc' }}">
                                    {{ $training->pivot?->kit ? __('Kit recebido') : __('Sem kit') }}
                                </flux:badge>
                                <flux:badge color="{{ $training->pivot?->payment ? 'emerald' : 'zinc' }}">
                                    {{ $training->pivot?->payment ? __('Pagamento OK') : __('Pagamento pendente') }}
                                </flux:badge>
                            </div>
                        </div>
                    </div>
                @empty
                    <flux:text class="text-sm text-[color:var(--ee-app-muted)]">
                        {{ __('Nenhum treinamento como aluno.') }}
                    </flux:text>
                @endforelse
            </div>
        </div>

        <div
            class="flex flex-col gap-6 rounded-2xl border border-[color:var(--ee-app-border)] bg-[color:var(--ee-app-surface)] p-6">
            <flux:heading size="sm" level="2">{{ __('Segurança e acesso') }}</flux:heading>

            <dl class="grid gap-4 sm:grid-cols-2">
                <div class="space-y-1">
                    <dt class="text-xs uppercase text-[color:var(--ee-app-muted)]">{{ __('Senha') }}</dt>
                    <dd class="text-sm font-medium">{{ __('Oculto') }}</dd>
                </div>
                <div class="space-y-1">
                    <dt class="text-xs uppercase text-[color:var(--ee-app-muted)]">{{ __('Remember Token') }}
                    </dt>
                    <dd class="text-sm font-medium">
                        {{ $user->remember_token ? __('Presente') : __('Vazio') }}
                    </dd>
                </div>
                <div class="space-y-1">
                    <dt class="text-xs uppercase text-[color:var(--ee-app-muted)]">{{ __('2FA configurado') }}
                    </dt>
                    <dd class="text-sm font-medium">
                        {{ $user->two_factor_secret ? __('Sim') : __('Não') }}
                    </dd>
                </div>
                <div class="space-y-1">
                    <dt class="text-xs uppercase text-[color:var(--ee-app-muted)]">{{ __('2FA confirmado em') }}
                    </dt>
                    <dd class="text-sm font-medium">{{ $this->formatDateTime($user->two_factor_confirmed_at) }}
                    </dd>
                </div>
                <div class="space-y-1">
                    <dt class="text-xs uppercase text-[color:var(--ee-app-muted)]">{{ __('Recovery codes') }}
                    </dt>
                    <dd class="text-sm font-medium">
                        {{ $user->two_factor_recovery_codes ? __('Gerados') : __('Não gerados') }}
                    </dd>
                </div>
                <div class="space-y-1">
                    <dt class="text-xs uppercase text-[color:var(--ee-app-muted)]">{{ __('Secret') }}</dt>
                    <dd class="text-sm font-medium">
                        {{ $user->two_factor_secret ? __('Armazenado') : __('Não armazenado') }}
                    </dd>
                </div>
            </dl>
        </div>

        @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::twoFactorAuthentication()))
            <livewire:settings.two-factor :embedded="true" />
        @endif

        <livewire:settings.appearance :embedded="true" />

        <div
            class="flex flex-col gap-6 rounded-2xl border border-[color:var(--ee-app-border)] bg-[color:var(--ee-app-surface)] p-6">
            <flux:heading size="sm" level="2">{{ __('Dados do sistema') }}</flux:heading>

            <dl class="grid gap-4 sm:grid-cols-2">
                <div class="space-y-1">
                    <dt class="text-xs uppercase text-[color:var(--ee-app-muted)]">{{ __('Criado em') }}</dt>
                    <dd class="text-sm font-medium">{{ $this->formatDateTime($user->created_at) }}</dd>
                </div>
                <div class="space-y-1">
                    <dt class="text-xs uppercase text-[color:var(--ee-app-muted)]">{{ __('Atualizado em') }}</dt>
                    <dd class="text-sm font-medium">{{ $this->formatDateTime($user->updated_at) }}</dd>
                </div>
                <div class="space-y-1">
                    <dt class="text-xs uppercase text-[color:var(--ee-app-muted)]">{{ __('Church ID') }}</dt>
                    <dd class="text-sm font-medium">{{ $this->formatValue($user->church_id) }}</dd>
                </div>
                <div class="space-y-1">
                    <dt class="text-xs uppercase text-[color:var(--ee-app-muted)]">{{ __('Church Temp ID') }}
                    </dt>
                    <dd class="text-sm font-medium">{{ $this->formatValue($user->church_temp_id) }}</dd>
                </div>
            </dl>
        </div>

        <div class="lg:col-span-2">
            <livewire:settings.delete-user-form :embedded="true" />
        </div>
    </div>

    <flux:modal name="profile-personal-modal" class="max-w-2xl" @close="closePersonalModal"
        wire:model="showPersonalModal">
        <form class="space-y-6" wire:submit="updatePersonal">
            <div class="space-y-2">
                <flux:heading size="lg">{{ __('Editar dados pessoais') }}</flux:heading>
                <flux:text class="text-sm text-[color:var(--ee-app-muted)]">
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
                <flux:text class="text-sm text-[color:var(--ee-app-muted)]">
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
                <flux:text class="text-sm text-[color:var(--ee-app-muted)]">
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
