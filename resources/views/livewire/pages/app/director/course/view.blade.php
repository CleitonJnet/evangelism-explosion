<div>
    <div class="text-lg uppercase font-bold">{{ $course->type }}: {{ $course->name }}:</div>
    <div class="pb-4 mb-4 border-b-4 border-amber-800">
        <div class="grid grid-cols-12 gap-4 even:bg-slate-50 odd:bg-slate-100 px-1">
            <div class="col-span-2 font-semibold">{{ __('Banner') }}:</div>
            <div class="col-span-10">{{ $course->banner }}</div>
        </div>
        <div class="grid grid-cols-12 gap-4 even:bg-slate-50 odd:bg-slate-100 px-1">
            <div class="col-span-2 font-semibold">{{ __('Logo') }}:</div>
            <div class="col-span-10">{{ $course->logo }}</div>
        </div>
        <div class="grid grid-cols-12 gap-4 even:bg-slate-50 odd:bg-slate-100 px-1">
            <div class="col-span-2 font-semibold">{{ __('Iniciais') }}:</div>
            <div class="col-span-10">{{ $course->initials }}</div>
        </div>
        <div class="grid grid-cols-12 gap-4 even:bg-slate-50 odd:bg-slate-100 px-1">
            <div class="col-span-2 font-semibold">{{ __('Target Audience') }}:</div>
            <div class="col-span-10">{{ $course->targetAudience }}</div>
        </div>
        <div class="grid grid-cols-12 gap-4 even:bg-slate-50 odd:bg-slate-100 px-1">
            <div class="col-span-2 font-semibold">{{ __('Learn More Link') }}:</div>
            <div class="col-span-10">{{ $course->learnMoreLink }}</div>
        </div>
        <div class="grid grid-cols-12 gap-4 even:bg-slate-50 odd:bg-slate-100 px-1">
            <div class="col-span-2 font-semibold">{{ __('Price') }}:</div>
            <div class="col-span-10">{{ $course->price }}</div>
        </div>
        <div class="grid grid-cols-12 gap-4 even:bg-slate-50 odd:bg-slate-100 px-1">
            <div class="col-span-2 font-semibold">{{ __('Color') }}:</div>
            <div class="col-span-10">{{ $course->color }}</div>
        </div>
        <div class="grid grid-cols-12 gap-4 even:bg-slate-50 odd:bg-slate-100 px-1">
            <div class="col-span-2 font-semibold">{{ __('Certificate') }}:</div>
            <div class="col-span-10">{{ $course->certificate }}</div>
        </div>
        <div class="grid grid-cols-12 gap-4 even:bg-slate-50 odd:bg-slate-100 px-1">
            <div class="col-span-2 font-semibold">{{ __('Know How') }}:</div>
            <div class="col-span-10">{{ $course->knowhow }}</div>
        </div>
        <div class="grid grid-cols-12 gap-4 even:bg-slate-50 odd:bg-slate-100 px-1">
            <div class="col-span-2 font-semibold">{{ __('Description') }}:</div>
            <div class="col-span-10">{{ $course->description }}</div>
        </div>
    </div>

    <ul>
        @foreach ($teachers as $teacher)
            <li><a href="{{ route('app.director.church.profile.show', $teacher->id) }}">{{ $teacher->name }}</a></li>
        @endforeach
    </ul>
</div>
