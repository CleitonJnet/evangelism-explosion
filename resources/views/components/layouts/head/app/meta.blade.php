<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">

{{-- SEO básico --}}
<meta name="description" content="{{ $metaDescription ?? '' }}">
<meta name="keywords" content="{{ $metaKeywords ?? '' }}">
<meta name="robots" content="{{ $robotsContent ?? '' }}">
<link rel="canonical" href="{{ $canonicalUrl ?? '' }}">

{{-- Open Graph --}}
<meta property="og:site_name" content="{{ $appName ?? '' }}">
<meta property="og:title" content="{{ $fullTitle ?? 'Evangelism Explosion' }}">
<meta property="og:description" content="{{ $metaDescription ?? '' }}">
<meta property="og:type" content="{{ $ogType ?? '' }}">
<meta property="og:url" content="{{ $canonicalUrl ?? '' }}">
<meta property="og:image" content="{{ $ogImg ?? '' }}">
<meta property="og:locale" content="{{ $locale ?? '' }}">
<meta property="og:image:alt" content="{{ $metaDescription ?? '' }}">

{{-- Twitter Card --}}
<meta name="twitter:card" content="{{ $twCard ?? '' }}">
<meta name="twitter:title" content="{{ $fullTitle ?? 'Evangelism Explosion' }}">
<meta name="twitter:description" content="{{ $metaDescription ?? '' }}">
<meta name="twitter:image" content="{{ $twImg ?? '' }}">
<meta name="twitter:image:alt" content="{{ $metaDescription ?? '' }}">
