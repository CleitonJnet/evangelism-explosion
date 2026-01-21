
<nav class="items-center hidden h-16 gap-6 font-semibold 2md:flex">
    <a href="<?php echo e(route('web.home')); ?>" class="flex items-center h-full transition text-white/90 hover:text-amber-300">
        Início
    </a>

    <?php if (isset($component)) { $__componentOriginal4efe4330c90522903dabe7d4971761ce = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4efe4330c90522903dabe7d4971761ce = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.src.dropdown','data' => ['label' => 'EE','description' => 'Detalhes do EE','width' => 'fit-content']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('src.dropdown'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'EE','description' => 'Detalhes do EE','width' => 'fit-content']); ?>
        <?php if (isset($component)) { $__componentOriginal03f1dc88aad334da44cf817bbdc3f066 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal03f1dc88aad334da44cf817bbdc3f066 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.src.dropdown-item','data' => ['label' => '&#10022; O que é o EE?','route' => '#about']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('src.dropdown-item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => '&#10022; O que é o EE?','route' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('#about')]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal03f1dc88aad334da44cf817bbdc3f066)): ?>
<?php $attributes = $__attributesOriginal03f1dc88aad334da44cf817bbdc3f066; ?>
<?php unset($__attributesOriginal03f1dc88aad334da44cf817bbdc3f066); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal03f1dc88aad334da44cf817bbdc3f066)): ?>
<?php $component = $__componentOriginal03f1dc88aad334da44cf817bbdc3f066; ?>
<?php unset($__componentOriginal03f1dc88aad334da44cf817bbdc3f066); ?>
<?php endif; ?>
        <?php if (isset($component)) { $__componentOriginal03f1dc88aad334da44cf817bbdc3f066 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal03f1dc88aad334da44cf817bbdc3f066 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.src.dropdown-item','data' => ['label' => '&#10022; História','route' => route('web.about.history')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('src.dropdown-item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => '&#10022; História','route' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('web.about.history'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal03f1dc88aad334da44cf817bbdc3f066)): ?>
<?php $attributes = $__attributesOriginal03f1dc88aad334da44cf817bbdc3f066; ?>
<?php unset($__attributesOriginal03f1dc88aad334da44cf817bbdc3f066); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal03f1dc88aad334da44cf817bbdc3f066)): ?>
<?php $component = $__componentOriginal03f1dc88aad334da44cf817bbdc3f066; ?>
<?php unset($__componentOriginal03f1dc88aad334da44cf817bbdc3f066); ?>
<?php endif; ?>
        <?php if (isset($component)) { $__componentOriginal03f1dc88aad334da44cf817bbdc3f066 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal03f1dc88aad334da44cf817bbdc3f066 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.src.dropdown-item','data' => ['label' => '&#10022; Declaração de Fé','route' => route('web.about.faith')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('src.dropdown-item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => '&#10022; Declaração de Fé','route' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('web.about.faith'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal03f1dc88aad334da44cf817bbdc3f066)): ?>
<?php $attributes = $__attributesOriginal03f1dc88aad334da44cf817bbdc3f066; ?>
<?php unset($__attributesOriginal03f1dc88aad334da44cf817bbdc3f066); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal03f1dc88aad334da44cf817bbdc3f066)): ?>
<?php $component = $__componentOriginal03f1dc88aad334da44cf817bbdc3f066; ?>
<?php unset($__componentOriginal03f1dc88aad334da44cf817bbdc3f066); ?>
<?php endif; ?>
        <?php if (isset($component)) { $__componentOriginal03f1dc88aad334da44cf817bbdc3f066 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal03f1dc88aad334da44cf817bbdc3f066 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.src.dropdown-item','data' => ['label' => '&#10022; Visão, Missão e Princípios','route' => route('web.about.vision-mission')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('src.dropdown-item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => '&#10022; Visão, Missão e Princípios','route' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('web.about.vision-mission'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal03f1dc88aad334da44cf817bbdc3f066)): ?>
<?php $attributes = $__attributesOriginal03f1dc88aad334da44cf817bbdc3f066; ?>
<?php unset($__attributesOriginal03f1dc88aad334da44cf817bbdc3f066); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal03f1dc88aad334da44cf817bbdc3f066)): ?>
<?php $component = $__componentOriginal03f1dc88aad334da44cf817bbdc3f066; ?>
<?php unset($__componentOriginal03f1dc88aad334da44cf817bbdc3f066); ?>
<?php endif; ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4efe4330c90522903dabe7d4971761ce)): ?>
<?php $attributes = $__attributesOriginal4efe4330c90522903dabe7d4971761ce; ?>
<?php unset($__attributesOriginal4efe4330c90522903dabe7d4971761ce); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4efe4330c90522903dabe7d4971761ce)): ?>
<?php $component = $__componentOriginal4efe4330c90522903dabe7d4971761ce; ?>
<?php unset($__componentOriginal4efe4330c90522903dabe7d4971761ce); ?>
<?php endif; ?>

    <?php if (isset($component)) { $__componentOriginal4efe4330c90522903dabe7d4971761ce = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4efe4330c90522903dabe7d4971761ce = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.src.dropdown','data' => ['label' => 'Ministérios','description' => 'TODOS OS MINISTÉRIOS']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('src.dropdown'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Ministérios','description' => 'TODOS OS MINISTÉRIOS']); ?>
        <a href="<?php echo e(route('web.ministry.kids-ee')); ?>"
            class="flex items-start gap-3 p-3 transition rounded-xl hover:bg-white/10 shine">
            <div>
                <div class="font-extrabold text-white border-b-2 border-orange-600/50 pb-0.5">
                    &#10022; EE-Kids <span class="pl-1 text-sm font-light opacity-80">«
                        Esperança Para
                        Crianças »</span>
                </div>
                <div class="text-xs leading-snug text-light text-white/60 pt-0.5 px-0.5">
                    Ministério de Evangelismo e Discipulado para
                    <strong>Crianças</strong>.
                </div>
            </div>
        </a>

        <a href="<?php echo e(route('web.ministry.everyday-evangelism')); ?>"
            class="flex items-start gap-3 p-3 transition rounded-xl hover:bg-white/10 shine">
            <div>
                <div class="font-extrabold text-white border-b-2 border-sky-600/50 pb-0.5">
                    &#10022; Evangelismo Eficaz <span class="pl-1 text-sm font-light opacity-80">« com 5
                        Partes »</span>
                </div>
                <div class="text-xs leading-snug text-light text-white/60 pt-0.5 px-0.5">
                    Ministério de Evangelismo e Discipulado para <strong>Jovens e
                        Adultos</strong>.
                </div>
            </div>
        </a>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4efe4330c90522903dabe7d4971761ce)): ?>
