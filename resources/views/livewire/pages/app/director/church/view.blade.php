<div>
    <div class="text-lg uppercase font-bold">{{ $church->name }}:</div>
    <div class="pb-4 mb-4 border-b-4 border-amber-800">
        <div class="grid grid-cols-12 gap-4 even:bg-slate-50 odd:bg-slate-100 px-1">
            <div class="col-span-3 font-semibold">Pastor:</div>
            <div class="col-span-9">{{ $church->pastor }}</div>
        </div>
        <div class="grid grid-cols-12 gap-4 even:bg-slate-50 odd:bg-slate-100 px-1">
            <div class="col-span-3 font-semibold">email:</div>
            <div class="col-span-9">{{ $church->email }}</div>
        </div>
        <div class="grid grid-cols-12 gap-4 even:bg-slate-50 odd:bg-slate-100 px-1">
            <div class="col-span-3 font-semibold">Telefone:</div>
            <div class="col-span-9">{{ $church->phone }}</div>
        </div>
        <div class="grid grid-cols-12 gap-4 even:bg-slate-50 odd:bg-slate-100 px-1">
            <div class="col-span-3 font-semibold">CEP:</div>
            <div class="col-span-9">{{ $church->postal_code }}</div>
        </div>
        <div class="grid grid-cols-12 gap-4 even:bg-slate-50 odd:bg-slate-100 px-1">
            <div class="col-span-3 font-semibold">Logradouro:</div>
            <div class="col-span-9">{{ $church->street }}</div>
        </div>
        <div class="grid grid-cols-12 gap-4 even:bg-slate-50 odd:bg-slate-100 px-1">
            <div class="col-span-3 font-semibold">Número:</div>
            <div class="col-span-9">{{ $church->number }}</div>
        </div>
        <div class="grid grid-cols-12 gap-4 even:bg-slate-50 odd:bg-slate-100 px-1">
            <div class="col-span-3 font-semibold">Complemento:</div>
            <div class="col-span-9">{{ $church->complement }}</div>
        </div>
        <div class="grid grid-cols-12 gap-4 even:bg-slate-50 odd:bg-slate-100 px-1">
            <div class="col-span-3 font-semibold">Bairro:</div>
            <div class="col-span-9">{{ $church->district }}</div>
        </div>
        <div class="grid grid-cols-12 gap-4 even:bg-slate-50 odd:bg-slate-100 px-1">
            <div class="col-span-3 font-semibold">Cidade:</div>
            <div class="col-span-9">{{ $church->city }}</div>
        </div>
        <div class="grid grid-cols-12 gap-4 even:bg-slate-50 odd:bg-slate-100 px-1">
            <div class="col-span-3 font-semibold">UF:</div>
            <div class="col-span-9">{{ $church->state }}</div>
        </div>
        <div class="grid grid-cols-12 gap-4 even:bg-slate-50 odd:bg-slate-100 px-1">
            <div class="col-span-3 font-semibold">Contato:</div>
            <div class="col-span-9">{{ $church->contact }}</div>
        </div>
        <div class="grid grid-cols-12 gap-4 even:bg-slate-50 odd:bg-slate-100 px-1">
            <div class="col-span-3 font-semibold">Telefone do Contato:</div>
            <div class="col-span-9">{{ $church->contact_phone }}</div>
        </div>
        <div class="grid grid-cols-12 gap-4 even:bg-slate-50 odd:bg-slate-100 px-1">
            <div class="col-span-3 font-semibold">Email do Contato:</div>
            <div class="col-span-9">{{ $church->contact_email }}</div>
        </div>
        <div class="grid grid-cols-12 gap-4 even:bg-slate-50 odd:bg-slate-100 px-1">
            <div class="col-span-3 font-semibold">Logo:</div>
            <div class="col-span-9">{{ $church->logo }}</div>
        </div>
        <div class="grid grid-cols-12 gap-4 even:bg-slate-50 odd:bg-slate-100 px-1">
            <div class="col-span-3 font-semibold">Comentários sobre a igreja:</div>
            <div class="col-span-9">{{ $church->notes }}</div>
        </div>
    </div>

    <ul>
        <div class="text-lg uppercase font-bold">Lista de membros participantes:</div>
        @foreach ($profiles as $profile)
            <li><a
                    href="{{ route('app.director.church.profile.show', ['church' => $church->id, 'profile' => $profile->id]) }}">{{ $profile->name }}</a>
            </li>
        @endforeach
    </ul>

</div>
