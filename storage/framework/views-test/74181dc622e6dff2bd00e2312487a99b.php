<!doctype html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">

<?php echo $__env->make('components.layouts.head.web', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<body class="relative">

    <?php if (isset($component)) { $__componentOriginalef137565c0dee90df663f9d3e715afb8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalef137565c0dee90df663f9d3e715afb8 = $attributes; } ?>
<?php $component = App\View\Components\Web\Whatsapp::resolve(['phone' => '5511976423666','title' => __('EE-Brasil')] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('web.whatsapp'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\Web\Whatsapp::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalef137565c0dee90df663f9d3e715afb8)): ?>
<?php $attributes = $__attributesOriginalef137565c0dee90df663f9d3e715afb8; ?>
<?php unset($__attributesOriginalef137565c0dee90df663f9d3e715afb8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalef137565c0dee90df663f9d3e715afb8)): ?>
<?php $component = $__componentOriginalef137565c0dee90df663f9d3e715afb8; ?>
<?php unset($__componentOriginalef137565c0dee90df663f9d3e715afb8); ?>
<?php endif; ?>

    <?php if (isset($component)) { $__componentOriginaldcf5530ce5d72f3979823c8a010c2fdc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldcf5530ce5d72f3979823c8a010c2fdc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.web.navigation.navbar','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('web.navigation.navbar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldcf5530ce5d72f3979823c8a010c2fdc)): ?>
<?php $attributes = $__attributesOriginaldcf5530ce5d72f3979823c8a010c2fdc; ?>
<?php unset($__attributesOriginaldcf5530ce5d72f3979823c8a010c2fdc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldcf5530ce5d72f3979823c8a010c2fdc)): ?>
<?php $component = $__componentOriginaldcf5530ce5d72f3979823c8a010c2fdc; ?>
<?php unset($__componentOriginaldcf5530ce5d72f3979823c8a010c2fdc); ?>
<?php endif; ?>

    <main class="relative min-h-screen pb-10 space-y-10 antialiased leading-relaxed text-gray-800 ee-metal-section">
        <?php echo e($slot); ?>

    </main>

    <?php if (isset($component)) { $__componentOriginal20d90b51c541c707c0abda6b84690e20 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal20d90b51c541c707c0abda6b84690e20 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.web.footer','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('web.footer'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal20d90b51c541c707c0abda6b84690e20)): ?>
<?php $attributes = $__attributesOriginal20d90b51c541c707c0abda6b84690e20; ?>
<?php unset($__attributesOriginal20d90b51c541c707c0abda6b84690e20); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal20d90b51c541c707c0abda6b84690e20)): ?>
<?php $component = $__componentOriginal20d90b51c541c707c0abda6b84690e20; ?>
<?php unset($__componentOriginal20d90b51c541c707c0abda6b84690e20); ?>
<?php endif; ?>

    <script type="module" src="<?php echo e(asset('build/assets/javascript-C6M4hJnc.js')); ?>"></script>

    <!-- Swiper JS -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    @livewireScripts
    <?php echo $__env->yieldPushContent('js'); ?>

</body>

</html>
<?php /**PATH /home/cleiton/workspaces/ee/resources/views/components/layouts/guest.blade.php ENDPATH**/ ?>