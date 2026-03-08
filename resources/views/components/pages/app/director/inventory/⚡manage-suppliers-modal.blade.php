<?php

use App\Models\Material;
use App\Models\Supplier;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public int $materialId;

    public bool $showModal = false;

    /**
     * @var array<int>
     */
    public array $selectedSupplierIds = [];

    public function mount(int $materialId): void
    {
        $this->materialId = $materialId;
        $this->fillSelections();
    }

    #[On('open-director-material-suppliers-modal')]
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
            'selectedSupplierIds' => ['array'],
            'selectedSupplierIds.*' => ['integer', 'exists:suppliers,id'],
        ]);

        $material = Material::query()->findOrFail($this->materialId);
        $material->suppliers()->sync($validated['selectedSupplierIds'] ?? []);

        $this->dispatch('director-material-suppliers-updated', materialId: $material->id);
        $this->closeModal();
    }

    /**
     * @return array<int, \App\Models\Supplier>
     */
    public function suppliers(): array
    {
        return Supplier::query()->orderBy('name')->get()->all();
    }

    private function fillSelections(): void
    {
        $this->selectedSupplierIds = Material::query()
            ->findOrFail($this->materialId)
            ->suppliers()
            ->pluck('suppliers.id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }
};
?>

<div>
    <flux:modal name="director-material-suppliers-modal" wire:model="showModal"
        class="max-w-4xl w-full bg-sky-950! p-0!">
        <div class="flex max-h-[90vh] flex-col overflow-hidden rounded-2xl">
            <header class="sticky top-0 z-20 border-b border-sky-800 bg-sky-950 px-6 py-4 text-sky-50">
                <h3 class="text-lg font-semibold">{{ __('Vincular fornecedores') }}</h3>
                <p class="text-sm opacity-90">
                    {{ __('Selecione os fornecedores já cadastrados que atendem este material.') }}
                </p>
            </header>

            <div class="min-h-0 flex-1 overflow-y-auto bg-white px-6 py-6">
                <div class="grid gap-3">
                    @forelse ($this->suppliers() as $supplier)
                        <label class="flex items-start gap-3 rounded-xl border border-slate-200 bg-slate-50 p-4"
                            wire:key="director-material-supplier-{{ $supplier->id }}">
                            <input type="checkbox" value="{{ $supplier->id }}" wire:model.live="selectedSupplierIds"
                                class="mt-1 rounded border-slate-300">
                            <div class="space-y-1">
                                <div class="font-semibold text-slate-900">{{ $supplier->name }}</div>
                                <div class="text-sm text-slate-600">
                                    {{ $supplier->email ?: __('Sem email') }} · {{ $supplier->phone ?: __('Sem telefone') }}
                                </div>
                            </div>
                        </label>
                    @empty
                        <div class="rounded-xl border border-dashed border-slate-300 p-4 text-sm text-slate-500">
                            {{ __('Nenhum fornecedor cadastrado.') }}
                        </div>
                    @endforelse
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
