@props([
    'eyebrow' => null,
    'title',
    'description' => null,
    'breadcrumbs' => [],
])

<section class="rounded-3xl border border-neutral-200 bg-white p-6 shadow-sm">
    <div class="flex flex-col gap-5">
        @if ($breadcrumbs !== [])
            <nav aria-label="{{ __('Breadcrumb') }}" class="flex flex-wrap items-center gap-2 text-xs text-neutral-500">
                @foreach ($breadcrumbs as $breadcrumb)
                    @if (! $loop->first)
                        <span class="text-neutral-300">/</span>
                    @endif

                    @if (! empty($breadcrumb['url']) && empty($breadcrumb['current']))
                        <a href="{{ $breadcrumb['url'] }}" class="font-medium text-neutral-500 transition hover:text-sky-700">
                            {{ $breadcrumb['label'] }}
                        </a>
                    @else
                        <span class="font-semibold text-neutral-900">{{ $breadcrumb['label'] }}</span>
                    @endif
                @endforeach
            </nav>
        @endif

        <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
            <div class="flex max-w-3xl flex-col gap-2">
                @if ($eyebrow)
                    <span class="text-[11px] font-semibold uppercase tracking-[0.24em] text-sky-700">
                        {{ $eyebrow }}
                    </span>
                @endif

                <div class="flex flex-col gap-2">
                    <h1 class="text-2xl font-semibold text-neutral-950">{{ $title }}</h1>

                    @if ($description)
                        <p class="text-sm text-neutral-600">{{ $description }}</p>
                    @endif
                </div>
            </div>

            @if (trim((string) $slot) !== '')
                <div class="flex shrink-0 flex-wrap items-center gap-3">
                    {{ $slot }}
                </div>
            @endif
        </div>
    </div>
</section>
