
<div id="mobile-menu" class="fixed inset-0 hidden -z-[1] 2md:hidden bg-sky-950/90 backdrop-blur-lg">
    <div class="flex flex-col items-center w-full h-full">

        
        <nav class="w-full px-6 py-24 space-y-5 overflow-auto text-lg font-extrabold">
            <?php if(auth()->guard()->check()): ?>
                <a href="<?php echo e(url('system/start')); ?>"
                    class="block pb-4 text-center js-close-menu text-amber-300 hover:text-amber-500">
                    <small class="mr-1">&#10023;</small> <?php echo e(Auth::user()->name); ?> <small class="mr-1">&#10023;</small>
                    <div class="text-xs font-light text-amber-100"><?php echo e(__('Plataforma Ministerial')); ?></div>
                </a>
            <?php else: ?>
                <a href="<?php echo e(route('login')); ?>"
                    class="block pb-4 text-center js-close-menu text-amber-300 hover:text-amber-500">
                    <small class="px-1">&#10023;</small> <?php echo e(__('Login')); ?> <small class="px-1">&#10023;</small>
                    <div class="text-xs font-light text-amber-100"><?php echo e(__('Plataforma Ministerial')); ?></div>
                </a>
            <?php endif; ?>


            <a href="<?php echo e(route('web.home')); ?>" class="block js-close-menu hover:text-amber-300"><small
                    class="mr-1">&#10022; </small>
                Início</a>
            <a href="<?php echo e(route('web.home')); ?>#about" class="block js-close-menu hover:text-amber-300"><small
                    class="mr-1">&#10022;
                </small> O que é o
                EE?</a>
            <a href="<?php echo e(route('web.about.history')); ?>" class="block js-close-menu hover:text-amber-300"><small
                    class="mr-1">&#10022;
                </small>
                história</a>
            <a href="<?php echo e(route('web.about.faith')); ?>" class="block js-close-menu hover:text-amber-300"><small
                    class="mr-1">&#10022;
                </small>
                Declaração de
                Fé</a>
            <a href="<?php echo e(route('web.about.vision-mission')); ?>" class="block js-close-menu hover:text-amber-300"><small
                    class="mr-1">&#10022; </small>
                Visão,
                Missão e Princípios</a>
            <a href="<?php echo e(route('web.event.index')); ?>" class="block js-close-menu hover:text-amber-300"><small
                    class="mr-1">&#10022;
                </small>
                Eventos</a>
            <a href="<?php echo e(route('web.donate')); ?>" class="block js-close-menu hover:text-amber-300"><small
                    class="mr-1">&#10022;
                </small> Ofertas</a>
            <a href="https://evangelismexplosion.org" class="block js-close-menu hover:text-amber-300"
                target="_blank"><small class="mr-1">&#10022; </small> EE Internacional
            </a>

            <div class="flex flex-wrap gap-2 pt-10">
                <a href="<?php echo e(route('web.ministry.kids-ee')); ?>"
                    class="flex-auto px-8 py-2 text-center transition border rounded-lg shine js-close-menu border-white/20 text-white/90 hover:border-amber-400/60 hover:text-amber-300">
                    EE-Kids
                </a>

                <a href="<?php echo e(route('web.ministry.everyday-evangelism')); ?>"
                    class="flex-auto px-8 py-2 text-center transition border rounded-lg shine js-close-menu border-white/20 text-white/90 hover:border-amber-400/60 hover:text-amber-300">
                    Evangelismo Eficaz
                </a>

                <a href="<?php echo e(route('web.event.schedule')); ?>"
                    class="js-close-menu flex-auto shine px-8 py-2 font-semibold text-center rounded-lg text-[#1b1709] bg-linear-to-br from-[#f1d57a] via-[#c7a840] to-[#8a7424] border border-white/20 shadow-md shadow-black/40 transition hover:brightness-110 hover:shadow-black/60 nav-link-textshadow">
                    Agendar Treinamento
                </a>
            </div>
        </nav>

        <div class="fixed inset-x-0 bottom-0 py-3 text-sm text-center text-white/60 bg-sky-950/90 backdrop-blur-lg">
            © <?php echo e(date('Y')); ?> Evangelismo Explosivo no Brasil
        </div>
    </div>
</div>
<?php /**PATH /home/cleiton/workspaces/ee/resources/views/components/web/navigation/menu-mobile.blade.php ENDPATH**/ ?>