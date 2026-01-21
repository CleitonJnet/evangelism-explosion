<div>
    <div class="text-lg uppercase font-bold">{{ $ministry->name }}:</div>
    <div class="pb-4 mb-4 border-b-4 border-amber-800">
        <div class="grid grid-cols-12 gap-4 even:bg-slate-50 odd:bg-slate-100 px-1">
            <div class="col-span-2 font-semibold">{{ __('Logo') }}:</div>
            <div class="col-span-10">{{ $ministry->logo }}</div>
        </div>
        <div class="grid grid-cols-12 gap-4 even:bg-slate-50 odd:bg-slate-100 px-1">
            <div class="col-span-2 font-semibold">{{ __('Iniciais') }}:</div>
            <div class="col-span-10">{{ $ministry->initials }}</div>
        </div>
        <div class="grid grid-cols-12 gap-4 even:bg-slate-50 odd:bg-slate-100 px-1">
            <div class="col-span-2 font-semibold">{{ __('Color') }}:</div>
            <div class="col-span-10">{{ $ministry->color }}</div>
        </div>
        <div class="grid grid-cols-12 gap-4 even:bg-slate-50 odd:bg-slate-100 px-1">
            <div class="col-span-2 font-semibold">{{ __('Description') }}:</div>
            <div class="col-span-10">{{ $ministry->description }}</div>
        </div>
    </div>

    <div class="grid gap-4">
        <div class="pb-4 border-b-4 border-amber-800">
            <div class="text-lg uppercase font-bold">Curso de lançamento:</div>
            <ul>
                @foreach ($launcher as $course)
                    <li><a
                            href="{{ route('app.director.ministry.course.show', ['ministry' => $ministry->id, 'course' => $course->id]) }}">{{ $course->type }}
                            {{ $course->name }}</a>
                    </li>
                @endforeach
            </ul>
        </div>
        <div>
            <div class="text-lg uppercase font-bold">Curso de implementação:</div>
            <ul>
                @foreach ($implementation as $course)
                    <li><a
                            href="{{ route('app.director.ministry.course.show', ['ministry' => $ministry->id, 'course' => $course->id]) }}">
                            @if ($implementation->count() > 1)
                                Parte {{ $course->order }}:
                            @endif {{ $course->name }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>

    </div>
</div>
