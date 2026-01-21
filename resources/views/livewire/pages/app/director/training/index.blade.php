<div>
    <div class="flex flex-col gap-6">
        @foreach ($statusLabels as $status => $label)
            @php
                $items = $trainingsByStatus[$status] ?? collect();
            @endphp

            <section class="flex flex-col gap-4">
                <div class="flex items-center gap-2">
                    <h2 class="text-lg font-semibold">{{ $label }}</h2>
                    <span class="text-sm text-slate-500">({{ $items->count() }})</span>
                </div>

                @if ($items->isEmpty())
                    <div class="text-sm text-slate-500">Sem eventos.</div>
                @else
                    <!-- Swiper -->
                    <div class="swiper w-full SwiperTrainings">
                        <div class="swiper-wrapper">
                            @foreach ($items as $item)
                                @php
                                    $training = $item['training'];
                                    $coordinatorName = $training->coordinator;
                                    $dates = $item['dates'];
                                    $courseType = data_get($training, 'course.type', 'Nao informado');
                                    $courseName = data_get($training, 'course.name', 'Nao informado');
                                    $teacherName = data_get($training, 'teacher.name', 'Nao informado');
                                    $churchName = data_get($training, 'church.name', 'Nao informado');
                                    $pastorName = data_get($training, 'church.pastor', 'Nao informado');
                                    $addressParts = array_filter([
                                        $training?->street,
                                        $training?->number,
                                        $training?->complement,
                                        $training?->district,
                                        $training?->city,
                                        $training?->state,
                                        $training?->postal_code,
                                    ]);
                                    $address = $addressParts ? implode(', ', $addressParts) : 'Endereco nao informado';
                                @endphp

                                <div wire:key="training-{{ $training->id }}-status-{{ $status }}"
                                    class="flex flex-col gap-3 rounded border border-slate-800 bg-white p-4 shadow-sm swiper-slide w-fit">
                                    <div class="flex items-start justify-between gap-4">
                                        <div class="text-base font-semibold text-slate-900">
                                            {{ $courseType . ': ' . $courseName }}
                                        </div>
                                    </div>

                                    <div class="flex flex-col gap-1 text-sm text-slate-700">
                                        <div><span class="font-semibold">Base de Treinamento:</span>
                                            {{ $churchName }}
                                        </div>
                                        <div><span class="font-semibold">Pastor:</span> {{ $pastorName }}</div>
                                        <div><span class="font-semibold">Coordenado:</span> {{ $coordinatorName }}
                                        </div>
                                        <div><span class="font-semibold">Professor:</span> {{ $teacherName }}</div>
                                        <div><span class="font-semibold">Endereco:</span> {{ $address }}</div>
                                    </div>

                                    <div class="flex flex-col gap-1 text-sm text-slate-600">
                                        <div class="font-semibold">Datas:</div>
                                        <ul class="flex flex-col gap-1">
                                            @foreach ($dates as $eventDate)
                                                @php
                                                    $dateLabel = $eventDate->date
                                                        ? \Illuminate\Support\Carbon::parse($eventDate->date)->format(
                                                            'd/m/Y',
                                                        )
                                                        : '-';
                                                    $startTime = $eventDate->start_time
                                                        ? \Illuminate\Support\Carbon::parse(
                                                            $eventDate->start_time,
                                                        )->format('H:i')
                                                        : '-';
                                                    $endTime = $eventDate->end_time
                                                        ? \Illuminate\Support\Carbon::parse(
                                                            $eventDate->end_time,
                                                        )->format('H:i')
                                                        : '-';
                                                @endphp
                                                <li>
                                                    <span class="font-semibold">{{ $dateLabel }}</span>
                                                    <span>({{ $startTime }} - {{ $endTime }})</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="swiper-pagination"></div>
                    </div>

                    <!-- Initialize Swiper -->
                    <script>
                        var swiper = new Swiper(".SwiperTrainings", {
                            slidesPerView: "auto",
                            spaceBetween: 30,
                            pagination: {
                                el: ".swiper-pagination",
                                clickable: true,
                            },
                        });
                    </script>
                @endif
            </section>
        @endforeach
    </div>
</div>
