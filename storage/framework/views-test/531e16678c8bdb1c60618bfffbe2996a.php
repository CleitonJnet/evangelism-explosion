<div class="wa-widget" id="<?php echo e($uid); ?>" data-wa-phone="<?php echo e($phone); ?>"
    data-wa-ddis='<?php echo json_encode($ddis, 15, 512) ?>'>
    <button type="button" class="wa-button" aria-label="<?php echo e(__('Abrir formulário WhatsApp')); ?>">
        <img src="https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg" alt="WhatsApp">
    </button>

    <div class="wa-modal" role="dialog" aria-modal="true" aria-hidden="true"
        aria-labelledby="wa-title-<?php echo e($uid); ?>">
        <div class="wa-modal-panel" role="document">
            <button class="wa-close" aria-label="<?php echo e(__('Fechar')); ?>">&times;</button>

            <h3 class="wa-title" id="wa-title-<?php echo e($uid); ?>">
                <img src="https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg" alt="WhatsApp"
                    width="30" height="30">
                <?php echo e($title); ?>

            </h3>

            <form class="wa-form" style="background-image: url(<?php echo e(asset('images/bg_whatsapp.webp')); ?>);">
                <p class="wa-detail">
                    Olá! Preencha os campos abaixo para iniciar a conversa no WhatsApp
                </p>

                <div class="wa-field">
                    <i class="bi bi-person wa-icon"></i>
                    <input class="wa-input" name="name" type="text" placeholder="Nome completo *" required>
                </div>

                <div class="wa-field">
                    <i class="bi bi-envelope wa-icon"></i>
                    <input class="wa-input" name="email" type="email" placeholder="E-mail *" required>
                </div>

                
                <div class="wa-phone-group-wrapper">
                    <div class="wa-phone-group">

                        <!-- ******* Input DDI ******* -->
                        <input class="wa-input wa-ddi-input" aria-label="Código do país (DDI)" type="text" readonly>

                        <!-- ******* TELEFONE ******* -->
                        <div class="wa-field wa-phone-field">
                            <i class="bi bi-phone wa-icon"></i>
                            <input class="wa-input wa-phone-visible" aria-label="Telefone" type="tel"
                                id="phone-<?php echo e($uid); ?>" placeholder="(11) 00000-0000 *" inputmode="tel"
                                autocomplete="tel" data-intl-phone="1" required>
                        </div>

                    </div>

                    <!-- ******* Campos escondidos ******* -->
                    <input type="hidden" name="phone_user">
                    <input type="hidden" name="phone_ddi">

                    <!-- ******* Dropdown DDI ******* -->
                    <div class="wa-ddi-dropdown">
                        <input type="search" class="wa-ddi-search" placeholder="Buscar país ou código">
                        <div class="wa-ddi-list" data-wa-ddi-list></div>
                    </div>
                </div>

                <div class="wa-field">
                    <i class="bi bi-house-door wa-icon"></i>
                    <input class="wa-input" name="church" type="text" placeholder="Nome completo da sua igreja *"
                        required>
                </div>

                <div class="wa-divisor"></div>

                <div class="wa-field">
                    <i class="bi bi-chat-left-dots wa-icon"></i>
                    <select class="wa-input" aria-label="Assunto" name="subject" required>
                        <option value="" hidden><?php echo e(__('Selecione o assunto')); ?></option>
                        <?php $__currentLoopData = $subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $text): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($text); ?>"><?php echo e($text); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div class="wa-field wa-textarea-field">
                    <i class="bi bi-pencil-square wa-icon"></i>
                    <textarea class="wa-input" aria-label="Sua Mensagem" name="comment" rows="3" placeholder="Sua Mensagem"></textarea>
                </div>

                <button class="wa-submit" type="submit"><i class="bi bi-whatsapp"></i> Iniciar Conversa</button>
            </form>
        </div>
    </div>
</div>
<?php /**PATH /home/cleiton/workspaces/ee/resources/views/components/web/whatsapp.blade.php ENDPATH**/ ?>