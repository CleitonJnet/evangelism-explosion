<div>
    <ul>
        @foreach ($ministries as $ministry)
            <li><a
                    href="{{ route('app.director.ministry.show', ['ministry' => $ministry->id]) }}">{{ $ministry->name }}</a>
            </li>
        @endforeach
    </ul>
</div>
