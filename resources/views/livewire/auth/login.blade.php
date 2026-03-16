@php
    $selectedPortal = \App\Support\Portals\Enums\Portal::tryFrom((string) request()->string('portal'));
@endphp

<x-app.layouts.auth>
    <div class="flex flex-col gap-6">
        <x-app.auth-header
            :title="$selectedPortal ? __('Entrar em :portal', ['portal' => $selectedPortal->label()]) : __('Log in to your account')"
            :description="$selectedPortal
                ? __('Use suas credenciais para acessar o portal correto da plataforma ministerial.')
                : __('Enter your email and password below to log in')" />

        <!-- Session Status -->
        <x-app.auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-6">
            @csrf

            @if ($selectedPortal)
                <input type="hidden" name="portal" value="{{ $selectedPortal->value }}">

                <div class="rounded-2xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-900">
                    {{ __('Entrada direcionada para :portal. Depois do login, vamos te levar para a area correta se o seu perfil tiver acesso.', ['portal' => $selectedPortal->label()]) }}
                </div>
            @endif

            <!-- Email Address -->
            <flux:input name="email" :label="__('Email address')" :value="old('email')" type="email" required
                autofocus autocomplete="email" placeholder="email@example.com" />

            <!-- Password -->
            <div class="relative">
                <flux:input name="password" :label="__('Password')" type="password" required
                    autocomplete="current-password" :placeholder="__('Password')" />

                @if (Route::has('password.request'))
                    <flux:link class="absolute top-0 text-sm end-0" :href="route('password.request')" wire:navigate>
                        {{ __('Forgot your password?') }}
                    </flux:link>
                @endif
            </div>

            <div class="flex items-center justify-end">
                <flux:button variant="primary" type="submit" class="w-full" data-test="login-button">
                    {{ __('Log in') }}
                </flux:button>
            </div>

            <div class="rounded-2xl border border-neutral-200 bg-neutral-50 px-4 py-4 text-sm text-neutral-700">
                <div class="font-semibold text-neutral-900">{{ __('Entradas rápidas') }}</div>
                <div class="mt-2 flex flex-wrap gap-2">
                    <a href="{{ route('web.portals.show', 'base') }}" class="rounded-full border border-neutral-200 bg-white px-3 py-1.5 font-medium transition hover:border-sky-300 hover:bg-sky-50">
                        {{ __('Base e Treinamentos') }}
                    </a>
                    <a href="{{ route('web.portals.show', 'staff') }}" class="rounded-full border border-neutral-200 bg-white px-3 py-1.5 font-medium transition hover:border-sky-300 hover:bg-sky-50">
                        {{ __('Staff / Governança') }}
                    </a>
                    <a href="{{ route('web.portals.show', 'student') }}" class="rounded-full border border-neutral-200 bg-white px-3 py-1.5 font-medium transition hover:border-sky-300 hover:bg-sky-50">
                        {{ __('Aluno') }}
                    </a>
                </div>
            </div>
        </form>
    </div>
</x-app.layouts.auth>
