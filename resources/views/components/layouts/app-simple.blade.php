<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>

    @include('components.layouts.head.web.meta')
    @include('components.layouts.head.favicon')

    <title>{{ $fullTitle ?? 'Evangelism Explosion' }}</title>

    @include('components.layouts.head.app.links')

</head>

<body class="relative">

    {{ $slot }}

    @livewire('shared.church-link-modal')

    @livewireScripts
    @stack('js')

</body>

</html>
