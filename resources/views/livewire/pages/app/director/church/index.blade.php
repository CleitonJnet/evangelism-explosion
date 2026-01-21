<div>
    <div class="text-lg uppercase font-bold">Todas as Igrejas:</div>
    <ul>
        @foreach ($churches as $church)
            <li><a href="{{ route('app.director.church.show', ['church' => $church->id]) }}">{{ $church->name }}</a></li>
        @endforeach
    </ul>
</div>
