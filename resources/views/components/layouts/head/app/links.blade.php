<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

@vite(entrypoints: ['resources/css/tailwind.css', 'resources/css/app.css', 'resources/js/app.js'])
{{-- <link rel="stylesheet" href="{{ asset('build/assets/tailwind-Dw6KZDMc.css') }}">
<link rel="stylesheet" href="{{ asset('build/assets/app-B1GeArIX.css') }}"> --}}

@livewireStyles
@fluxAppearance
@stack('css')
