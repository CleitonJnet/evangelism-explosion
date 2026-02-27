@php
    $title = 'Formulário de Inscrição';
    $description =
        'Inscreva-se para participar deste treinamento e dar um passo intencional no cumprimento da Grande Comissão.';
    $keywords = 'base de treinamento, evangelismo explosivo, implementação, discipulado, mentoria';
    $ogImage = asset('images/leadership-meeting.webp');
@endphp

<x-layouts.guest :title="$title" :description="$description" :keywords="$keywords" :ogImage="$ogImage">
    <!-- ===================== Inscrição do Evento ===================== -->
    <section class="relative bg-radial from-slate-800 via-sky-900 to-slate-950 -mb-10">
        <x-web.header :title="'<div>' .
            $event->course->type .
            ': </div><div class=`text-nowrap>' .
            $event->course->name .
            '</div>'" :subtitle="$event->church->name .'<br>'.$event->city .' - '.$event->state"
            :cover="asset('images/leadership-meeting.webp')" />

        <div class="px-4 py-12 max-w-8xl mx-auto sm:px-6 lg:px-8">
            <div class="grid gap-8 lg:grid-cols-12">

                <!-- ============ COLUNA DIREITA: Formulário ============ -->
                <div class="lg:col-span-full">
                    <div class="overflow-hidden bg-white border shadow-sm rounded-3xl border-slate-200">
                        <div class="h-0.75 bg-linear-to-r from-[#8a7424] via-[#c7a840] to-[#f1d57a]"></div>

                        @livewire('web.event.access', ['event' => $event, 'mode' => 'login'])
                    </div>
                </div>
            </div>
        </div>
    </section>

</x-layouts.guest>
