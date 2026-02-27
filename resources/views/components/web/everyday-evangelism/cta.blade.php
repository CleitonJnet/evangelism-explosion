@php
    $colors = [
        'gold' => ['hex' => '#c7a840'], // dourado EE
        'gold_dark' => ['hex' => '#8a7424'],
        'blue' => ['hex' => '#2f318f'], // azul institucional EE
    ];
@endphp

{{-- CTA FINAL --}}
<section class="py-16 md:py-24"
    style="background:
        radial-gradient(900px 500px at 15% 10%, rgba(199,168,64,.14), transparent 55%),
        radial-gradient(800px 450px at 85% 15%, rgba(138,116,36,.18), transparent 55%),
        linear-gradient(180deg, #082f49 0%, #05273d 55%, #041b2d 100%);">
    {{-- ~ bg-sky-950 --}}

    <div class="px-4 max-w-8xl mx-auto sm:px-6 lg:px-8">
        <div
            class="p-6 sm:p-10 rounded-3xl ring-1 ring-white/10
                   bg-white/5 backdrop-blur
                   shadow-[0_25px_60px_rgba(0,0,0,.55)]">

            <div class="max-w-4xl">
                <h2 class="text-2xl font-extrabold tracking-tight text-white sm:text-3xl">
                    Pronto para iniciar?
                </h2>

                <p class="mt-4 leading-relaxed text-slate-200">
                    Comece pelo <strong>Workshop ESM</strong>, organize um grupo pequeno e avance com fidelidade no
                    ciclo
                    <strong>ensino + prática</strong>.
                    O Evangelho continua poderoso, e Deus honra igrejas que levam a Grande Comissão a sério.
                </p>

                <div class="flex flex-wrap gap-3 mt-8">
                    <x-src.btn-gold label="Ver agenda e inscrever igreja" route="#events" />
                    <x-src.btn-silver label="Entender a Clínica de EE" route="#clinic" />
                </div>
            </div>
        </div>
    </div>
</section>
