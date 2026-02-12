<div class="wa-widget" id="{{ $uid }}" data-wa-phone="{{ $phone }}"
    data-wa-ddis='@json($ddis)'>
    <button type="button" class="wa-button" aria-label="{{ __('Abrir formulário WhatsApp') }}">
        <img src="https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg" alt="WhatsApp">
    </button>

    <div class="wa-modal" role="dialog" aria-modal="true" aria-hidden="true"
        aria-labelledby="wa-title-{{ $uid }}">
        <div class="wa-modal-panel" role="document">
            <button class="wa-close" aria-label="{{ __('Fechar') }}">&times;</button>

            <h3 class="wa-title" id="wa-title-{{ $uid }}">
                <img src="https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg" alt="WhatsApp"
                    width="30" height="30">
                {{ $title }}
            </h3>

            <form class="wa-form" style="background-image: url({{ asset('images/bg_whatsapp.webp') }});">
                <p class="wa-detail">
                    Olá! Preencha os campos abaixo para iniciar a conversa no WhatsApp
                </p>

                <div class="wa-field">
                    <svg class="wa-icon h-5 w-auto" viewBox="0 0 24 24" aria-hidden="true" fill="none"
                        stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21a8 8 0 0 0-16 0"></path>
                        <circle cx="12" cy="8" r="4"></circle>
                    </svg>
                    <input class="wa-input" name="name" type="text" placeholder="Nome completo *" required>
                </div>

                <div class="wa-field">
                    <svg class="wa-icon h-5 w-auto" viewBox="0 0 24 24" aria-hidden="true" fill="none"
                        stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="5" width="18" height="14" rx="2"></rect>
                        <path d="M3 7l9 6 9-6"></path>
                    </svg>
                    <input class="wa-input" name="email" type="email" placeholder="E-mail *" required>
                </div>

                {{-- ******* DDI + Telefone ******* --}}
                <div class="wa-phone-group-wrapper">
                    <div class="wa-phone-group">

                        <!-- ******* Input DDI ******* -->
                        <input class="wa-input wa-ddi-input" aria-label="Código do país (DDI)" type="text" readonly>

                        <!-- ******* TELEFONE ******* -->
                        <div class="wa-field wa-phone-field">
                            <svg class="wa-icon h-5 w-auto" viewBox="0 0 24 24" aria-hidden="true" fill="none"
                                stroke="currentColor" stroke-width="1.75" stroke-linecap="round"
                                stroke-linejoin="round">
                                <rect x="7" y="2" width="10" height="20" rx="2"></rect>
                                <line x1="11" y1="18" x2="13" y2="18"></line>
                            </svg>
                            <input class="wa-input wa-phone-visible" aria-label="Telefone" type="tel"
                                id="phone-{{ $uid }}" placeholder="(11) 00000-0000 *" inputmode="tel"
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
                    <svg class="wa-icon h-5 w-auto" viewBox="0 0 24 24" aria-hidden="true" fill="none"
                        stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 11l9-7 9 7"></path>
                        <path d="M5 10v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V10"></path>
                        <path d="M10 21v-6h4v6"></path>
                    </svg>
                    <input class="wa-input" name="church" type="text"
                        placeholder="Nome completo da sua igreja *" required>
                </div>

                <div class="wa-divisor"></div>

                <div class="wa-field">
                    <svg class="wa-icon h-5 w-auto" viewBox="0 0 24 24" aria-hidden="true" fill="none"
                        stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z"></path>
                        <circle cx="8.5" cy="11" r="1"></circle>
                        <circle cx="12" cy="11" r="1"></circle>
                        <circle cx="15.5" cy="11" r="1"></circle>
                    </svg>
                    <select class="wa-input" aria-label="Assunto" name="subject" required>
                        <option value="" hidden>{{ __('Selecione o assunto') }}</option>
                        @foreach ($subjects as $text)
                            <option value="{{ $text }}">{{ $text }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="wa-field wa-textarea-field">
                    <svg class="wa-icon h-5 w-auto" viewBox="0 0 24 24" aria-hidden="true" fill="none"
                        stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 20h9"></path>
                        <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"></path>
                    </svg>
                    <textarea class="wa-input" aria-label="Sua Mensagem" name="comment" rows="3" placeholder="Sua Mensagem"></textarea>
                </div>

                <button class="wa-submit" type="submit">
                    <svg class="wa-icon h-5 w-auto" viewBox="0 0 24 24" aria-hidden="true" fill="none"
                        stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 20l1.5-4.5A9 9 0 1 1 12 21a9 9 0 0 1-5-1.5L3 20z"></path>
                        <path
                            d="M9.5 8.5c.4-.4 1-.4 1.4 0l1 1c.4.4.4 1 0 1.4l-.7.7a6 6 0 0 0 2.8 2.8l.7-.7c.4-.4 1-.4 1.4 0l1 1c.4.4.4 1 0 1.4l-.8.8c-.6.6-1.5.8-2.3.5a9 9 0 0 1-6-6c-.3-.8-.1-1.7.5-2.3l.8-.8z">
                        </path>
                    </svg>
                    Iniciar Conversa
                </button>
            </form>
        </div>
    </div>
</div>
