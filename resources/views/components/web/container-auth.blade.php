@props(['title' => null, 'subtitle' => null])

<div class="-mb-16">
    <x-web.header :title="$title" :subtitle="$subtitle" :cover="asset('images/leadership-meeting.webp')" />

    <div class="w-full max-w-xl px-8 pt-16 pb-32 mx-auto">{{ $slot }}
    </div>
</div>
