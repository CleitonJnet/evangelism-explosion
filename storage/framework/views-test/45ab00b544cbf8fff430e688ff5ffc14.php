<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['title' => '', 'subtitle' => null, 'cover' => asset('images/bg_welcome/photo1.webp')]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['title' => '', 'subtitle' => null, 'cover' => asset('images/bg_welcome/photo1.webp')]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<header
    <?php echo e($attributes->merge(['class' => 'relative py-16 overflow-hidden bg-center bg-no-repeat bg-cover after:absolute after:inset-0 after:bg-sky-950/85'])); ?>

    style="background-image: url(<?php echo e($cover); ?>);">

    <div class="relative z-10 flex flex-col items-center gap-4 px-4 mx-auto max-w-8xl mx-auto sm:px-6 lg:px-8 pt-28">

        <h1 class="max-w-xl text-3xl text-center text-amber-300 sm:text-4xl" style="font-family: 'Cinzel', serif;">
            <?php echo $title; ?>

        </h1>

        <?php if($subtitle): ?>
            <p class="max-w-4xl text-lg text-center text-white/90">
                <?php echo $subtitle; ?>

            </p>
        <?php endif; ?>
    </div>

    <div class="absolute inset-x-0 bottom-0 h-2 z-10 bg-linear-to-r from-[#f1d57a] via-[#c7a840] to-[#8a7424]"></div>
</header>
<?php /**PATH /home/cleiton/workspaces/ee/resources/views/components/web/header.blade.php ENDPATH**/ ?>