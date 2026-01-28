@props(['title' => null, 'description' => null, 'justify' => 'justify-start', 'class' => ''])

<section
    {{ $attributes->merge(['class' => 'rounded-2xl border border-amber-300/20 bg-linear-to-br from-white via-slate-50 to-slate-200 px-5 py-2 shadow-sm mb-6 w-full']) }}>
    <div class="flex flex-wrap items-center justify-between gap-4 w-full">
        @if ($title)
            <div class="w-full">
                <h1 class="text-xl font-semibold text-slate-900">
                    {{ $title }}
                </h1>
                @if ($description)
                    <p class="text-sm text-slate-600">
                        {{ $description }}
                    </p>
                @endif
            </div>
            <div class="h-px w-full bg-slate-200/90"></div>
        @endif
        <div class="w-full flex flex-wrap items-center gap-2 text-sm text-slate-700 {{ $justify }}">
            {{ $slot }}
        </div>
    </div>
</section>
