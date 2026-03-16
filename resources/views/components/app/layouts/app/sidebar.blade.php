<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('components.app.partials.head')
</head>

<body class="min-h-screen bg-slate-400 select-none">
    <flux:sidebar sticky collapsible="mobile" class="border-e border-sky-900/60 bg-sky-950 text-slate-100 z-9999!">
        <flux:sidebar.header>
            <x-app.app-logo :sidebar="true" href="{{ route('app.start') }}" wire:navigate />
            <flux:sidebar.collapse class="lg:hidden" />
        </flux:sidebar.header>

        <x-src.line-theme />

        <flux:sidebar.nav>
            @php
                $showPortalNavigation = request()->routeIs('app.portal.*') || ($currentPortal?->value === 'student' && request()->routeIs('app.student.*'));
            @endphp

            @if ($currentPortal)
                <div class="mb-4 flex flex-col gap-3 rounded-2xl border border-white/10 bg-white/5 p-4">
                    <div class="flex flex-col gap-1">
                        <span class="text-[11px] font-semibold uppercase tracking-[0.24em] text-sky-200/80">
                            {{ __('Portal atual') }}
                        </span>
                        <span class="text-sm font-semibold text-white">{{ $currentPortal->label() }}</span>
                    </div>

                    <flux:button variant="ghost" size="sm" :href="route('app.start')" wire:navigate>
                        {{ __('Trocar portal') }}
                    </flux:button>
                </div>
            @endif

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

            @if ($showPortalNavigation && ! empty($portalMenuSections))
                @foreach ($portalMenuSections as $section)
                    <flux:sidebar.group :heading="__($section['title'])"
                        class="grid [&>div>div]:truncate [&>div>div]:text-slate-300/80">
                        @foreach ($section['items'] as $item)
                            <x-app.menu-sidebar-item
                                :label="$item['label']"
                                :route="route($item['route'])"
                                :current="request()->routeIs($item['route'])"
                                :icon="$item['icon']" />
                        @endforeach
                    </flux:sidebar.group>
                @endforeach
            @elseif ($currentRoleKey && isset($roleMenus[$currentRoleKey]))
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
            <flux:profile avatar:src="{{ auth()->user()->profile_photo_url }}" :initials="auth()->user()->initials()"
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
    <x-shared.toast-stack />

    @include('components.layouts.bottom.app-scripts')

    @stack('js')
</body>

</html>
