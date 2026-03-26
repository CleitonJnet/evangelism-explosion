<?php

use App\Models\Material;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public int $materialId;

    public bool $showModal = false;

    /**
     * @var array<int, int>
     */
    public array $componentQuantities = [];

    /**
     * @var array<int>
     */
    public array $selectedComponentIds = [];

    public function mount(int $materialId): void
    {
        $this->materialId = $materialId;
        $this->fillSelections();
    }

    #[On('open-director-material-components-modal')]
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

    public function saveComposition(): void
    {
        $material = $this->material();

        if (! $material->isComposite()) {
            $this->addError('selectedComponentIds', __('A composição só pode ser gerenciada em materiais compostos.'));

            return;
        }

        $validated = $this->validate([
            'selectedComponentIds' => ['array'],
            'selectedComponentIds.*' => [
                'integer',
                Rule::exists('materials', 'id')->where(fn ($query) => $query->where('type', 'simple')),
                'distinct',
            ],
            'componentQuantities' => ['array'],
        ], [
            'integer' => 'O campo :attribute deve ser um número inteiro.',
            'selectedComponentIds.*.exists' => 'Somente itens simples podem compor um produto composto.',
            'distinct' => 'O mesmo componente não pode ser informado mais de uma vez.',
        ], [
            'selectedComponentIds' => 'componentes',
            'selectedComponentIds.*' => 'componente',
        ]);

        $selectedIds = collect($validated['selectedComponentIds'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->values();

        if ($selectedIds->contains($material->id)) {
            $this->addError('selectedComponentIds', __('O material não pode ser componente de si mesmo.'));

            return;
        }

        $payload = [];

        foreach ($selectedIds as $selectedId) {
            $quantity = (int) ($this->componentQuantities[$selectedId] ?? 0);

            if ($quantity < 1) {
                $this->addError('componentQuantities.'.$selectedId, __('A quantidade deve ser maior que zero.'));

                return;
            }

            $payload[$selectedId] = ['quantity' => $quantity];
        }

        $material->componentMaterials()->sync($payload);

        $this->fillSelections();
        $this->dispatch('director-material-components-updated', materialId: $material->id);
        $this->dispatch('toast', type: 'success', message: __('Composição do material salva com sucesso.'));
    }

    /**
     * @return array<int, \App\Models\Material>
     */
    public function availableMaterials(): array
    {
        return Material::query()
            ->whereKeyNot($this->materialId)
            ->where('type', 'simple')
            ->orderBy('name')
            ->get()
            ->all();
    }

    public function updatedSelectedComponentIds(): void
    {
        foreach ($this->selectedComponentIds as $selectedComponentId) {
            $selectedComponentId = (int) $selectedComponentId;

            if (! array_key_exists($selectedComponentId, $this->componentQuantities)) {
                $this->componentQuantities[$selectedComponentId] = 1;
            }
        }
    }

    private function fillSelections(): void
    {
        $components = $this->material()
            ->components()
            ->get(['component_material_id', 'quantity']);

        $this->selectedComponentIds = $components
            ->pluck('component_material_id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        $this->componentQuantities = $components
            ->mapWithKeys(fn ($component) => [(int) $component->component_material_id => (int) $component->quantity])
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
        class="max-w-6xl w-[calc(100%-4px)] mx-auto bg-sky-950! p-0! max-h-[calc(100vh-4px)]! overflow-hidden">
        <div class="flex max-h-[90vh] flex-col overflow-hidden rounded-2xl">
            <header class="sticky top-0 z-20 border-b border-sky-800 bg-sky-950 px-6 py-4 text-sky-50">
                <h3 class="text-lg font-semibold">{{ __('Gerenciar composição do material') }}</h3>
                <p class="text-sm opacity-90">
                    {{ __('Selecione os itens já cadastrados que fazem parte deste material composto e informe a quantidade de cada um.') }}
                </p>
            </header>

            <div class="min-h-0 flex-1 overflow-y-auto bg-white px-6 py-6">
                @php($material = $this->material())

                @if (! $material->isComposite())
                    <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                        {{ __('A composição só pode ser gerenciada em materiais do tipo composto.') }}
                    </div>
                @else
                    <section class="space-y-4 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <div>
                            <h4 class="text-sm font-semibold text-slate-900">{{ __('Montar composição do kit') }}</h4>
                            <p class="mt-1 text-sm text-slate-600">
                                {{ __('Marque os itens que entram no composto. Itens desmarcados serão removidos da composição ao salvar.') }}
                            </p>
                        </div>

                        @error('selectedComponentIds')
                            <p class="text-sm font-semibold text-red-600">{{ $message }}</p>
                        @enderror

                        <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white">
                            <table class="w-full text-left text-sm">
                                <thead class="bg-slate-50 text-xs uppercase text-slate-600">
                                    <tr>
                                        <th class="px-4 py-3">{{ __('Usar') }}</th>
                                        <th class="px-4 py-3">{{ __('Item') }}</th>
                                        <th class="px-4 py-3">{{ __('Tipo') }}</th>
                                        <th class="px-4 py-3">{{ __('Quantidade no kit') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($this->availableMaterials() as $availableMaterial)
                                        @php($isSelected = in_array($availableMaterial->id, $selectedComponentIds, true))
                                        <tr class="border-t border-slate-200"
                                            wire:key="director-material-component-row-{{ $availableMaterial->id }}">
                                            <td class="px-4 py-4 align-top">
                                                <input id="director-material-component-checkbox-{{ $availableMaterial->id }}"
                                                    name="selected_component_ids[]"
                                                    type="checkbox" value="{{ $availableMaterial->id }}"
                                                    wire:model.live="selectedComponentIds"
                                                    class="mt-1 rounded border-slate-300">
                                            </td>
                                            <td class="px-4 py-4 align-top">
                                                <div class="font-medium text-slate-900">{{ $availableMaterial->name }}</div>
                                                @if ($availableMaterial->description)
                                                    <div class="mt-1 text-xs text-slate-500">
                                                        {{ $availableMaterial->description }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="px-4 py-4 align-top">
                                                <span
                                                    class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $availableMaterial->typeBadgeClasses() }}">
                                                    {{ $availableMaterial->typeLabel() }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-4 align-top">
                                                <div class="max-w-32">
                                                    <input id="director-material-component-quantity-{{ $availableMaterial->id }}"
                                                        name="component_quantities[{{ $availableMaterial->id }}]"
                                                        type="number" min="1"
                                                        wire:model.live="componentQuantities.{{ $availableMaterial->id }}"
                                                        @disabled(! $isSelected)
                                                        class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm text-slate-900 shadow-xs focus:border-sky-500 focus:outline-none focus:ring-0 disabled:bg-slate-100 disabled:text-slate-400">
                                                </div>
                                                @error('componentQuantities.'.$availableMaterial->id)
                                                    <p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>
                                                @enderror
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-4 py-6 text-center text-sm text-slate-500">
                                                {{ __('Nenhum item disponível para montar este material composto.') }}
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </section>
                @endif
            </div>

            <footer class="sticky bottom-0 z-20 border-t border-sky-800 bg-sky-950 px-6 py-4 text-sky-50">
                <div class="flex justify-between gap-3">
                    <x-src.btn-silver type="button" wire:click="closeModal">{{ __('Fechar') }}</x-src.btn-silver>
                    @if ($material->isComposite())
                        <x-src.btn-gold type="button" wire:click="saveComposition" wire:loading.attr="disabled"
                            wire:target="saveComposition">
                            {{ __('Salvar composição') }}
                        </x-src.btn-gold>
                    @endif
                </div>
            </footer>
        </div>
    </flux:modal>
</div>
