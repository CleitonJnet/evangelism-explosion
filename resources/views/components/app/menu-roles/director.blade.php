<flux:sidebar.group :heading="__('Platform')" class="grid">


    @can('access-director')
        <flux:sidebar.item :href="route('app.director.dashboard')" :current="request()->routeIs('app.director.dashboard')"
            wire:navigate>
            <div class="relative"><span class="text-lg">&#10174;</span> {{ __('Dashboard') }}
                <div style="text-shadow: 0 0 1px #000000"
                    class="absolute top-1/2 -translate-1/2 right-0 text-slate-50 {{ request()->routeIs('app.start') ? 'opacity-100' : 'opacity-0' }}">
                    &#10148;
                </div>
            </div>
        </flux:sidebar.item>

        <flux:sidebar.item :href="route('app.director.church.index')" :current="request()->routeIs('app.director.church.*')"
            wire:navigate>
            <div class="relative"><span class="text-lg">&#10174;</span> {{ __('Churches') }}
                <div style="text-shadow: 0 0 1px #000000"
                    class="absolute top-1/2 -translate-1/2 right-0 text-slate-50 {{ request()->routeIs('app.director.church.*') ? 'opacity-100' : 'opacity-0' }}">
                    &#10148;
                </div>
            </div>
        </flux:sidebar.item>

        <flux:sidebar.item :href="route('app.director.ministry.index')"
            :current="request()->routeIs('app.director.ministry.*')" wire:navigate>
            <div class="relative"><span class="text-lg">&#10174;</span> {{ __('Ministry') }}
                <div style="text-shadow: 0 0 1px #000000"
                    class="absolute top-1/2 -translate-1/2 right-0 text-slate-50 {{ request()->routeIs('app.director.ministry.*') ? 'opacity-100' : 'opacity-0' }}">
                    &#10148;
                </div>
            </div>
        </flux:sidebar.item>

        <flux:sidebar.item :href="route('app.director.training.index')"
            :current="request()->routeIs('app.director.training.*')" wire:navigate>
            <div class="relative"><span class="text-lg">&#10174;</span> {{ __('Trainings') }}
                <div style="text-shadow: 0 0 1px #000000"
                    class="absolute top-1/2 -translate-1/2 right-0 text-slate-50 {{ request()->routeIs('app.director.training.*') ? 'opacity-100' : 'opacity-0' }}">
                    &#10148;
                </div>
            </div>
        </flux:sidebar.item>

        <flux:sidebar.item :href="route('app.director.inventory.index')"
            :current="request()->routeIs('app.director.inventory.*')" wire:navigate>
            <div class="relative"><span class="text-lg">&#10174;</span> {{ __('Inventory') }}
                <div style="text-shadow: 0 0 1px #000000"
                    class="absolute top-1/2 -translate-1/2 right-0 text-slate-50 {{ request()->routeIs('app.director.inventory.*') ? 'opacity-100' : 'opacity-0' }}">
                    &#10148;
                </div>
            </div>
        </flux:sidebar.item>
    @endcan

</flux:sidebar.group>
