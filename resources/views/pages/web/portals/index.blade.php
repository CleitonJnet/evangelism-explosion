@php
    $title = 'Portais da Plataforma Ministerial';
    $description = 'Conheça os três portais da plataforma ministerial do EE no Brasil: Base e Treinamentos, Staff / Governança e Aluno.';
    $keywords = 'portais, plataforma ministerial, base e treinamentos, staff, governança, aluno';
    $ogImage = asset('images/og/home.webp');
@endphp

<x-layouts.guest :title="$title" :description="$description" :keywords="$keywords" :ogImage="$ogImage" class="pb-14">
    <x-web.header
        title="Os 3 Portais da <span class='text-nowrap'>Plataforma Ministerial</span>"
        subtitle="Uma entrada pública mais clara para operação de base, governança institucional e jornada do aluno."
        :cover="asset('images/bg_welcome/photo1.webp')" />

    <x-web.container class="mt-10 space-y-10">
        <section class="rounded-[2rem] border border-slate-200 bg-white/90 p-8 shadow-sm">
            <div class="max-w-3xl">
                <div class="text-xs font-extrabold uppercase tracking-[0.28em] text-amber-700">{{ __('Ecossistema') }}</div>
                <h2 class="mt-3 text-3xl font-semibold text-slate-950">{{ __('Cada portal foi desenhado para um papel diferente') }}</h2>
                <p class="mt-4 text-base leading-7 text-slate-700">
                    {{ __('Em vez de uma única entrada confusa, o site público agora mostra com clareza quem entra em cada área da plataforma e como essa jornada se conecta ao ministério como um todo.') }}
                </p>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-3">
            @foreach ($portalCards as $portalCard)
                @php
                    $accentClasses = match ($portalCard['tone']) {
                        'amber' => 'border-amber-200 bg-amber-50/80',
                        'slate' => 'border-slate-200 bg-slate-50/80',
                        default => 'border-sky-200 bg-sky-50/80',
                    };
                @endphp

                <article class="flex h-full flex-col rounded-[2rem] border p-7 shadow-sm {{ $accentClasses }}">
                    <div class="text-xs font-extrabold uppercase tracking-[0.24em] text-slate-500">{{ $portalCard['eyebrow'] }}</div>
                    <h3 class="mt-3 text-2xl font-semibold text-slate-950">{{ $portalCard['headline'] }}</h3>
                    <p class="mt-3 text-sm leading-6 text-slate-700">{{ $portalCard['description'] }}</p>

                    <div class="mt-6 text-xs font-extrabold uppercase tracking-[0.2em] text-slate-500">{{ __('Quem usa') }}</div>
                    <ul class="mt-3 space-y-2 text-sm text-slate-700">
                        @foreach ($portalCard['who_uses'] as $userType)
                            <li>{{ __('• :item', ['item' => $userType]) }}</li>
                        @endforeach
                    </ul>

                    <div class="mt-6 text-xs font-extrabold uppercase tracking-[0.2em] text-slate-500">{{ __('O que resolve') }}</div>
                    <ul class="mt-3 space-y-2 text-sm text-slate-700">
                        @foreach ($portalCard['what_it_solves'] as $solution)
                            <li>{{ __('• :item', ['item' => $solution]) }}</li>
                        @endforeach
                    </ul>

                    <div class="mt-8 flex flex-1 items-end gap-3">
                        <a href="{{ $portalCard['route'] }}"
                            class="inline-flex flex-1 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900 transition hover:border-sky-300 hover:bg-sky-50">
                            {{ __('Ver detalhes') }}
                        </a>
                        <a href="{{ $portalCard['access_route'] }}"
                            class="inline-flex flex-1 items-center justify-center rounded-xl bg-linear-to-br from-[#f1d57a] via-[#c7a840] to-[#8a7424] px-4 py-3 text-sm font-semibold text-[#1b1709] transition hover:brightness-110">
                            {{ $portalCard['cta_label'] }}
                        </a>
                    </div>
                </article>
            @endforeach
        </section>
    </x-web.container>
</x-layouts.guest>
