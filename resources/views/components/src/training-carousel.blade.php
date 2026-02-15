@props(['items', 'role'])

@once
    @push('css')
        <style>
            .js-swiper-trainings .swiper-button-next,
            .js-swiper-trainings .swiper-button-prev {
                color: #b79a32;
            }

            .js-swiper-trainings .swiper-button-next:after,
            .js-swiper-trainings .swiper-button-prev:after {
                font-size: 16px;
                font-weight: 900;
            }

            .js-swiper-trainings .swiper-pagination-bullet {
                background: #c7a840;
                opacity: .35;
            }

            .js-swiper-trainings .swiper-pagination-bullet-active {
                background: #f1d57a;
                opacity: 1;
            }
        </style>
    @endpush

    @push('js')
        <script>
            (function() {
                function initTrainingSwiper() {
                    document.querySelectorAll('.SwiperCarouselTraining').forEach((root) => {
                        if (root.dataset.swiperInit === '1') return;
                        root.dataset.swiperInit = '1';

                        const nextEl = root.querySelector('.swiper-button-next');
                        const prevEl = root.querySelector('.swiper-button-prev');

                        const slidesCount = root.querySelectorAll('.swiper-slide').length;
                        const isSingleSlide = slidesCount < 1;

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
                                // 320: {
                                //     slidesPerView: 1,
                                //     spaceBetween: 6
                                // },
                                640: {
                                    slidesPerView: 1,
                                    spaceBetween: 6
                                },
                                870: {
                                    slidesPerView: 2,
                                    spaceBetween: 10
                                },
                                1280: {
                                    slidesPerView: 3,
                                    spaceBetween: 30
                                }
                            }
                        });
                    });
                }

                document.addEventListener('DOMContentLoaded', initTrainingSwiper);
                document.addEventListener('livewire:navigated', initTrainingSwiper);
            })
            ();
        </script>
    @endpush
@endonce

<div class="relative">
    <div class="px-3! swiper js-swiper-trainings SwiperCarouselTraining">
        <div class="swiper-wrapper">
            @foreach ($items as $item)
                @php
                    $training = $item['training'];
                    $dates = $item['dates'];
                    $course = $training->course;
                    $eventDate = $dates->first();
                    $category = $course?->ministry?->name ?? 'Treinamento';
                    $type = $course?->type ?? 'Treinamento';
                    $eventName = $course?->name ?? 'Treinamento';
                    $date = $eventDate?->date
                        ? \Illuminate\Support\Carbon::parse($eventDate->date)->format('d/m')
                        : 'A definir';
                    $startTime = $eventDate?->start_time
                        ? \Illuminate\Support\Carbon::parse($eventDate->start_time)->format('H:i')
                        : '--:--';

                    // SOBRE O TREINAMENTO
                    $free = str_replace(',', '.', str_replace(['R$', ' ', '.'], '', $item['training']->payment)) > 0;

                    $canAccessPublicSchedule = \App\Helpers\DayScheduleHelper::hasAllDaysMatch(
                        $item['training']->eventDates,
                        $item['training']->scheduleItems,
                    );
                    $schedule = !empty($canAccessPublicSchedule);

                    $bannerPath = is_string($item['training']->banner) ? trim($item['training']->banner) : '';
                    $bannerExtension = strtolower(pathinfo($bannerPath, PATHINFO_EXTENSION));
                    $allowedImageExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'bmp', 'svg'];
                    $hasBannerImage =
                        $bannerPath !== '' &&
                        in_array($bannerExtension, $allowedImageExtensions, true) &&
                        Storage::disk('public')->exists($bannerPath);
                    $bannerDownloadUrl = $hasBannerImage
                        ? route('web.event.banner.download', ['id' => $item['training']->id])
                        : null;

                    $free = str_replace(',', '.', str_replace(['R$', ' ', '.'], '', $item['training']->payment)) > 0;

                    $canAccessPublicSchedule = \App\Helpers\DayScheduleHelper::hasAllDaysMatch(
                        $item['training']->eventDates,
                        $item['training']->scheduleItems,
                    );
                    $schedule = !empty($canAccessPublicSchedule);

                    $bannerPath = is_string($item['training']->banner) ? trim($item['training']->banner) : '';
                    $bannerExtension = strtolower(pathinfo($bannerPath, PATHINFO_EXTENSION));
                    $allowedImageExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'bmp', 'svg'];
                    $hasBannerImage =
                        $bannerPath !== '' &&
                        in_array($bannerExtension, $allowedImageExtensions, true) &&
                        Storage::disk('public')->exists($bannerPath);
                    $bannerDownloadUrl = $hasBannerImage
                        ? route('web.event.banner.download', ['id' => $item['training']->id])
                        : null;

                @endphp

                <x-src.carousel-item :category="$category" :type="$type" :event="$eventName" :date="$date"
                    :start_time="$startTime" :city="$training->city" :state="$training->state" :route="route('app.' . $role . '.trainings.show', $training->id)" :schedule="$schedule"
                    :free="$free" :banner="$bannerDownloadUrl" />
            @endforeach
        </div>
        <div class="swiper-button-prev -left-2!"></div>
        <div class="swiper-button-next -right-2!"></div>
        <div class="swiper-pagination relative! mt-6!"></div>
    </div>
</div>
