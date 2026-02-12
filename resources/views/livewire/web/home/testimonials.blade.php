@push('css')
    <style>
        .Swiper-Testimonials .swiper-button-next,
        .Swiper-Testimonials .swiper-button-prev {
            color: #8a7424;
        }

        .Swiper-Testimonials .swiper-button-next:after,
        .Swiper-Testimonials .swiper-button-prev:after {
            font-size: 16px;
            font-weight: 900;
        }

        .Swiper-Testimonials .swiper-pagination-bullet {
            background: #c7a840;
            opacity: .35;
        }

        .Swiper-Testimonials .swiper-pagination-bullet-active {
            opacity: 1;
        }

        .photo {
            box-shadow: 0px -4px 0 4px #c7a840;
        }

        @media (min-width: 768px) {
            .photo {
                box-shadow: -4px 0 0 4px #c7a840;
            }
        }
    </style>
@endpush

<x-web.container id="testimonials" class="overflow-hidden">

    <div aria-hidden="true"
        class="absolute w-1/3 rounded-full pointer-events-none -top-24 -right-24 h-4/5 blur-3xl opacity-70"
        style="background-image: radial-gradient(circle at 30% 30%,rgb(241, 213, 122),transparent 90%);">
    </div>

    <div aria-hidden="true"
        class="absolute w-1/3 rounded-full pointer-events-none -bottom-24 -left-24 h-4/5 blur-3xl opacity-70"
        style="background-image: radial-gradient(circle at 30% 30%,rgb(241, 213, 122),transparent 90%);">
    </div>

    <h2 class="relative flex justify-center pt-10 text-3xl text-center sm:text-4xl lg:text-5xl text-slate-900"
        style="font-family:'Cinzel', serif;">
        Testemunhos
        {{-- Linha temática (dourado metálico) --}}
        <span
            class="absolute -bottom-2 h-0.5 w-[min(28rem,100%)]
                           bg-linear-to-r from-[#b79d46] via-[#c7a84099] to-[#8a742455] opacity-90">
        </span>
    </h2>


    <p class="max-w-2xl pt-3 pb-10 mx-auto text-sm text-center sm:pb-14 sm:text-base text-slate-600">
        Relatos de pessoas que conheceram o Evangelismo Explosivo e cresceram ao viver o evangelismo e mentorear
        outros.
    </p>

    <div class="swiper Swiper-Testimonials">
        <div class="swiper-wrapper">

            {{-- Slides --}}
            @foreach ($testimonials as $t)
                <div class="relative swiper-slide">

                    <figure
                        class="relative w-full overflow-hidden bg-white border shadow-md rounded-2xl border-amber-500/20 shadow-black/5">
                        <div aria-hidden="true"
                            class="pointer-events-none absolute -bottom-10 -right-10 h-40 w-40 rounded-full blur-2xl opacity-60 bg-[radial-gradient(circle_at_30%_30%,rgba(241,213,122,.33),transparent_100%)]">
                        </div>

                        <div class="p-4 sm:p-6 lg:p-10">
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-12 md:items-stretch">

                                {{-- Foto --}}
                                <div class="relative overflow-hidden bg-center bg-cover border-4 border-white rounded-lg md:col-span-5 lg:col-span-4 photo"
                                    style="aspect-ratio: 16 / 11; background-image: url({{ asset('images/profile.webp') }})">
                                    {{-- Texto na foto --}}
                                    <div
                                        class="absolute inset-x-0 bottom-0 px-4 py-3 bg-linear-to-t from-black via-black/50 to-transparent">
                                        <div class="text-sm font-semibold text-white truncate sm:text-base drop-shadow">
                                            {{ $t['name'] }}
                                        </div>
                                        <div class="text-xs text-white truncate sm:text-sm drop-shadow">
                                            {{ $t['meta'] }}
                                        </div>
                                    </div>
                                </div>

                                {{-- Conteúdo --}}
                                <div class="flex flex-col justify-center md:col-span-7 lg:col-span-8">

                                    {{-- Comentário mais destacado --}}
                                    <div
                                        class="z-10 flex items-start gap-2 p-5 overflow-hidden bg-white border shadow-sm rounded-2xl border-amber-500/15 sm:p-6 lg:p-7 shadow-black/5">
                                        <span class="text-6xl leading-none select-none text-amber-700/80">“</span>

                                        <blockquote
                                            class="text-slate-900 leading-relaxed text-base sm:text-base lg:text-lg font-semibold tracking-[0.01em]">
                                            {{ $t['quote'] }}
                                        </blockquote>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </figure>

                </div>
            @endforeach

        </div>

        {{-- Controles --}}
        <div class="swiper-button-prev !left-2"></div>
        <div class="swiper-button-next !right-2"></div>
        <div class="swiper-pagination !relative !mt-6"></div>
    </div>
</x-web.container>

@push('js')
    <script>
        var swiper_testimonials = new Swiper(".Swiper-Testimonials", {
            slidesPerView: 1,
            spaceBetween: 5,
            loop: true,
            autoplay: {
                delay: 2500,
                disableOnInteraction: true,
            },
            observer: true,
            observeParents: true,
            updateOnWindowResize: true,
            pagination: {
                el: ".Swiper-Testimonials .swiper-pagination",
                clickable: true,
                dynamicBullets: true,
            },
            navigation: {
                nextEl: ".Swiper-Testimonials .swiper-button-next",
                prevEl: ".Swiper-Testimonials .swiper-button-prev",
            },
        });
    </script>
@endpush
