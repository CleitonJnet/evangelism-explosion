@props([
    'model' => 'period',
    'options' => [],
    'selected' => 'year',
])

<div class="inline-flex flex-wrap gap-2 rounded-2xl border border-slate-200 bg-white/80 p-2 shadow-sm">
    @foreach ($options as $option)
        <button
            type="button"
            wire:click="$set('{{ $model }}', '{{ $option['value'] }}')"
            class="inline-flex min-w-28 items-center justify-center rounded-xl border px-4 py-2 text-sm font-semibold transition {{ $selected === $option['value'] ? 'border-sky-900 bg-sky-950 text-white' : 'border-slate-200 text-slate-600 hover:border-sky-300 hover:text-sky-900' }}"
        >
            <span>
                {{ $option['label'] }}
            </span>
        </button>
    @endforeach
</div>
