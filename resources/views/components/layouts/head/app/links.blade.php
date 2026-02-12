<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

@vite(entrypoints: ['resources/css/tailwind.css', 'resources/css/app.css', 'resources/js/app.js'])
{{-- <link rel="stylesheet" href="{{ asset('build/assets/tailwind-DUzofF9r.css') }}">
<link rel="stylesheet" href="{{ asset('build/assets/app-BSA4eGAX.css') }}"> --}}

@livewireStyles
@fluxAppearance
@stack('css')
