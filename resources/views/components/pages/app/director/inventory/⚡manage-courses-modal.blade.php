<?php

use App\Models\Material;
use App\Models\Ministry;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public int $materialId;

    public bool $showModal = false;

    /**
     * @var array<int>
     */
    public array $selectedCourseIds = [];

    public function mount(int $materialId): void
    {
        $this->materialId = $materialId;
        $this->fillSelections();
    }

    #[On('open-director-material-courses-modal')]
    public function openModal(?int $materialId = null): void
    {
        if ($materialId !== null && $materialId !== $this->materialId) {
            return;
        }

        $this->fillSelections();
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetValidation();
        $this->fillSelections();
    }

    public function save(): void
    {
        $validated = $this->validate([
            'selectedCourseIds' => ['array'],
            'selectedCourseIds.*' => ['integer', 'exists:courses,id'],
        ]);

        $material = Material::query()->findOrFail($this->materialId);
        $material->courses()->sync($validated['selectedCourseIds'] ?? []);

        $this->dispatch('director-material-courses-updated', materialId: $material->id);
        $this->closeModal();
    }

    /**
     * @return array<int, \App\Models\Ministry>
     */
    public function ministries(): array
    {
        return Ministry::query()
            ->with(['courses' => fn ($query) => $query->orderBy('name')])
            ->orderBy('name')
            ->get()
            ->all();
    }

    private function fillSelections(): void
    {
        $this->selectedCourseIds = Material::query()
            ->findOrFail($this->materialId)
            ->courses()
            ->pluck('courses.id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }
};
?>

<div>
    <flux:modal name="director-material-courses-modal" wire:model="showModal" class="max-w-5xl w-[calc(100%-4px)] mx-auto bg-sky-950! p-0! max-h-[calc(100vh-4px)]! overflow-hidden">
        <div class="flex max-h-[90vh] flex-col overflow-hidden rounded-2xl">
            <header class="sticky top-0 z-20 border-b border-sky-800 bg-sky-950 px-6 py-4 text-sky-50">
                <h3 class="text-lg font-semibold">{{ __('Vincular cursos') }}</h3>
                <p class="text-sm opacity-90">
                    {{ __('Selecione os cursos existentes que utilizam este material, agrupados por ministério.') }}
                </p>
            </header>

            <div class="min-h-0 flex-1 overflow-y-auto bg-white px-6 py-6">
                <div class="space-y-5">
                    @foreach ($this->ministries() as $ministry)
                        <section class="rounded-2xl border border-slate-200 bg-slate-50 p-4"
                            wire:key="director-material-ministry-{{ $ministry->id }}">
                            <div class="mb-3">
                                <h4 class="text-sm font-semibold uppercase tracking-wide text-slate-700">
                                    {{ $ministry->name }}
                                </h4>
                            </div>

                            <div class="grid gap-3 md:grid-cols-2">
                                @forelse ($ministry->courses as $course)
                                    <label class="flex items-start gap-3 rounded-xl border border-slate-200 bg-white p-3">
                                        <input type="checkbox" value="{{ $course->id }}"
                                            wire:model.live="selectedCourseIds" class="mt-1 rounded border-slate-300">
                                        <div class="space-y-1">
                                            <div class="font-semibold text-slate-900">
                                                {{ $course->type ? $course->type . ': ' : '' }}{{ $course->name }}
                                            </div>
                                            <div class="text-xs text-slate-500">
                                                {{ $course->initials ?: __('Sem sigla') }}
                                            </div>
                                        </div>
                                    </label>
                                @empty
                                    <div class="text-sm text-slate-500">{{ __('Nenhum curso neste ministério.') }}</div>
                                @endforelse
                            </div>
                        </section>
                    @endforeach
                </div>
            </div>

            <footer class="sticky bottom-0 z-20 border-t border-sky-800 bg-sky-950 px-6 py-4 text-sky-50">
                <div class="flex justify-between gap-3">
                    <x-src.btn-silver type="button" wire:click="closeModal">{{ __('Cancelar') }}</x-src.btn-silver>
                    <x-src.btn-gold type="button" wire:click="save">{{ __('Salvar vínculos') }}</x-src.btn-gold>
                </div>
            </footer>
        </div>
    </flux:modal>
</div>
