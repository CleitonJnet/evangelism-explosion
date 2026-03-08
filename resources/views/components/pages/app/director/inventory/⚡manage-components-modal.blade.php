<?php

use App\Models\Material;
use App\Models\MaterialComponent;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public int $materialId;

    public bool $showModal = false;

    public ?int $component_material_id = null;

    public int $quantity = 1;

    /**
     * @var array<int, int>
     */
    public array $componentQuantities = [];

    public function mount(int $materialId): void
    {
        $this->materialId = $materialId;
        $this->fillQuantities();
    }

    #[On('open-director-material-components-modal')]
    public function openModal(?int $materialId = null): void
    {
        if ($materialId !== null && $materialId !== $this->materialId) {
            return;
        }

        $this->fillQuantities();
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetValidation();
        $this->component_material_id = null;
        $this->quantity = 1;
        $this->fillQuantities();
    }

    public function addComponent(): void
    {
        $material = $this->material();

        if (! $material->isComposite()) {
            $this->addError('component_material_id', __('A composição só pode ser gerenciada em materiais compostos.'));

            return;
        }

        $validated = $this->validate([
            'component_material_id' => ['required', 'integer', 'exists:materials,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ], [
            'required' => 'O campo :attribute é obrigatório.',
            'integer' => 'O campo :attribute deve ser um número inteiro.',
            'min' => 'O campo :attribute deve ser no mínimo :min.',
        ], [
            'component_material_id' => 'componente',
            'quantity' => 'quantidade',
        ]);

        if ((int) $validated['component_material_id'] === $material->id) {
            $this->addError('component_material_id', __('O material não pode ser componente de si mesmo.'));

            return;
        }

        $duplicate = $material->components()
            ->where('component_material_id', $validated['component_material_id'])
            ->exists();

        if ($duplicate) {
            $this->addError('component_material_id', __('Este componente já foi adicionado ao material composto.'));

            return;
        }

        $material->components()->create([
            'component_material_id' => $validated['component_material_id'],
            'quantity' => $validated['quantity'],
        ]);

        $this->component_material_id = null;
        $this->quantity = 1;
        $this->fillQuantities();
        $this->dispatch('director-material-components-updated', materialId: $material->id);
    }

    public function updateComponentQuantity(int $componentId): void
    {
        $component = $this->material()
            ->components()
            ->whereKey($componentId)
            ->firstOrFail();

        $quantity = (int) ($this->componentQuantities[$componentId] ?? 0);

        if ($quantity < 1) {
            $this->addError('componentQuantities.'.$componentId, __('A quantidade deve ser maior que zero.'));

            return;
        }

        $component->update(['quantity' => $quantity]);
        $this->dispatch('director-material-components-updated', materialId: $this->materialId);
    }

    public function removeComponent(int $componentId): void
    {
        $this->material()
            ->components()
            ->whereKey($componentId)
            ->delete();

        unset($this->componentQuantities[$componentId]);
        $this->dispatch('director-material-components-updated', materialId: $this->materialId);
    }

    /**
     * @return array<int, \App\Models\Material>
     */
    public function availableMaterials(): array
    {
        $material = $this->material();
        $componentIds = $material->components()->pluck('component_material_id')->all();

        return Material::query()
            ->whereKeyNot($material->id)
            ->whereNotIn('id', $componentIds)
            ->orderBy('name')
            ->get()
            ->all();
    }

    private function fillQuantities(): void
    {
        $this->componentQuantities = $this->material()
            ->components()
            ->pluck('quantity', 'id')
            ->mapWithKeys(fn ($quantity, $id) => [(int) $id => (int) $quantity])
            ->all();
    }

    private function material(): Material
    {
        return Material::query()
            ->with('components.componentMaterial')
            ->findOrFail($this->materialId);
    }
};
?>

