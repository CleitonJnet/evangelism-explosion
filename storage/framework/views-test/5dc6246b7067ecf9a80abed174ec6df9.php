<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['label', 'description' => 'Menu', 'width' => '340px']));

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

foreach (array_filter((['label', 'description' => 'Menu', 'width' => '340px']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>


<div class="relative h-full" data-dropdown>
    
    <a data-dropdown-toggle
        class="flex items-center h-full gap-0.5 px-1 transition cursor-pointer text-white/90 hover:text-amber-300">
        <span><?php echo $label; ?></span>

        
        <svg class="w-4 h-4 transition-transform duration-180" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd"
                d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.94l3.71-3.71a.75.75 0 1 1 1.06 1.06l-4.24 4.24a.75.75 0 0 1-1.06 0L5.21 8.29a.75.75 0 0 1 .02-1.08Z"
                clip-rule="evenodd" />
        </svg>
    </a>

    
    <div data-dropdown-menu style="width: <?php echo e($width); ?>;"
        class="absolute -right-2 top-full z-50 mt-0 rounded-2xl border border-white/10 bg-sky-950 shadow-[0_18px_50px_rgba(0,0,0,.55)] overflow-hidden opacity-0 invisible pointer-events-none transition-all duration-150 nav-backdrop-24">

        <div class="absolute inset-x-0 h-3 -top-3"></div>

        
        <div class="h-[2px] w-full rounded-t-2xl nav-gold-gradient"></div>

        <div class="p-4">
            <div class="mb-0.5 text-xs font-extrabold tracking-widest text-right uppercase text-amber-200/80">
                <?php echo $description; ?>

            </div>

            <div class="">
                <?php echo e($slot); ?>

            </div>
        </div>
    </div>
</div>
<?php /**PATH /home/cleiton/workspaces/ee/resources/views/components/src/dropdown.blade.php ENDPATH**/ ?>