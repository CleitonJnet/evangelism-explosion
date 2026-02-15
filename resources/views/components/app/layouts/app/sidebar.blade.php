<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('components.app.partials.head')
</head>

<body class="min-h-screen bg-slate-400 select-none">
    <flux:sidebar sticky collapsible="mobile" class="border-e border-sky-900/60 bg-sky-950 text-slate-100">
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

                $hasRoleLinks =
                    auth()->user()->can('access-board') ||
                    auth()->user()->can('access-director') ||
                    auth()->user()->can('access-teacher') ||
                    auth()->user()->can('access-facilitator') ||
                    auth()->user()->can('access-fieldworker') ||
                    auth()->user()->can('access-mentor') ||
                    auth()->user()->can('access-student');
            @endphp

            @if ($currentRoleKey && isset($roleMenus[$currentRoleKey]))
                <x-dynamic-component :component="$roleMenus[$currentRoleKey]" />
            @endif

            @if (!$currentRoleKey && $hasRoleLinks)
                <flux:sidebar.group :heading="__('Funções')" class="grid [&>div>div]:text-slate-300/80">
                    @can('access-board')
                        <flux:sidebar.item :href="route('app.board.dashboard')" :current="request()->routeIs('app.board.*')"
                            wire:navigate
                            class="text-slate-100/90 hover:text-white hover:bg-white/10 data-current:text-amber-200 data-current:bg-white/10 data-current:border-amber-200/30 border border-transparent">
                            {{ __('Board Member') }}
                        </flux:sidebar.item>
                    @endcan

                    @can('access-director')
                        <flux:sidebar.item :href="route('app.director.dashboard')"
                            :current="request()->routeIs('app.director.*')" wire:navigate
                            class="text-slate-100/90 hover:text-white hover:bg-white/10 data-current:text-amber-200 data-current:bg-white/10 data-current:border-amber-200/30 border border-transparent">
                            {{ __('Diretor Nacional') }}
                        </flux:sidebar.item>
                    @endcan

                    @can('access-teacher')
                        <flux:sidebar.item :href="route('app.teacher.dashboard')"
                            :current="request()->routeIs('app.teacher.*')" wire:navigate
                            class="text-slate-100/90 hover:text-white hover:bg-white/10 data-current:text-amber-200 data-current:bg-white/10 data-current:border-amber-200/30 border border-transparent">
                            {{ __('Teacher') }}
                        </flux:sidebar.item>
                    @endcan

                    @can('access-facilitator')
                        <flux:sidebar.item :href="route('app.facilitator.dashboard')"
                            :current="request()->routeIs('app.facilitator.*')" wire:navigate
                            class="text-slate-100/90 hover:text-white hover:bg-white/10 data-current:text-amber-200 data-current:bg-white/10 data-current:border-amber-200/30 border border-transparent">
                            {{ __('Facilitator') }}
                        </flux:sidebar.item>
                    @endcan

                    @can('access-fieldworker')
                        <flux:sidebar.item :href="route('app.fieldworker.dashboard')"
                            :current="request()->routeIs('app.fieldworker.*')" wire:navigate
                            class="text-slate-100/90 hover:text-white hover:bg-white/10 data-current:text-amber-200 data-current:bg-white/10 data-current:border-amber-200/30 border border-transparent">
                            {{ __('Field Worker') }}
                        </flux:sidebar.item>
                    @endcan

                    @can('access-mentor')
                        <flux:sidebar.item :href="route('app.mentor.dashboard')"
                            :current="request()->routeIs('app.mentor.*')" wire:navigate
                            class="text-slate-100/90 hover:text-white hover:bg-white/10 data-current:text-amber-200 data-current:bg-white/10 data-current:border-amber-200/30 border border-transparent">
                            {{ __('Mentor') }}
                        </flux:sidebar.item>
                    @endcan

                    @can('access-student')
                        <flux:sidebar.item :href="route('app.student.dashboard')"
                            :current="request()->routeIs('app.student.*')" wire:navigate
                            class="text-slate-200/90 hover:text-white hover:bg-white/10 data-current:text-amber-200 data-current:bg-white/10 data-current:border-amber-200/30 border border-transparent">
                            {{ __('Student') }}
                        </flux:sidebar.item>
                    @endcan
                </flux:sidebar.group>
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
                        <a class="flex items-center gap-2 px-1 py-1.5 text-start text-sm"
                            href="{{ route('app.profile') }}" wire:navigate data-test="mobile-profile-link">
                            <flux:avatar :name="auth()->user()->name" :initials="auth()->user()->initials()" />

                            <div class="grid flex-1 text-start text-sm leading-tight">
                                <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                            </div>
                        </a>
                    </div>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <flux:menu.radio.group>
                    @can('access-director')
                        <flux:menu.item :href="route('app.director.setup')" icon="adjustments-horizontal" wire:navigate>
                            {{ __('Setup do sistema') }}
                        </flux:menu.item>
                    @endcan
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

    @livewire('shared.church-link-modal')

    @include('components.layouts.bottom.app-scripts')

    @stack('js')
</body>

</html>