<div>
    <flux:modal name="director-material-components-modal" wire:model="showModal"
        class="max-w-5xl w-full bg-sky-950! p-0!">
        <div class="flex max-h-[90vh] flex-col overflow-hidden rounded-2xl">
            <header class="sticky top-0 z-20 border-b border-sky-800 bg-sky-950 px-6 py-4 text-sky-50">
                <h3 class="text-lg font-semibold">{{ __('Gerenciar composição do material') }}</h3>
                <p class="text-sm opacity-90">
                    {{ __('Adicione componentes, ajuste quantidades e mantenha a composição do kit atualizada.') }}
                </p>
            </header>

            <div class="min-h-0 flex-1 overflow-y-auto bg-white px-6 py-6">
                @php($material = $this->material())

                @if (! $material->isComposite())
                    <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                        {{ __('A composição só pode ser gerenciada em materiais do tipo composto.') }}
                    </div>
                @else
                    <div class="space-y-6">
                        <section class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <div class="mb-4">
                                <h4 class="text-sm font-semibold text-slate-900">{{ __('Adicionar componente') }}</h4>
                            </div>

                            <div class="flex flex-wrap gap-x-4 gap-y-8">
                                <x-src.form.select name="director-material-component-select"
                                    wire:model.live="component_material_id" label="Componente" width_basic="320"
                                    :options="collect($this->availableMaterials())->map(fn ($availableMaterial) => [
                                        'value' => $availableMaterial->id,
                                        'label' => $availableMaterial->name,
                                    ])->values()->all()" />
                                <x-src.form.input name="director-material-component-quantity" wire:model.live="quantity"
                                    label="Quantidade" type="number" width_basic="160" min="1" />
                            </div>

                            <div class="mt-4 flex justify-end">
                                <x-src.btn-gold type="button" wire:click="addComponent">
                                    {{ __('Adicionar componente') }}
                                </x-src.btn-gold>
                            </div>
                        </section>

                        <section class="rounded-2xl border border-slate-200 bg-white">
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-sm">
                                    <thead class="bg-slate-50 text-xs uppercase text-slate-600">
                                        <tr>
                                            <th class="px-4 py-3">{{ __('Componente') }}</th>
                                            <th class="px-4 py-3">{{ __('Quantidade') }}</th>
                                            <th class="px-4 py-3 text-right">{{ __('Ações') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($material->components as $component)
                                            <tr class="border-t border-slate-200"
                                                wire:key="director-material-component-row-{{ $component->id }}">
                                                <td class="px-4 py-4 font-medium text-slate-900">
                                                    {{ $component->componentMaterial?->name ?: __('Material removido') }}
                                                </td>
                                                <td class="px-4 py-4">
                                                    <div class="max-w-32">
                                                        <input
                                                            id="director-material-component-quantity-{{ $component->id }}"
                                                            type="number" min="1"
                                                            wire:model.live="componentQuantities.{{ $component->id }}"
                                                            class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm text-slate-900 shadow-xs focus:border-sky-500 focus:outline-none focus:ring-0">
                                                    </div>
                                                    @error('componentQuantities.'.$component->id)
                                                        <p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>
                                                    @enderror
                                                </td>
                                                <td class="px-4 py-4">
                                                    <div class="flex justify-end gap-2">
                                                        <button type="button"
                                                            wire:click="updateComponentQuantity({{ $component->id }})"
                                                            class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 transition hover:border-sky-300 hover:text-sky-900">
                                                            {{ __('Salvar') }}
                                                        </button>
                                                        <button type="button"
                                                            wire:click="removeComponent({{ $component->id }})"
                                                            class="inline-flex items-center rounded-lg border border-rose-200 bg-white px-3 py-2 text-xs font-semibold text-rose-700 transition hover:border-rose-300 hover:text-rose-900">
                                                            {{ __('Remover') }}
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="px-4 py-6 text-center text-sm text-slate-500">
                                                    {{ __('Nenhum componente cadastrado neste material composto.') }}
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </section>
                    </div>
                @endif
            </div>

            <footer class="sticky bottom-0 z-20 border-t border-sky-800 bg-sky-950 px-6 py-4 text-sky-50">
                <div class="flex justify-between gap-3">
                    <x-src.btn-silver type="button" wire:click="closeModal">{{ __('Fechar') }}</x-src.btn-silver>
                </div>
            </footer>
        </div>
    </flux:modal>
</div>
