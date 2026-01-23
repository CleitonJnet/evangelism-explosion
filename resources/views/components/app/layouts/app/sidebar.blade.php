<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('components.app.partials.head')
</head>

<body class="min-h-screen bg-[color:var(--ee-app-bg)] text-[color:var(--ee-app-text)]">
    <flux:sidebar sticky collapsible="mobile"
        class="border-e border-[color:var(--ee-app-border)] bg-[color:var(--ee-app-surface)]">
        <flux:sidebar.header>
            <x-app.app-logo :sidebar="true" href="{{ route('app.start') }}" wire:navigate />
            <flux:sidebar.collapse class="lg:hidden" />
        </flux:sidebar.header>

        <x-src.line-theme />

        <flux:sidebar.nav>
            <x-app.desktop-roles-menu class="hidden lg:block" />

            @php
                $currentRoleKey = match (true) {
                    request()->routeIs('app.board.*') => 'board',
                    request()->routeIs('app.director.*') => 'director',
                    request()->routeIs('app.teacher.*') => 'teacher',
                    request()->routeIs('app.facilitator.*') => 'facilitator',
                    request()->routeIs('app.fieldworker.*') => 'fieldworker',
                    request()->routeIs('app.mentor.*') => 'mentor',
                    request()->routeIs('app.student.*') => 'student',
                    default => null,
                };

                $roleMenus = [
                    'board' => 'app.menu-roles.board',
                    'director' => 'app.menu-roles.director',
                    'teacher' => 'app.menu-roles.teacher',
                    'facilitator' => 'app.menu-roles.facilitator',
                    'fieldworker' => 'app.menu-roles.fieldworker',
                    'mentor' => 'app.menu-roles.mentor',
                    'student' => 'app.menu-roles.student',
                ];
            @endphp

            @if ($currentRoleKey && isset($roleMenus[$currentRoleKey]))
                <x-dynamic-component :component="$roleMenus[$currentRoleKey]" />
            @endif

        </flux:sidebar.nav>

        <flux:spacer />

        <x-app.desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
    </flux:sidebar>


    <!-- Mobile User Menu -->
    <flux:header class="lg:hidden">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

        <flux:spacer />

        <flux:dropdown position="top" align="end">
            <flux:profile :initials="auth()->user()->initials()" icon-trailing="chevron-down" />

            <flux:menu>
                <flux:menu.radio.group>
                    <div class="p-0 text-sm font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                            <flux:avatar :name="auth()->user()->name" :initials="auth()->user()->initials()" />

                            <div class="grid flex-1 text-start text-sm leading-tight">
                                <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                            </div>
                        </div>
                    </div>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <flux:menu.radio.group>
                    @can('access-director')
                        <flux:menu.item :href="route('app.director.setup')" icon="adjustments-horizontal" wire:navigate>
                            {{ __('Setup do sistema') }}
                        </flux:menu.item>
                    @endcan

                    <flux:menu.item :href="route('app.profile.edit')" icon="cog" wire:navigate>
                        {{ __('Configurações do usuário') }}
                    </flux:menu.item>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle"
                        class="w-full cursor-pointer" data-test="logout-button">
                        {{ __('Log Out') }}
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:header>

    {{ $slot }}

    @include('components.layouts.bottom.app-scripts')
</body>

</html>