<?php $attributes = $__attributesOriginal4efe4330c90522903dabe7d4971761ce; ?>
<?php unset($__attributesOriginal4efe4330c90522903dabe7d4971761ce); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4efe4330c90522903dabe7d4971761ce)): ?>
<?php $component = $__componentOriginal4efe4330c90522903dabe7d4971761ce; ?>
<?php unset($__componentOriginal4efe4330c90522903dabe7d4971761ce); ?>
<?php endif; ?>


    <a href="<?php echo e(route('web.event.index')); ?>"
        class="flex items-center h-full transition text-white/90 hover:text-amber-300">
        Eventos
    </a>
    <a href="https://www.evangelismexplosion.org/" target="_blank"
        class="flex items-center h-full transition text-white/90 hover:text-amber-300">
        EE Internacional
    </a>
    <a href="<?php echo e(route('web.donate')); ?>" title="Oferta Missionária"
        class="h-9 flex items-center px-6 py-0.5 font-semibold text-center rounded text-sm text-[#1b1709]
                                   shine bg-linear-to-br from-[#f1d57a] via-[#c7a840] to-[#8a7424]
                                   border border-white/20 transition hover:brightness-110 nav-link-textshadow">
        <span class="text-2xl">&#10087;</span> Ofertas
    </a>
</nav>
<?php /**PATH /home/cleiton/workspaces/ee/resources/views/components/web/navigation/menu-desktop.blade.php ENDPATH**/ ?>