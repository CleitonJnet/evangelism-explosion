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
        @can('access-board')
            <flux:menu.item :href="route('app.board.dashboard')" icon="home" wire:navigate
                :class="$roleItemClass(request()->routeIs('app.board.*'))">
                {{ __('Board Member') }}
            </flux:menu.item>
        @endcan

        @can('access-director')
            <flux:menu.item :href="route('app.director.dashboard')" icon="home" wire:navigate
                :class="$roleItemClass(request()->routeIs('app.director.*'))">
                {{ __('Diretor Nacional') }}
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
                {{ __('Field Worker') }}
            </flux:menu.item>
        @endcan

        @can('access-mentor')
            <flux:menu.item :href="route('app.mentor.dashboard')" icon="home" wire:navigate
                :class="$roleItemClass(request()->routeIs('app.mentor.*'))">
                {{ __('Mentor') }}
            </flux:menu.item>
        @endcan

        @can('access-student')
            <flux:menu.item :href="route('app.student.dashboard')" icon="home" wire:navigate
                :class="$roleItemClass(request()->routeIs('app.student.*'))">
                {{ __('Student') }}
            </flux:menu.item>
        @endcan

        <flux:menu.item :href="route('app.start')" icon="home" wire:navigate
            :class="$roleItemClass(! request()->routeIs('app.*'))">
            {{ __('Triagem') }}
        </flux:menu.item>
    </flux:menu.radio.group>
@endif
