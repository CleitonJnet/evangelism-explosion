@props(['ministry' => null, 'ministryNot' => null])

@once
    @push('css')
        <style>
            .js-swiper-events .swiper-button-next,
            .js-swiper-events .swiper-button-prev {
                color: #b79a32;
            }

            .js-swiper-events .swiper-button-next:after,
            .js-swiper-events .swiper-button-prev:after {
                font-size: 16px;
                font-weight: 900;
            }

            .js-swiper-events .swiper-pagination-bullet {
                background: #c7a840;
                opacity: .35;
            }

            .js-swiper-events .swiper-pagination-bullet-active {
                background: #f1d57a;
                opacity: 1;
            }

            @keyframes arrow-pulse {

                0%,
                100% {
                    transform: translateX(0);
                }

                50% {
                    transform: translateX(4px);
                }
            }
        </style>
    @endpush

    @push('js')
        <script>
            (function() {
                function initEventsSwiper() {
                    document.querySelectorAll('.SwiperCarouselEvent').forEach((root) => {
                        // evita reinicializar
                        if (root.dataset.swiperInit === '1') return;
                        root.dataset.swiperInit = '1';

                        const pagination = root.querySelector('.swiper-pagination');
                        const nextEl = root.querySelector('.swiper-button-next');
                        const prevEl = root.querySelector('.swiper-button-prev');

                        const slidesCount = root.querySelectorAll('.swiper-slide').length;
                        const isSingleSlide = slidesCount < 2;

                        new Swiper(root, {
                            slidesPerView: isSingleSlide ? 1 : 3,
                            spaceBetween: 10,
                            loop: false,
                            grabCursor: true,
                            autoplay: false,
                            pagination: {
                                el: ".swiper-pagination",
                                dynamicBullets: true,
                            },
                            navigation: {
                                nextEl,
                                prevEl
                            },
                            breakpoints: isSingleSlide ? {} : {
                                320: {
                                    slidesPerView: 1,
                                    spaceBetween: 6
                                },
                                640: {
                                    slidesPerView: 2,
                                    spaceBetween: 6
                                },
                                870: {
                                    slidesPerView: 3,
                                    spaceBetween: 10
                                },
                                1280: {
                                    slidesPerView: 4,
                                    spaceBetween: 30
                                }
                            }
                        });
                    });
                }

                // primeira carga
                document.addEventListener('DOMContentLoaded', initEventsSwiper);

                // se estiver usando Livewire com navegação (SPA-like), re-init após navegar
                document.addEventListener('livewire:navigated', initEventsSwiper);
            })
            ();
        </script>
    @endpush
@endonce

<div class="relative">
    <div class="px-3! swiper js-swiper-events SwiperCarouselEvent">

        <livewire:swiper-wrapper-events :ministry="$ministry" :ministry-not="$ministryNot" />

        <div class="swiper-button-prev -left-2!"></div>
        <div class="swiper-button-next -right-2!"></div>
        <div class="swiper-pagination relative! mt-6!"></div>
    </div>
</div>
