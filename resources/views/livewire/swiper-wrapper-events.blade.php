<div class="items-center swiper-wrapper">

    @foreach ($events as $event)
        @php
            $eventDate = $event->eventDates->first();
            $course = $event->course;
            $category = $course?->ministry?->name ?? 'Outros';
            $type = $course?->type ?? 'Treinamento';
            $eventName = $course?->name ?? 'Treinamento';
            $date = $eventDate?->date
                ? \Illuminate\Support\Carbon::parse($eventDate->date)->format('d/m')
                : 'A definir';
            $startTime = $eventDate?->start_time
                ? \Illuminate\Support\Carbon::parse($eventDate->start_time)->format('H:i')
                : '--:--';

            $free = str_replace(',', '.', str_replace(['R$', ' ', '.'], '', $event->payment)) > 0;

            $canAccessPublicSchedule = \App\Helpers\DayScheduleHelper::hasAllDaysMatch(
                $event->eventDates,
                $event->scheduleItems,
            );
            $schedule = !empty($canAccessPublicSchedule);

            $bannerPath = is_string($event->banner) ? trim($event->banner) : '';
            $bannerExtension = strtolower(pathinfo($bannerPath, PATHINFO_EXTENSION));
            $allowedImageExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'bmp', 'svg'];
            $hasBannerImage =
                $bannerPath !== '' &&
                in_array($bannerExtension, $allowedImageExtensions, true) &&
                Storage::disk('public')->exists($bannerPath);
            $bannerDownloadUrl = $hasBannerImage ? route('web.event.banner.download', ['id' => $event->id]) : null;

        @endphp

        @if (!$course)
            @continue
        @endif

        <x-src.carousel-item :category="$category" :type="$type" :event="$eventName" :date="$date" :start_time="$startTime"
            :city="$event->city" :state="$event->state" :route="route('web.event.details', $event->id)" :schedule="$schedule" :free="$free"
            :banner="$bannerDownloadUrl" />
    @endforeach

    @if ($showScheduleRequestCard)
        <div class="swiper-slide">
            <a href="{{ route('web.event.schedule-request') }}"
                class="relative flex flex-col justify-center p-3 overflow-hidden text-center text-white transition border shadow-lg shine border-white/10 h-72 group rounded-2xl backdrop-blur-md ring-1 ring-white/10 shadow-black/30"
                style="background: linear-gradient(180deg, #082f49 0%, #05273d 55%, #041b2d 100%);">

                <div class="absolute inset-x-0 top-0 h-0.75 bg-linear-to-r from-[#8a7424] via-[#c7a840] to-[#f1d57a]">
                </div>

                <h4 class="text-2xl" style="font-family: 'Cinzel', serif;">
                    Agende um Treinamento Local
                </h4>
                <p class="mt-2">
                    Você pode agendar um treinamento de Líderes em sua igreja.
                </p>

                <div class="flex items-center justify-center gap-1.5 font-bold text-amber-200"
                    style="text-shadow: 1px 1px 1px black">
                    Veja como.
                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="w-4 h-4 opacity-0 -translate-x-1 group-hover:opacity-100 group-hover:translate-x-0
                                   group-hover:animate-[arrow-pulse_800ms_ease-in-out_infinite]"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        style="filter: drop-shadow(1px 1px 1px #000000ab)">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </div>

                <div aria-hidden="true"
                    class="absolute inset-x-0 bottom-0 h-1 pointer-events-none bg-linear-to-r from-transparent via-amber-500 to-transparent">
                </div>
            </a>
        </div>
    @endif
</div>
