@props(['label', 'route', 'isroute' => false])

<a href="{{ $route }}"
    class="flex items-start py-3 pl-3 pr-6 font-extrabold text-white transition rounded-lg shine text-nowrap {{ $isroute ? 'bg-white/5' : 'hover:bg-white/10' }}">
    {!! $label !!}
</a>
