<flux:dropdown position="bottom" align="start">
    <flux:sidebar.profile
        {{ $attributes->only('name')->merge(['class' => 'text-slate-50/95 hover:text-white [&_span]:text-slate-50/95 [&_span]:group-hover:text-white [&_svg]:text-slate-50/90 [&_svg]:group-hover:text-white']) }}
        avatar:class="bg-amber-700 text-amber-900 after:inset-ring-black/15" :initials="auth()->user()->initials()"
        icon:trailing="chevrons-up-down" data-test="sidebar-menu-button" />

    <flux:menu>
        <a class="flex items-center gap-2 px-1 py-1.5 text-start text-sm" href="{{ route('app.profile') }}" wire:navigate
            data-test="sidebar-profile-link">
            <flux:avatar class="bg-slate-500 text-slate-50 after:inset-ring-black/15" :name="auth()->user()->name"
                :initials="auth()->user()->initials()" />
            <div class="grid flex-1 text-start text-sm leading-tight">
                <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
            </div>
        </a>
        <flux:menu.separator />
        <flux:menu.radio.group>
            @can('access-director')
                <flux:menu.item :href="route('app.director.setup')" icon="adjustments-horizontal" wire:navigate>
                    {{ __('Setup do sistema') }}
                </flux:menu.item>
            @endcan

            <form method="POST" action="{{ route('logout') }}" class="w-full">
                @csrf
                <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle"
                    class="w-full cursor-pointer" data-test="logout-button">
                    {{ __('Log Out') }}
                </flux:menu.item>
            </form>
        </flux:menu.radio.group>
    </flux:menu>
</flux:dropdown>
