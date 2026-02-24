<?php

use App\Models\Training;
use App\TrainingStatus;
use Illuminate\Support\Facades\Gate;
use Livewire\Volt\Component;

new class extends Component {
    public int $trainingId;

    public int $status = 0;

    public function mount(int $trainingId): void
    {
        $this->trainingId = $trainingId;

        $training = $this->resolveTraining();
        $this->status = $this->statusValue($training);
    }

    public function updateStatus(int $status): void
    {
        $statusEnum = TrainingStatus::tryFrom($status);

        if (!$statusEnum instanceof TrainingStatus) {
            return;
        }

        $training = $this->resolveTraining();
        $training->status = $statusEnum;
        $training->save();

        $this->status = $statusEnum->value;
    }

    public function statusLabel(): string
    {
        return $this->selectedStatus()->label();
    }

    public function buttonClasses(): string
    {
        return match ($this->selectedStatus()) {
            TrainingStatus::Planning => '!bg-amber-200 !text-amber-900 !border-amber-400 hover:!bg-amber-300/90',
            TrainingStatus::Scheduled => '!bg-sky-900 !text-slate-100 !border-sky-700 hover:!bg-sky-800',
            TrainingStatus::Canceled => '!bg-rose-200 !text-rose-900 !border-rose-400 hover:!bg-rose-300/90',
            TrainingStatus::Completed => '!bg-emerald-200 !text-emerald-900 !border-emerald-400 hover:!bg-emerald-300/90',
        };
    }

    /**
     * @return array<int, array{value: int, label: string}>
     */
    public function statusOptions(): array
    {
        return array_map(
            fn(TrainingStatus $status): array => [
                'value' => $status->value,
                'label' => $status->label(),
            ],
            TrainingStatus::cases(),
        );
    }

    private function selectedStatus(): TrainingStatus
    {
        return TrainingStatus::tryFrom($this->status) ?? TrainingStatus::Planning;
    }

    private function resolveTraining(): Training
    {
        $training = Training::query()->findOrFail($this->trainingId);
        Gate::authorize('update', $training);

        return $training;
    }

    private function statusValue(Training $training): int
    {
        $status = $training->status;

        if ($status instanceof TrainingStatus) {
            return $status->value;
        }

        return (int) $status;
    }
}; ?>

<div class="ml-auto flex items-center gap-2">
    <flux:dropdown position="bottom" align="end">
        <x-src.toolbar.button href="#" :label="__('Status do Evento')" icon="list" :tooltip="__('Modifica o status do evento')" :class="$this->buttonClasses()"
            x-on:click.prevent wire:loading.attr="disabled" wire:target="updateStatus">
            <div
                class="absolute -bottom-1.5 inset-x-2 rounded-md bg-slate-100 border border-slate-400 px-2.5 text-xs font-bold uppercase text-slate-800">
                {{ __($this->statusLabel()) }}
            </div>
        </x-src.toolbar.button>

        <flux:menu class="w-56 min-w-56 border border-slate-300" style="box-shadow: 0 2px 5px 2px rgba(0,0,0,0.25)">
            @foreach ($this->statusOptions() as $option)
                <flux:menu.item as="button" type="button"
                    class="w-full cursor-pointer hover:bg-slate-200! mb-px border-b {{ $status === $option['value'] ? 'bg-slate-200' : '' }}"
                    wire:click="updateStatus({{ $option['value'] }})" wire:loading.attr="disabled"
                    wire:target="updateStatus">
                    <div class="flex w-full items-center justify-between gap-3 text-sm">
                        <span>{{ __($option['label']) }}</span>
                        @if ($status === $option['value'])
                            <span class="text-emerald-700">{{ __('Atual') }}</span>
                        @endif
                    </div>
                </flux:menu.item>
            @endforeach
        </flux:menu>
    </flux:dropdown>

    <span class="mx-1 h-7 w-px bg-slate-300/80"></span>
</div>
