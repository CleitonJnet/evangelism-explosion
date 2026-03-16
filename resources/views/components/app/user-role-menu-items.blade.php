@php
    $hasRoleLinks =
        auth()->user()->can('access-board') ||
        auth()->user()->can('access-director') ||
        auth()->user()->can('access-teacher') ||
        auth()->user()->can('access-facilitator') ||
        auth()->user()->can('access-fieldworker') ||
        auth()->user()->can('access-mentor') ||
        auth()->user()->can('access-student');

    $roleItemClass = static fn (bool $isCurrent): string => $isCurrent
        ? 'bg-sky-950! text-amber-200! font-semibold'
        : 'text-slate-700 hover:bg-slate-100';
@endphp

@if ($hasRoleLinks)
    <flux:menu.separator />

    <flux:menu.radio.group>
        <flux:menu.item :href="route('app.start')" icon="squares-2x2" wire:navigate
            :class="$roleItemClass(request()->routeIs('app.portal.*') || request()->routeIs('app.start'))">
            {{ __('Portais') }}
        </flux:menu.item>

        @can('access-board')
            <flux:menu.item :href="route('app.board.dashboard')" icon="home" wire:navigate
                :class="$roleItemClass(request()->routeIs('app.board.*'))">
                {{ __('Board') }}
            </flux:menu.item>
        @endcan

        @can('access-director')
            <flux:menu.item :href="route('app.director.dashboard')" icon="home" wire:navigate
                :class="$roleItemClass(request()->routeIs('app.director.*'))">
                {{ __('Director') }}
            </flux:menu.item>
        @endcan

        @can('access-teacher')
            <flux:menu.item :href="route('app.teacher.dashboard')" icon="home" wire:navigate
                :class="$roleItemClass(request()->routeIs('app.teacher.*'))">
                {{ __('Teacher') }}
            </flux:menu.item>
        @endcan

        @can('access-facilitator')
            <flux:menu.item :href="route('app.facilitator.dashboard')" icon="home" wire:navigate
                :class="$roleItemClass(request()->routeIs('app.facilitator.*'))">
                {{ __('Facilitator') }}
            </flux:menu.item>
        @endcan

        @can('access-fieldworker')
            <flux:menu.item :href="route('app.fieldworker.dashboard')" icon="home" wire:navigate
                :class="$roleItemClass(request()->routeIs('app.fieldworker.*'))">
                {{ __('FieldWorker') }}
            </flux:menu.item>
        @endcan

        @can('access-mentor')
            <flux:menu.item :href="route('app.mentor.dashboard')" icon="home" wire:navigate
                :class="$roleItemClass(request()->routeIs('app.mentor.*'))">
                {{ __('Mentor') }}
            </flux:menu.item>
        @endcan

        @can('access-student')
            <flux:menu.item :href="route('app.portal.student.dashboard')" icon="home" wire:navigate
                :class="$roleItemClass(request()->routeIs('app.student.*') || request()->routeIs('app.portal.student.*'))">
                {{ __('Student') }}
            </flux:menu.item>
        @endcan
    </flux:menu.radio.group>
@endif
