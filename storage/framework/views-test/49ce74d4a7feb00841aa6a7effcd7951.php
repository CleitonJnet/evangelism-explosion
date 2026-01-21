<?php
    $logoSrc = request()->routeIs('web.ministry.kids-ee')
        ? asset('images/logo/kids-ee.webp')
        : asset('images/logo/ee-white.webp');
?>

<header id="main-header" class="fixed top-0 left-0 z-50 w-full text-white nav-textshadow">

    <div id="header-wrapper" class="transition-transform duration-300 ease-in-out translate-y-0 will-change-transform">

        
        <div id="top-bar" class="hidden w-full transition-colors duration-300 shadow-none 2md:block">
            <div class="flex items-center justify-end px-4 mx-auto max-w-8xl mx-auto sm:px-6 lg:px-8">
                <div class="flex items-center gap-2">
                    <?php if(auth()->guard()->check()): ?>
                        <a href="<?php echo e(url('dashboard')); ?>"
                            class="inline-flex items-center gap-2 py-2 text-sm font-semibold transition text-white/90 hover:text-amber-300">
                            <?php echo e(Auth::user()->name); ?>

                        </a>
                    <?php else: ?>
                        <a href="<?php echo e(route('login')); ?>"
                            class="inline-flex items-center gap-2 py-2 text-sm font-semibold transition text-white/90 hover:text-amber-300">
                            &#10023; <?php echo e(__('LOGIN')); ?> &#10023;
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        
        <div id="main-bar"
            class="border-t border-amber-500/80 transition-[background-color,box-shadow] duration-300 ease-in-out shadow-none">

            <div class="px-4 mx-auto max-w-8xl mx-auto sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">

                    
                    <a href="<?php echo e(route('web.home')); ?>" class="flex items-center h-full gap-3">
                        <img src="<?php echo e(asset($logoSrc)); ?>" class="w-auto h-12 nav-iconshadow">
                        <div class="leading-tight">
                            <div class="relative text-white nav-cinzel">
                                EVANGELISMO EXPLOSIVO
                                <span
                                    class="<?php echo e(request()->routeIs('web.ministry.kids-ee') ? '' : 'hidden'); ?> absolute text-sm text-orange-400 rotate-45 -top-2 -right-4 font-averia-bold">Kids</span>
                            </div>
                            <div
                                class="hidden min-[345px]:block text-xs font-bold bg-linear-to-r from-[#f5e6a8] via-[#d6b85f] to-[#b89b3c] bg-clip-text text-transparent tracking-wide">
                                NO BRASIL — Até Que Todos Ouçam!
                            </div>
                        </div>
                    </a>

                    <?php if (isset($component)) { $__componentOriginal834318e4ab2ddb89416e54622669eee8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal834318e4ab2ddb89416e54622669eee8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.web.navigation.menu-desktop','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('web.navigation.menu-desktop'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal834318e4ab2ddb89416e54622669eee8)): ?>
<?php $attributes = $__attributesOriginal834318e4ab2ddb89416e54622669eee8; ?>
<?php unset($__attributesOriginal834318e4ab2ddb89416e54622669eee8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal834318e4ab2ddb89416e54622669eee8)): ?>
<?php $component = $__componentOriginal834318e4ab2ddb89416e54622669eee8; ?>
<?php unset($__componentOriginal834318e4ab2ddb89416e54622669eee8); ?>
<?php endif; ?>

                    
                    <button id="menu-btn"
                        class="p-2 rounded-md 2md:hidden hover:text-amber-300 focus:outline-none focus:ring focus:ring-amber-400/40"
                        aria-expanded="false" aria-label="Abrir menu">
                        <svg id="icon-hamburger" class="w-7 h-7" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-width="2" stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16" />
                        </svg>
                        <svg id="icon-close" class="hidden w-7 h-7" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-width="2" stroke-linecap="round" d="M6 6l12 12M18 6L6 18" />
                        </svg>
                    </button>

                </div>
            </div>
        </div>


    </div>
    
    <?php if (isset($component)) { $__componentOriginal4e394034e5aa9efd2b48f30c4bbcc70b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4e394034e5aa9efd2b48f30c4bbcc70b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.web.navigation.menu-mobile','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('web.navigation.menu-mobile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4e394034e5aa9efd2b48f30c4bbcc70b)): ?>
<?php $attributes = $__attributesOriginal4e394034e5aa9efd2b48f30c4bbcc70b; ?>
<?php unset($__attributesOriginal4e394034e5aa9efd2b48f30c4bbcc70b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4e394034e5aa9efd2b48f30c4bbcc70b)): ?>
<?php $component = $__componentOriginal4e394034e5aa9efd2b48f30c4bbcc70b; ?>
<?php unset($__componentOriginal4e394034e5aa9efd2b48f30c4bbcc70b); ?>
<?php endif; ?>
</header>
<?php /**PATH /home/cleiton/workspaces/ee/resources/views/components/web/navigation/navbar.blade.php ENDPATH**/ ?>