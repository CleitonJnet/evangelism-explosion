<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('components.app.partials.head')
</head>

<body class="min-h-screen bg-slate-400 select-none">
    @php
        $roleLabels = [
            'board' => __('Board'),
            'director' => __('Diretoria'),
            'teacher' => __('Teacher'),
            'facilitator' => __('Facilitador'),
            'fieldworker' => __('Campo'),
            'mentor' => __('Mentor'),
            'student' => __('Aluno'),
        ];

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

        $roleHomeRoutes = [
            'board' => 'app.board.dashboard',
            'director' => 'app.director.dashboard',
            'teacher' => 'app.teacher.dashboard',
            'facilitator' => 'app.facilitator.dashboard',
            'fieldworker' => 'app.fieldworker.dashboard',
            'mentor' => 'app.mentor.dashboard',
            'student' => 'app.student.dashboard',
        ];

        $currentRoleHomeRoute = $currentRoleKey && isset($roleHomeRoutes[$currentRoleKey])
            ? route($roleHomeRoutes[$currentRoleKey])
            : route('app.start');
    @endphp

    <flux:sidebar sticky collapsible="mobile" class="border-e border-sky-900/60 bg-sky-950 text-slate-100 z-9999!">
        <flux:sidebar.header>
            <x-app.app-logo :sidebar="true" href="{{ $currentRoleHomeRoute }}" wire:navigate />
        </flux:sidebar.header>

        <x-src.line-theme />

        <flux:sidebar.nav>
            @php
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
    <div id="app-mobile-header-shell" class="lg:hidden">
        <flux:header id="app-mobile-header"
            class="ee-mobile-header px-0! border-b border-amber-400/35 bg-sky-950 text-slate-100 shadow-[0_18px_40px_-28px_rgba(2,6,23,0.9)]">
            <div class="flex w-full items-center justify-between gap-3 px-3 py-2 sm:px-4 md:px-5">
                <a href="{{ $currentRoleHomeRoute }}" wire:navigate class="flex min-w-0 items-center gap-3">
                    <img src="{{ asset('images/logo/ee-white.webp') }}" class="h-9 w-auto nav-iconshadow"
                        alt="{{ __('Evangelismo Explosivo') }}">

                    <div class="min-w-0 leading-tight">
                        <div
                            class="truncate text-[0.62rem] font-semibold uppercase tracking-[0.24em] text-slate-200/70">
                            {{ __('Evangelismo Explosivo') }}
                        </div>
                        <div class="truncate text-sm text-white nav-cinzel">
                            {{ $roleLabels[$currentRoleKey] ?? __('Plataforma') }}
                        </div>
                    </div>
                </a>

                <flux:dropdown position="top" align="end">
                    <flux:profile
                        class="ee-mobile-profile-trigger rounded-xl border border-white/10 bg-white/8 px-0.5 py-0.5 text-slate-100 shadow-[0_12px_22px_-18px_rgba(2,6,23,0.85)] hover:border-amber-300/45 hover:bg-white/12 [&_span]:text-slate-100 [&_span]:group-hover:text-white [&_svg]:text-amber-200"
                        avatar:class="bg-amber-500 text-amber-950 after:inset-ring-sky-950/10"
                        avatar:src="{{ auth()->user()->profile_photo_url }}" :initials="auth()->user()->initials()"
                        icon-trailing="chevron-down" />

                    <flux:menu>
                        <flux:menu.radio.group>
                            <div class="p-0 text-sm font-normal">
                                <a class="flex items-center gap-2 px-1 py-1.5 text-start text-sm"
                                    href="{{ route('app.profile') }}" wire:navigate data-test="mobile-profile-link">
                                    <flux:avatar :name="auth()->user()->name" :src="auth()->user()->profile_photo_url"
                                        :initials="auth()->user()->initials()" />

                                    <div class="grid flex-1 text-start text-sm leading-tight">
                                        <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                        <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                    </div>
                                </a>
                            </div>
                        </flux:menu.radio.group>

                        <x-app.user-role-menu-items />

                        <flux:menu.radio.group>
                            @can('access-director')
                                <flux:menu.item :href="route('app.director.setup')" icon="adjustments-horizontal"
                                    wire:navigate>
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
            </div>
        </flux:header>

        <div id="app-mobile-sidebar-fab"
            class="ee-mobile-sidebar-fab pointer-events-none fixed left-0 top-[4.4rem] z-[70]">
            <div
                class="ee-mobile-menu-wrap pointer-events-auto flex h-12 w-10 items-center justify-center rounded-e-xl bg-linear-to-br from-[#f1d57a] via-[#c7a840] to-[#8a7424] text-slate-50 shadow-[0_18px_34px_-20px_rgba(2,6,23,0.9)] ring-1 ring-white/30">
                <flux:sidebar.collapse class="text-white [&_svg]:text-white" />
            </div>
        </div>
    </div>

    {{ $slot }}

    @livewire('shared.church-link-modal')
    <x-shared.toast-stack />

    @include('components.layouts.bottom.app-scripts')

    @stack('js')
</body>

</html>
