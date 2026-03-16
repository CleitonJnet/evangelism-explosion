@props(['portalCards' => []])

<section class="relative overflow-hidden bg-slate-950 py-20 text-white">
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,rgba(201,179,89,0.18),transparent_42%),linear-gradient(180deg,rgba(15,23,42,0.96),rgba(2,6,23,1))]"></div>

    <x-web.container class="relative">
        <div class="mx-auto max-w-3xl text-center">
            <div class="text-xs font-extrabold uppercase tracking-[0.32em] text-amber-300/80">{{ __('Plataforma ministerial') }}</div>
            <h2 class="mt-4 text-3xl font-semibold text-white md:text-5xl">{{ __('Três portais, uma plataforma mais clara') }}</h2>
            <p class="mt-4 text-base leading-7 text-slate-300 md:text-lg">
                {{ __('O ecossistema do EE agora fica mais fácil de entender: cada público entra pela área certa, com uma experiência própria e coerente com sua missão.') }}
            </p>
        </div>

        <div class="mt-12 grid gap-5 lg:grid-cols-3">
            @foreach ($portalCards as $portalCard)
                @php
                    $toneClasses = match ($portalCard['tone']) {
                        'amber' => 'border-amber-400/30 bg-amber-400/10',
                        'slate' => 'border-slate-400/30 bg-white/5',
                        default => 'border-sky-400/30 bg-sky-400/10',
                    };
                @endphp

                <article class="flex h-full flex-col rounded-3xl border p-6 shadow-[0_18px_50px_rgba(0,0,0,.28)] backdrop-blur-sm {{ $toneClasses }}">
                    <div class="text-xs font-extrabold uppercase tracking-[0.24em] text-white/70">{{ $portalCard['eyebrow'] }}</div>
                    <h3 class="mt-3 text-2xl font-semibold text-white">{{ $portalCard['headline'] }}</h3>
                    <p class="mt-3 text-sm leading-6 text-slate-200">{{ $portalCard['description'] }}</p>

                    <div class="mt-5 text-xs font-semibold uppercase tracking-[0.2em] text-amber-200/80">{{ __('Quem usa') }}</div>
                    <div class="mt-2 flex flex-wrap gap-2">
                        @foreach ($portalCard['who_uses'] as $userType)
                            <span class="rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-semibold text-white/90">{{ $userType }}</span>
                        @endforeach
                    </div>

                    <div class="mt-6 flex flex-1 items-end gap-3">
                        <a href="{{ $portalCard['route'] }}"
                            class="inline-flex flex-1 items-center justify-center rounded-xl border border-white/20 bg-white/10 px-4 py-3 text-sm font-semibold text-white transition hover:border-amber-300/60 hover:bg-white/15">
                            {{ __('Conhecer') }}
                        </a>
                        <a href="{{ $portalCard['access_route'] }}"
                            class="inline-flex flex-1 items-center justify-center rounded-xl bg-linear-to-br from-[#f1d57a] via-[#c7a840] to-[#8a7424] px-4 py-3 text-sm font-semibold text-[#1b1709] transition hover:brightness-110">
                            {{ $portalCard['cta_label'] }}
                        </a>
                    </div>
                </article>
            @endforeach
        </div>
    </x-web.container>
</section>
