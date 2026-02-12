<div>
    <div class="text-lg uppercase font-bold">Informações do Perfil:</div>
    <div class="pb-4 mb-4 border-b-4 border-amber-800">
        <div class="grid grid-cols-12 gap-4 even:bg-slate-50 odd:bg-slate-100 px-1">
            <div class="col-span-2 font-semibold">{{ __('Pastor') }}?</div>
            <div class="col-span-10">{{ $profile->pastor }}</div>
        </div>
        <div class="grid grid-cols-12 gap-4 even:bg-slate-50 odd:bg-slate-100 px-1">
            <div class="col-span-2 font-semibold">{{ __('Name') }}:</div>
            <div class="col-span-10">{{ $profile->name }}</div>
        </div>
        <div class="grid grid-cols-12 gap-4 even:bg-slate-50 odd:bg-slate-100 px-1">
            <div class="col-span-2 font-semibold">{{ __('Birthdate') }}:</div>
            <div class="col-span-10">{{ $profile->birthdate }}</div>
        </div>
        <div class="grid grid-cols-12 gap-4 even:bg-slate-50 odd:bg-slate-100 px-1">
            <div class="col-span-2 font-semibold">{{ __('Gender') }}:</div>
            <div class="col-span-10">{{ $profile->gender }}</div>
        </div>
        <div class="grid grid-cols-12 gap-4 even:bg-slate-50 odd:bg-slate-100 px-1">
            <div class="col-span-2 font-semibold">{{ __('Phone') }}:</div>
            <div class="col-span-10">{{ $profile->phone }}</div>
        </div>
        <div class="grid grid-cols-12 gap-4 even:bg-slate-50 odd:bg-slate-100 px-1">
            <div class="col-span-2 font-semibold">{{ __('Email') }}:</div>
            <div class="col-span-10">{{ $profile->email }}</div>
        </div>
        <div class="grid grid-cols-12 gap-4 even:bg-slate-50 odd:bg-slate-100 px-1">
            <div class="col-span-2 font-semibold">{{ __('Street') }}:</div>
            <div class="col-span-10">{{ $profile->street }}</div>
        </div>
        <div class="grid grid-cols-12 gap-4 even:bg-slate-50 odd:bg-slate-100 px-1">
            <div class="col-span-2 font-semibold">{{ __('Number') }}:</div>
            <div class="col-span-10">{{ $profile->number }}</div>
        </div>
        <div class="grid grid-cols-12 gap-4 even:bg-slate-50 odd:bg-slate-100 px-1">
            <div class="col-span-2 font-semibold">{{ __('Complement') }}:</div>
            <div class="col-span-10">{{ $profile->complement }}</div>
        </div>
        <div class="grid grid-cols-12 gap-4 even:bg-slate-50 odd:bg-slate-100 px-1">
            <div class="col-span-2 font-semibold">{{ __('District') }}:</div>
            <div class="col-span-10">{{ $profile->district }}</div>
        </div>
        <div class="grid grid-cols-12 gap-4 even:bg-slate-50 odd:bg-slate-100 px-1">
            <div class="col-span-2 font-semibold">{{ __('City') }}:</div>
            <div class="col-span-10">{{ $profile->city }}</div>
        </div>
        <div class="grid grid-cols-12 gap-4 even:bg-slate-50 odd:bg-slate-100 px-1">
            <div class="col-span-2 font-semibold">{{ __('State') }}:</div>
            <div class="col-span-10">{{ $profile->state }}</div>
        </div>
        <div class="grid grid-cols-12 gap-4 even:bg-slate-50 odd:bg-slate-100 px-1">
            <div class="col-span-2 font-semibold">{{ __('Postal Code') }}:</div>
            <div class="col-span-10">{{ $profile->postal_code }}</div>
        </div>
        <div class="grid grid-cols-12 gap-4 even:bg-slate-50 odd:bg-slate-100 px-1">
            <div class="col-span-2 font-semibold">{{ __('notes') }}:</div>
            <div class="col-span-10">{{ $profile->notes }}</div>
        </div>
    </div>
</div>
