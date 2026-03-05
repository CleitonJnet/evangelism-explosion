<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('components.app.partials.head')
</head>

<body class="min-h-screen antialiased">
    <div class="flex min-h-svh flex-col items-center justify-center gap-6 bg-blue-900/85 bg-blend-multiply bg-center bg-cover p-6 md:p-10"
        style="background-image: url('{{ asset('images/clinic-ee.webp') }}');">
        <div class="flex w-full max-w-sm flex-col gap-2 p-6 rounded-2xl bg-white/85">
            <a href="{{ route('web.home') }}" class="flex flex-col items-center gap-2 font-medium" wire:navigate>
                <span class="flex h-16 w-16 mb-1 items-center justify-center rounded-md">
                    <x-app.app-logo-icon class="size-9 fill-current text-black" />
                </span>
                <span class="sr-only">{{ config('app.name', 'Laravel') }}</span>
            </a>
            <div class="flex flex-col gap-6">
                {{ $slot }}
            </div>
        </div>
    </div>

    <x-shared.toast-stack />

    @include('components.layouts.bottom.web-scripts')
</body>

</html>
