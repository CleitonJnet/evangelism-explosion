@php
    use Illuminate\Support\Facades\Storage;

    $title = __('Eventos & treinamentos');
    $description =
        'Confira ou agende eventos e treinamentos do ministério Evangelismo Explosivo no Brasil, participando da expansão do Evangelho.';
    $keywords = 'agenda, eventos, treinamentos, evangelismo, EE Brasil';
    $ogImage = asset('images/leadership-meeting.webp');
    $churchAddress = implode(
        ', ',
        array_filter([
            $event->street ?? null,
            $event->number ?? null,
            $event->district ?? null,
            $event->city ?? null,
            $event->state ?? null,
        ]),
    );
    $isEnrolled = auth()->check() && $event->students()->whereKey(auth()->id())->exists();
    $eventAccessRoute = $isEnrolled
        ? route('app.student.training.show', ['training' => $event->id])
        : route('web.event.register', ['id' => $event->id]);
    $eventAccessLabel = $isEnrolled ? 'Acessar evento' : 'Fazer inscrição';
    $bannerPath = is_string($event->banner) ? trim($event->banner) : '';
    $bannerExtension = strtolower(pathinfo($bannerPath, PATHINFO_EXTENSION));
    $allowedImageExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'bmp', 'svg'];
    $hasBannerImage = $bannerPath !== ''
        && in_array($bannerExtension, $allowedImageExtensions, true)
        && Storage::disk('public')->exists($bannerPath);
    $bannerDownloadUrl = $hasBannerImage ? route('web.event.banner.download', ['id' => $event->id]) : null;
@endphp

