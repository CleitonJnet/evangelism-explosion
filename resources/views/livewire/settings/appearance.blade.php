<?php

use Livewire\Volt\Component;

new class extends Component {
    public bool $embedded = false;
}; ?>

<section class="w-full">
    @if (!$embedded)
        @include('components.app.partials.settings-heading')

        <flux:heading class="sr-only">{{ __('Configurações de aparência') }}</flux:heading>

        <x-app.settings.layout :heading="__('Appearance')" :subheading="__('Update the appearance settings for your account')">
            @include('components.app.settings.appearance-content')
        </x-app.settings.layout>
    @else
        <div class="flex flex-col gap-6 rounded-2xl border border-[color:var(--ee-app-border)] bg-[color:var(--ee-app-surface)] p-6">
            <div class="flex items-center justify-between gap-4">
                <flux:heading size="sm" level="2">{{ __('Appearance') }}</flux:heading>
            </div>

            @include('components.app.settings.appearance-content')
        </div>
    @endif
</section>
