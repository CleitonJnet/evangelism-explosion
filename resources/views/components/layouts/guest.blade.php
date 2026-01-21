<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>

    @include('components.layouts.head.web.meta')
    @include('components.layouts.head.favicon')

    <title>{{ $fullTitle ?? 'Evangelism Explosion' }}</title>

    @include('components.layouts.head.web.links')

</head>

<body class="relative">

    <x-web.whatsapp phone="5511976423666" :title="__('EE-Brasil')" />

    <x-web.navigation.navbar />

    <main class="relative min-h-screen pb-10 space-y-10 antialiased leading-relaxed text-gray-800 ee-metal-section">
        {{ $slot }}
    </main>

    <x-web.footer />


    @include('components.layouts.bottom.web-scripts')
</body>

</html>
