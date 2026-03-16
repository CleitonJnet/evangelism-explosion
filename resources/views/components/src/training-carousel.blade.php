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
                            slidesPerView: 'auto',
                            spaceBetween: 10,
                            loop: false,
                            grabCursor: true,
                            autoplay: false,
                            navigation: {
                                nextEl,
                                prevEl
                            },
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
    <div class="px-1 sm:px-2 md:px-3 swiper js-swiper-trainings SwiperCarouselTraining">
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
                        ? \Illuminate\Support\Carbon::parse($eventDate->date)->format('d/m/Y')
                        : 'A definir';
                    $startTime = $eventDate?->start_time
                        ? \Illuminate\Support\Carbon::parse($eventDate->start_time)->format('H:i')
                        : '--:--';

                    $isPaid = (float) preg_replace('/\D/', '', (string) $item['training']->payment) > 0;
                    $free = ! $isPaid;

                    $canAccessPublicSchedule = \App\Helpers\DayScheduleHelper::hasAllDaysMatch(
                        $item['training']->eventDates,
                        $item['training']->scheduleItems,
                    );
                    $schedule = !empty($canAccessPublicSchedule);

                    $bannerPath = is_string($item['training']->banner) ? trim($item['training']->banner) : '';
                    $bannerExtension = strtolower(pathinfo($bannerPath, PATHINFO_EXTENSION));
                    $allowedImageExtensions = ['webp', 'jpeg', 'png', 'webp', 'gif', 'bmp', 'svg'];
                    $hasBannerImage =
                        $bannerPath !== '' &&
                        in_array($bannerExtension, $allowedImageExtensions, true) &&
                        Storage::disk('public')->exists($bannerPath);
                    $bannerDownloadUrl = $hasBannerImage
                        ? route('web.event.banner.download', ['id' => $item['training']->id])
                        : null;

                    $detailsRoute = match ($role) {
                        'director' => route('app.director.trainings.show', $training->id),
                        'teacher' => route('app.teacher.trainings.show', $training->id),
                        'portal-base-serving' => route('app.portal.base.trainings.context', $training->id),
                        default => \Illuminate\Support\Facades\Route::has('app.' . $role . '.trainings.show')
                            ? route('app.' . $role . '.trainings.show', $training->id)
                            : '#',
                    };
                    $requiresCompletionReview = $training->requiresCompletionReview();
                    $completionReviewAlertMessage = $training->completionReviewAlertMessage();
                    $footerLabel = match ($role) {
                        'director' => $training->teacher?->name ?? __('Professor nao informado'),
                        'portal-base-serving' => __('Abrir contexto.'),
                        default => __('Saiba mais.'),
                    };
                    $studentsCount = (int) ($training->students_count ?? 0);
                @endphp

                <x-src.carousel-item :category="$category" :type="$type" :event="$eventName" :date="$date"
                    :start_time="$startTime" :city="$training->city" :state="$training->state" :route="$detailsRoute" :schedule="$schedule"
                    :free="$free" :banner="$bannerDownloadUrl" :new-churches-count="$training->new_churches_count ?? 0"
                    :alert="$requiresCompletionReview" :alert_message="$completionReviewAlertMessage" :footer_label="$footerLabel"
                    :students-count="$studentsCount" :show-students-count="true" />
            @endforeach
        </div>
        <div class="swiper-button-prev left-0 sm:-left-1 md:-left-2"></div>
        <div class="swiper-button-next right-0 sm:-right-1 md:-right-2"></div>
        <div class="swiper-pagination relative! mt-6!"></div>
    </div>
</div>
