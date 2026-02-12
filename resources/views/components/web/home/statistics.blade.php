<section class="max-w-8xl mx-auto px-4 pt-12 mx-auto sm:px-6 lg:px-8">
    <div class="p-6 bg-white rounded-lg shadow-h">
        <div class="grid grid-cols-2 gap-6 mt-6 md:grid-cols-4">

            <div class="text-center">
                <div class="text-3xl font-bold" x-data x-init="$el.innerText = '0'">
                    @livewire('web.home.stat-count', ['key' => 'igrejas'])
                </div>
                <div class="mt-1 text-sm text-gray-600">Igrejas treinadas</div>
            </div>

            <div class="text-center">
                <div class="text-3xl font-bold" x-data x-init="$el.innerText = '0'">
                    @livewire('web.home.stat-count', ['key' => 'criancas'])
                </div>
                <div class="mt-1 text-sm text-gray-600">Crianças impactadas</div>
            </div>

            <div class="text-center">
                <div class="text-3xl font-bold" x-data x-init="$el.innerText = '0'">
                    @livewire('web.home.stat-count', ['key' => 'professores'])
                </div>
                <div class="mt-1 text-sm text-gray-600">Professores certificados</div>
            </div>

            <div class="text-center">
                <div class="text-3xl font-bold" x-data x-init="$el.innerText = '0'">
                    @livewire('web.home.stat-count', ['key' => 'alcancados'])
                </div>
                <div class="mt-1 text-sm text-gray-600">Pessoas alcançadas</div>
            </div>

        </div>
    </div>
</section>
