<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    
    <title><?php echo e($fullTitle ?? 'Evangelism Explosion'); ?></title>
    <meta name="description" content="<?php echo e($metaDescription ?? ''); ?>">
    <meta name="keywords" content="<?php echo e($metaKeywords ?? ''); ?>">
    <meta name="robots" content="<?php echo e($robotsContent ?? ''); ?>">
    <link rel="canonical" href="<?php echo e($canonicalUrl ?? ''); ?>">

    
    <meta property="og:site_name" content="<?php echo e($appName ?? ''); ?>">
    <meta property="og:title" content="<?php echo e($fullTitle ?? 'Evangelism Explosion'); ?>">
    <meta property="og:description" content="<?php echo e($metaDescription ?? ''); ?>">
    <meta property="og:type" content="<?php echo e($ogType ?? ''); ?>">
    <meta property="og:url" content="<?php echo e($canonicalUrl ?? ''); ?>">
    <meta property="og:image" content="<?php echo e($ogImg ?? ''); ?>">
    <meta property="og:locale" content="<?php echo e($locale ?? ''); ?>">
    <meta property="og:image:alt" content="<?php echo e($metaDescription ?? ''); ?>">

    
    <meta name="twitter:card" content="<?php echo e($twCard ?? ''); ?>">
    <meta name="twitter:title" content="<?php echo e($fullTitle ?? 'Evangelism Explosion'); ?>">
    <meta name="twitter:description" content="<?php echo e($metaDescription ?? ''); ?>">
    <meta name="twitter:image" content="<?php echo e($twImg ?? ''); ?>">
    <meta name="twitter:image:alt" content="<?php echo e($metaDescription ?? ''); ?>">

    <link rel="apple-touch-icon" sizes="57x57" href="<?php echo e(asset('images/favicon/apple-icon-57x57.png')); ?>">
    <link rel="apple-touch-icon" sizes="60x60" href="<?php echo e(asset('images/favicon/apple-icon-60x60.png')); ?>">
    <link rel="apple-touch-icon" sizes="72x72" href="<?php echo e(asset('images/favicon/apple-icon-72x72.png')); ?>">
    <link rel="apple-touch-icon" sizes="76x76" href="<?php echo e(asset('images/favicon/apple-icon-76x76.png')); ?>">
    <link rel="apple-touch-icon" sizes="114x114" href="<?php echo e(asset('images/favicon/apple-icon-114x114.png')); ?>">
    <link rel="apple-touch-icon" sizes="120x120" href="<?php echo e(asset('images/favicon/apple-icon-120x120.png')); ?>">
    <link rel="apple-touch-icon" sizes="144x144" href="<?php echo e(asset('images/favicon/apple-icon-144x144.png')); ?>">
    <link rel="apple-touch-icon" sizes="152x152" href="<?php echo e(asset('images/favicon/apple-icon-152x152.png')); ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo e(asset('images/favicon/apple-icon-180x180.png')); ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo e(asset('images/favicon/favicon-32x32.png')); ?>">
    <link rel="icon" type="image/png" sizes="96x96" href="<?php echo e(asset('images/favicon/favicon-96x96.png')); ?>">
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo e(asset('images/favicon/favicon-16x16.png')); ?>">
    <link rel="manifest" href="<?php echo e(asset('images/favicon/manifest.json')); ?>">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="<?php echo e(asset('images/favicon/ms-icon-144x144.png')); ?>">
    <meta name="theme-color" content="#ffffff">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

    
    <link rel="stylesheet" href="<?php echo e(asset('build/assets/tailwind-CAzqIfHO.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('build/assets/styles-B3akrGKr.css')); ?>">
    <?php echo $__env->yieldPushContent('css'); ?>
    @livewireStyles
</head>
<?php /**PATH /home/cleiton/workspaces/ee/resources/views/components/layouts/head/web.blade.php ENDPATH**/ ?>