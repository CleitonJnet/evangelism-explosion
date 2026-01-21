<div>
    <div class="text-lg uppercase font-bold">Bases de Treinamentos:</div>
    <ul>
        @foreach ($hosts as $host_church)
            <li><a
                    href="{{ route('app.director.church.view_host', ['church' => $host_church->church_id]) }}">{{ $host_church->church->name }}</a>
            </li>
        @endforeach
    </ul>
</div>