<x-layouts.guest :title="$title" :description="$description" :keywords="$keywords" :ogImage="$ogImage">

    <x-web.header :title="'<div>' . $event->course->type . ': </div><div class=`text-nowrap>' . $event->course->name . '</div>'"
        subtitle='Conheça os objetivos, a estrutura e os valores deste treinamento, preparado para fortalecer a igreja na missão de fazer discípulos.'
        :cover="asset('images/leadership-meeting.webp')" />

    {{-- HERO + RESUMO (grade alinhada) --}} <section class="px-4 mx-auto max-w-8xl sm:px-6 lg:px-8">
        <div class="grid items-start gap-8 lg:grid-cols-12">

            {{-- Card principal --}}
            <div class="lg:col-span-7" data-reveal>
                <div class="overflow-hidden bg-white border shadow-sm rounded-3xl ring-1 ring-slate-900/10">
                    <div class="relative">
                        {{-- Imagem do evento (exemplo) --}}
                        <img src="{{ asset('images/cover.jpg') }}" alt="Workshop O Evangelho Em Sua Mão"
                            class="object-cover w-full h-72 sm:h-80">
                        <div
                            class="absolute inset-x-0 bottom-0 h-2 bg-linear-to-r from-[#f1d57a] via-[#c7a840] to-[#8a7424]">
                        </div>
                    </div>

                    <div class="p-6 sm:p-8">
                        <div class="flex flex-wrap items-center gap-2">
                            <span
                                class="inline-flex items-center px-3 py-1 text-xs font-semibold border rounded-full bg-slate-50 text-slate-700 border-slate-200">
                                Carga horária: <span
                                    class="ml-1 font-bold text-slate-900">{{ $workloadDuration ?? '00h' }}</span>
                            </span>
                            <span
                                class="inline-flex items-center px-3 py-1 text-xs font-semibold border rounded-full bg-slate-50 text-slate-700 border-slate-200">
                                Investimento: <span class="ml-1 font-bold text-amber-900">
                                    {{ $event->payment }}</span>
                            </span>
                        </div>

                        <h1 id="page-title"
                            class="mt-8 text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl lg:text-5xl">
                            <div class="text-2xl font-semibold">
                                {{ $event->course->type }}
                            </div>
                            {{ $event->course->name }}
                        </h1>

                        <p class="mt-2 text-slate-600">
                            {{ $event->course->slogan }}
                        </p>

                        <div class="flex justify-end">
                            <x-src.btn-silver label="Detalhes do ministério" :route="$event->course->learnMoreLink" target="_blanc" />
                        </div>

                        {{-- Datas/horários bem legíveis --}}
                        <div class="grid gap-3 mt-6 sm:grid-cols-2">

                            @foreach ($event->eventDates as $date_event)
                                <div class="p-4 border rounded-2xl bg-slate-50 border-slate-200">
                                    <p class="font-semibold text-slate-900">
                                        <span class="mr-2">&#x1F4C5;</span>
                                        <span class="text-nowrap">
                                            {{ \Illuminate\Support\Str::ucfirst(\Carbon\Carbon::parse($date_event->date)->locale('pt_BR')->isoFormat('dddd')) }}
                                            • {{ date('d/m', strtotime($date_event->date)) }}:
                                        </span>
                                        <span class="font-light text-amber-900 text-nowrap">
                                            das {{ date('H:i', strtotime($date_event->start_time)) }} às
                                            {{ date('H:i', strtotime($date_event->end_time)) }}
                                        </span>
                                    </p>
                                </div>
                            @endforeach

                        </div>
                        <div class="mt-2 text-xs text-right text-slate-500">Horário de Brasília</div>
                        {{-- Descrição --}}
                        <div class="p-5 mt-6 bg-white border rounded-2xl border-slate-200">
                            <h2 class="text-lg text-slate-900" style="font-family:'Cinzel', serif;">Descrição</h2>
                            <p class="mt-2 text-sm leading-relaxed text-slate-700">
                                {{ $event->course->description }}
                            </p>
                        </div>

                        {{-- Público-alvo --}}
                        <div class="p-5 mt-6 border rounded-2xl bg-slate-50 border-slate-200">
                            <h2 class="text-lg text-slate-900" style="font-family:'Cinzel', serif;">Público-alvo
                            </h2>
                            <div class="mt-2 space-y-2 text-sm list-disc list-inside text-slate-700">
                                {{ $event->course->targetAudience }}
                            </div>
                        </div>

                        {{-- Botões --}}
                        @if ($isEnrolled)
                            <div class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50/80 p-4 text-sm text-emerald-900">
                                Você já está inscrito neste evento. Caso precise atualizar seus dados, acesse sua área
                                do aluno.
                            </div>
                        @endif

                        <div class="flex flex-col gap-3 mt-6 sm:flex-row sm:items-center">
                            <x-src.btn-gold :label="$eventAccessLabel" :route="$eventAccessRoute" />

                            @if ($bannerDownloadUrl)
                                <x-src.btn-silver label="Baixar cartaz" :route="$bannerDownloadUrl" download />
                            @endif

                            <x-src.btn-silver label="Ver programação" :route="route('web.event.schedule', $event->id)" />
                        </div>
                    </div>
                </div>
            </div>

            {{-- Coluna lateral --}}
            <aside class="space-y-6 lg:col-span-5" data-reveal>

                {{-- Local --}}
                <div class="p-6 bg-white border shadow-sm rounded-3xl ring-1 ring-slate-900/10">
                    <h2 class="text-lg text-slate-900" style="font-family:'Cinzel', serif;">Local</h2>
                    <div class="mt-3 space-y-2">
                        <div class="p-4 border rounded-2xl bg-slate-50 border-slate-200">
                            <p class="mt-1 text-lg font-bold text-center text-slate-700">{{ $event->church->name }}</p>
                        </div>

                        <div class="p-4 border rounded-2xl bg-slate-50 border-slate-200">
                            <p class="text-sm font-semibold text-slate-900">
                                <span class="mr-2">&#x1F4CD;</span>
                                Endereço
                            </p>
                            <a class="mt-1 text-sm text-slate-700" target="_blank" rel="noopener" target="_blank"
                                href="https://www.google.com/maps/search/?api=1&query={{ urlencode($churchAddress) }}">
                                {{ $event->street }}, {{ $event->number }},
                                {{ $event->district }}, {{ $event->city }},
                                {{ $event->state }}
                            </a>
                        </div>
                    </div>

                    <div class="mt-4 overflow-hidden border rounded-2xl border-slate-200 bg-slate-50">
                        <iframe class="w-full h-72" loading="lazy" referrerpolicy="no-referrer-when-downgrade"
                            src="https://www.google.com/maps?q={{ urlencode($churchAddress) }}&output=embed&hl=pt-BR"></iframe>
                    </div>

                    <x-src.btn-silver label="Abrir no Google Maps" target="blank" :route="'https://www.google.com/maps/search/?api=1&query=' . urlencode($churchAddress)"
                        class="py-2! w-full mt-1" />
                </div>

                {{-- Informações --}}
                <div class="p-6 bg-white border shadow-sm rounded-3xl ring-1 ring-slate-900/10">
                    <h2 class="pb-2 text-lg text-slate-900" style="font-family:'Cinzel', serif;">Contato</h2>
                    <div class="grid gap-3">
                        <div class="p-4 border rounded-2xl bg-slate-50 border-slate-200">
                            <p class="text-sm font-semibold text-slate-900">{{ $event->coordinator }}</p>
                            <div class="mt-1 space-y-1 text-sm text-slate-700">
                                <p>
                                    Telefone:
                                    {{ $event->phone }}
                                </p>

                                <p>
                                    E-mail: {{ $event->email }}
                                </p>
                            </div>
                        </div>

                        {{-- Linha temática --}}
                        <div class="my-3 h-0.5 w-full mx-auto lg:mx-0"
                            style="border-radius: 100%; background: linear-gradient(135deg,
                                #c7a8401a,
                                #c7a8408c,
                                #c7a8401a);">
                        </div>

                        <div class="p-4 border rounded-2xl bg-amber-50 border-amber-200">
                            <p class="text-sm font-semibold text-amber-900">Investimento</p>
                            <p class="mt-1 text-sm text-amber-900">
                                <span class="font-extrabold">{{ $event->payment }}</span>
                                <span class="text-amber-800/80">por participante</span>
                            </p>
                        </div>
                    </div>
                </div>

                {{-- COUNTDOWN + STATUS DO EVENTO --}}
                <div class="max-w-sm bg-sky-950"></div>

            </aside>
        </div>
    </section>

    <x-web.home.list-events :ministry_id="$event->course->ministry_id" />

    {{-- CTA fixo (sempre disponível) --}}
    <x-web.events.bar-fixed-cta :course_name="$event->course->name" :course_type="$event->course->type" :start_time="$event->eventDates()->first()->start_time" :end_time="$event->eventDates()->first()->end_time"
        :date="$event->eventDates()->first()->date" :banner="$bannerDownloadUrl" :route="$eventAccessRoute"
        :label="$eventAccessLabel" />


</x-layouts.guest>
