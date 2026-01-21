<flux:sidebar.group :heading="__('Platform')" class="grid">


    @can('access-facilitator')
        <flux:sidebar.item :href="route('app.facilitator.dashboard')"
            :current="request()->routeIs('app.facilitator.dashboard')" wire:navigate>
            <div class="relative"><span class="text-lg">&#10174;</span> {{ __('Dashboard') }}
                <div style="text-shadow: 0 0 1px #000000"
                    class="absolute top-1/2 -translate-1/2 right-0 text-slate-50 {{ request()->routeIs('app.start') ? 'opacity-100' : 'opacity-0' }}">
                    &#10148;
                </div>
            </div>
        </flux:sidebar.item>
    @endcan

</flux:sidebar.group>
