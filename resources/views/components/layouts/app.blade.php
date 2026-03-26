<x-app.layouts.app.sidebar :title="$title ?? null">
    <flux:main class="px-3 py-4 sm:px-4 md:px-5 md:py-5 lg:px-8 lg:py-8 mb-20 mt-14 lg:my-0">
        {{ $slot }}
    </flux:main>
</x-app.layouts.app.sidebar>
