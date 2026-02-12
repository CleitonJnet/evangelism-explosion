<form wire:submit="submit">
    <div class="text-sm font-semibold text-heading">{{ __('Informações do treinamento') }}</div>

    <div class="flex flex-wrap mt-6 gap-y-8 gap-x-4">
        <x-src.form.select name="course_id" wire:model.live="course_id" label="Curso" width_basic="500"
            :options="$courses
                ->map(fn($course) => ['value' => $course->id, 'label' => $course->type . ': ' . $course->name])
                ->toArray()"
            required />

        <x-src.form.select name="teacher_id" wire:model="teacher_id" label="Professor" width_basic="500"
            :options="$teachers->map(fn($teacher) => ['value' => $teacher->id, 'label' => $teacher->name])->toArray()" />

        <x-src.form.input name="price" wire:model="price" label="Preço sugerido" width_basic="200" disabled />

        <x-src.form.input name="price_church" wire:model="price_church" label="Preço igreja" width_basic="200" />

        <x-src.form.input name="discount" wire:model="discount" label="Desconto" width_basic="200" />

        <x-src.form.select name="status" wire:model="status" label="Status" width_basic="200"
            :options="collect($statusOptions)
                ->map(fn($label, $value) => ['value' => $value, 'label' => $label])
                ->values()
                ->toArray()" />

        <x-src.form.input name="welcome_duration_minutes" wire:model="welcome_duration_minutes"
            label="Boas-vindas (min)" width_basic="200" type="number" min="30" max="60" />
    </div>

    <div class="mt-8 flex flex-col gap-4">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="text-sm font-semibold text-heading">{{ __('Datas do treinamento') }}</div>
            <flux:button type="button" variant="outline" wire:click="addEventDate">
                {{ __('Adicionar dia') }}
            </flux:button>
        </div>

        <div class="flex flex-col gap-4">
            @foreach ($eventDates as $index => $eventDate)
                <div class="flex flex-wrap items-end gap-4">
                    <x-src.form.input name="eventDates.{{ $index }}.date"
                        wire:model="eventDates.{{ $index }}.date" label="Data" type="date" width_basic="220"
                        required />
                    <x-src.form.input name="eventDates.{{ $index }}.start_time"
                        wire:model="eventDates.{{ $index }}.start_time" label="Início" type="time"
                        width_basic="160" required />
                    <x-src.form.input name="eventDates.{{ $index }}.end_time"
                        wire:model="eventDates.{{ $index }}.end_time" label="Fim" type="time"
                        width_basic="160" required />
                    <flux:button type="button" variant="danger" class="shrink-0"
                        wire:click="removeEventDate({{ $index }})">
                        {{ __('Remover') }}
                    </flux:button>
                </div>
            @endforeach
        </div>
    </div>

    <div class="mt-10 space-y-6">
        <div class="text-sm font-semibold text-heading">{{ __('Igreja sede') }}</div>
        <div class="flex flex-wrap gap-8">
            <x-src.form.input name="churchSearch" wire:model.live="churchSearch" label="Buscar igreja"
                width_basic="900" />
            <x-src.form.select name="church_id" wire:model.live="church_id" label="Igreja sede" width_basic="900"
                :select="empty($churchSearch)" :options="$churches
                    ->map(fn($church) => ['value' => $church->id, 'label' => $church->name . ' - ' . $church->city])
                    ->toArray()" />
        </div>

        <livewire:address-fields wire:model="address" title="Endereço do treinamento" wire:key="training-address" />
    </div>

    <div class="mt-10">
        <div class="text-sm font-semibold text-heading">{{ __('Informações gerais') }}</div>
        <div class="mt-6 flex flex-wrap gap-y-8 gap-x-4">
            <x-src.form.input name="coordinator" wire:model="coordinator" label="Coordenador" width_basic="400" />
            <x-src.form.input name="email" wire:model="email" label="Email" width_basic="400" />
            <x-src.form.input name="phone" wire:model="phone" label="Telefone" width_basic="300" />
            <x-src.form.input name="gpwhatsapp" wire:model="gpwhatsapp" label="WhatsApp" width_basic="300" />
            <x-src.form.input name="url" wire:model="url" label="Link do evento online" width_basic="320"
                type="url" />
            <x-src.form.input name="totKitsReceived" wire:model="totKitsReceived" label="Kits recebidos"
                width_basic="200" type="number" />
            <x-src.form.textarea name="notes" wire:model="notes" label="Observações" rows="2" />
            <div class="flex flex-col gap-3" style="flex: 1 1 100%">
                <label for="bannerUpload" class="text-sm text-body cursor-pointer">{{ __('Banner') }}</label>
                <input id="bannerUpload" name="bannerUpload" type="file" accept="image/*"
                    wire:model="bannerUpload"
                    class="block w-full text-sm text-heading file:mr-4 file:rounded-lg file:border-0 file:bg-neutral-100 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-neutral-700 hover:file:bg-neutral-200 cursor-pointer" />
                @error('bannerUpload')
                    <p class="text-xs font-semibold text-red-600">{{ $message }}</p>
                @enderror
                @if ($bannerUpload)
                    <div class="mt-2">
                        <img src="{{ $bannerUpload->temporaryUrl() }}" alt="{{ __('Prévia do banner') }}"
                            class="h-24 w-auto rounded-lg border border-[color:var(--ee-app-border)] object-cover" />
                    </div>
                @elseif ($banner)
                    <div class="mt-2">
                        <img src="{{ Storage::url($banner) }}" alt="{{ __('Banner atual') }}"
                            class="h-24 w-auto rounded-lg border border-[color:var(--ee-app-border)] object-cover" />
                    </div>
                @endif
            </div>
        </div>
    </div>

    <flux:button variant="primary" type="submit" class="mt-8 w-full">
        {{ __('Salvar alterações') }}
    </flux:button>
</form>
