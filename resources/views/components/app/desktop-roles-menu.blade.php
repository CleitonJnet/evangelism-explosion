@if (auth()->user()->roles()->count() > 1)
    <flux:dropdown position="bottom" align="start" class="bg-white/5 rounded-lg">
        @php
            $currentRoleLabel = match (true) {
                request()->routeIs('app.board.*') => __('Board Member'),
                request()->routeIs('app.director.*') => __('National Director'),
                request()->routeIs('app.teacher.*') => __('Teacher'),
                request()->routeIs('app.facilitator.*') => __('Facilitator'),
                request()->routeIs('app.fieldworker.*') => __('Field Worker'),
                request()->routeIs('app.mentor.*') => __('Mentor'),
                request()->routeIs('app.student.*') => __('Student'),
                default => __('Triagem'),
            };
        @endphp

        <button type="button"
            class="flex h-8 w-full items-center justify-between gap-3 rounded-lg border border-transparent bg-transparent px-3 text-start text-sm font-semibold text-slate-100/90 hover:bg-white/10 hover:text-white in-data-flux-sidebar-on-mobile:h-10 in-data-flux-sidebar-collapsed-desktop:w-10 in-data-flux-sidebar-collapsed-desktop:justify-center"
            {{ $attributes }}>
            {{ $currentRoleLabel }}

            <svg class="size-4 transition-transform duration-180" viewBox="0 0 20 20" fill="currentColor"
                aria-hidden="true">
                <path fill-rule="evenodd"
                    d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.94l3.71-3.71a.75.75 0 1 1 1.06 1.06l-4.24 4.24a.75.75 0 0 1-1.06 0L5.21 8.29a.75.75 0 0 1 .02-1.08Z"
                    clip-rule="evenodd" />
            </svg>
        </button>

        <flux:menu
            class="border-slate-600! bg-slate-50! dark:border-slate-600! dark:bg-slate-700! text-white! w-56! min-w-56! backdrop-blur-3xl">
            <flux:menu.radio.group>
                @can('access-board')
                    <flux:menu.item :href="route('app.board.dashboard')" wire:navigate
                        class="hover:bg-sky-950! hover:text-white!">
                        &#10023; {{ __('Board Member') }}
                    </flux:menu.item>
                @endcan

                @can('access-director')
                    <flux:menu.item :href="route('app.director.dashboard')" wire:navigate
                        class="hover:bg-sky-950! hover:text-white!">
                        &#10023; {{ __('National Director') }}
                    </flux:menu.item>
                @endcan

                @can('access-teacher')
                    <flux:menu.item :href="route('app.teacher.dashboard')" wire:navigate
                        class="hover:bg-sky-950! hover:text-white!">
                        &#10023; {{ __('Teacher') }}
                    </flux:menu.item>
                @endcan

                @can('access-facilitator')
                    <flux:menu.item :href="route('app.facilitator.dashboard')" wire:navigate
                        class="hover:bg-sky-950! hover:text-white!">
                        &#10023; {{ __('Facilitator') }}
                    </flux:menu.item>
                @endcan

                @can('access-fieldworker')
                    <flux:menu.item :href="route('app.fieldworker.dashboard')" wire:navigate
                        class="hover:bg-sky-950! hover:text-white!">
                        &#10023; {{ __('Field Worker') }}
                    </flux:menu.item>
                @endcan

                @can('access-mentor')
                    <flux:menu.item :href="route('app.mentor.dashboard')" wire:navigate
                        class="hover:bg-sky-950! hover:text-white!">
                        &#10023; {{ __('Mentor') }}
                    </flux:menu.item>
                @endcan

                @can('access-student')
                    <flux:menu.item :href="route('app.student.training.index')" wire:navigate
                        class="hover:bg-sky-950! hover:text-white!">
                        &#10023; {{ __('Student') }}
                    </flux:menu.item>
                @endcan

                <flux:menu.item :href="route('app.start')" wire:navigate class="hover:bg-sky-950! hover:text-white!">
                    &#10023; {{ __('Triagem') }}
                </flux:menu.item>
            </flux:menu.radio.group>
        </flux:menu>
    </flux:dropdown>
@endif
