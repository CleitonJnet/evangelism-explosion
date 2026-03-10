@props([
    'action',
    'value' => '',
])

<form method="GET" action="{{ $action }}" class="min-w-[18rem] flex-1 lg:max-w-xl">
    <label for="training-filter" class="sr-only">
        {{ __('Filtrar treinamentos') }}
    </label>

    <div class="flex items-stretch overflow-hidden rounded-xl border border-slate-300 bg-white shadow-sm">
        <div class="flex min-w-0 flex-1 items-center bg-transparent">
            <input
                id="training-filter"
                type="text"
                name="filter"
                value="{{ $value }}"
                placeholder="{{ __('Filtrar por professor, igreja base, cidade, UF, data, curso ou auxiliar') }}"
                class="min-w-0 flex-1 border-0 bg-transparent px-4 py-2.5 text-sm text-slate-700 placeholder:text-slate-400 focus:outline-none focus:ring-0"
            />

            @if (filled($value))
                <a
                    href="{{ $action }}"
                    class="inline-flex h-full items-center justify-center px-3 text-slate-400 transition hover:text-slate-700"
                    aria-label="{{ __('Limpar filtro') }}"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </a>
            @endif
        </div>

        <button
            type="submit"
            class="inline-flex items-center justify-center border-l border-slate-300 bg-sky-950 px-4 text-white transition hover:bg-sky-900"
            aria-label="{{ __('Filtrar treinamentos') }}"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35m1.85-5.15a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" />
            </svg>
        </button>
    </div>
</form>
