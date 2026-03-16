@php
    $title = $portalCard['headline'].' - Plataforma Ministerial';
    $description = $portalCard['description'];
    $keywords = 'portal, plataforma ministerial, '.$portalCard['key'];
    $ogImage = asset('images/og/home.webp');
@endphp

<x-layouts.guest :title="$title" :description="$description" :keywords="$keywords" :ogImage="$ogImage" class="pb-14">
    <x-web.header :title="$portalCard['headline']" :subtitle="$portalCard['description']" :cover="asset('images/bg_welcome/photo1.webp')" />

    <x-web.container class="mt-10 space-y-8">
        <section class="grid gap-6 xl:grid-cols-[minmax(0,1.1fr)_minmax(20rem,0.9fr)]">
            <article class="rounded-[2rem] border border-slate-200 bg-white p-8 shadow-sm">
                <div class="text-xs font-extrabold uppercase tracking-[0.28em] text-amber-700">{{ __('Quem usa') }}</div>
                <h2 class="mt-3 text-3xl font-semibold text-slate-950">{{ __('Uma entrada pensada para esse público') }}</h2>
                <p class="mt-4 text-base leading-7 text-slate-700">
                    {{ __('Esta landing ajuda o usuário a reconhecer rapidamente se este é o portal certo antes de entrar na plataforma.') }}
                </p>

                <div class="mt-6 flex flex-wrap gap-2">
                    @foreach ($portalCard['who_uses'] as $userType)
                        <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-sm font-semibold text-slate-700">{{ $userType }}</span>
                    @endforeach
                </div>
            </article>

            <article class="rounded-[2rem] border border-slate-200 bg-slate-950 p-8 text-white shadow-sm">
                <div class="text-xs font-extrabold uppercase tracking-[0.28em] text-amber-300/80">{{ __('Acesso') }}</div>
                <h2 class="mt-3 text-3xl font-semibold">{{ __('Entrar com clareza') }}</h2>
                <p class="mt-4 text-base leading-7 text-slate-300">
                    {{ __('O botão abaixo já preserva o contexto deste portal. Se você ainda não estiver autenticado, o login será direcionado para esta mesma área.') }}
                </p>

                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ $portalCard['access_route'] }}"
                        class="inline-flex items-center justify-center rounded-xl bg-linear-to-br from-[#f1d57a] via-[#c7a840] to-[#8a7424] px-6 py-3 text-sm font-semibold text-[#1b1709] transition hover:brightness-110">
                        {{ $portalCard['cta_label'] }}
                    </a>
                    <a href="{{ route('web.portals.index') }}"
                        class="inline-flex items-center justify-center rounded-xl border border-white/20 px-6 py-3 text-sm font-semibold text-white transition hover:border-amber-300/60 hover:bg-white/5">
                        {{ __('Ver todos os portais') }}
                    </a>
                </div>
            </article>
        </section>

        <section class="rounded-[2rem] border border-slate-200 bg-white p-8 shadow-sm">
            <div class="text-xs font-extrabold uppercase tracking-[0.28em] text-amber-700">{{ __('O que resolve') }}</div>
            <h2 class="mt-3 text-3xl font-semibold text-slate-950">{{ __('Problemas que esta área ajuda a organizar') }}</h2>

            <div class="mt-6 grid gap-4 lg:grid-cols-3">
                @foreach ($portalCard['what_it_solves'] as $solution)
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5 text-sm leading-6 text-slate-700">
                        {{ $solution }}
                    </div>
                @endforeach
            </div>
        </section>
    </x-web.container>
</x-layouts.guest>
