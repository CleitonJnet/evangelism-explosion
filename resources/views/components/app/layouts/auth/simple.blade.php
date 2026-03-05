<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('components.app.partials.head')
</head>

<body class="min-h-screen antialiased">
    <div class="flex min-h-svh flex-col items-center justify-center gap-6 bg-blue-900/85 bg-blend-multiply bg-center bg-cover p-6 md:p-10 backdrop-blur-2xl"
        style="background-image: url('{{ asset('images/clinic-ee.webp') }}');">
        <div class="flex w-full max-w-sm flex-col gap-2 p-6 rounded-2xl bg-white/85"
            style="box-shadow: 0 0 15px 1px rgba(0,0,0,0.75);">
            <a href="{{ route('web.home') }}" class="w-16 block mx-auto" wire:navigate>
                <x-app.app-logo-icon class="" />
            </a>
            <div class="flex flex-col gap-6">{{ $slot }}</div>
        </div>
    </div>

    <x-shared.toast-stack />

    @include('components.layouts.bottom.web-scripts')
</body>

</html>
