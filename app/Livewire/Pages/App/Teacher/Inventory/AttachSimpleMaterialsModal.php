<?php

namespace App\Livewire\Pages\App\Teacher\Inventory;

use App\Models\Inventory;
use App\Models\Material;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class AttachSimpleMaterialsModal extends Component
{
    use AuthorizesRequests;

    public int $inventoryId;

    public bool $showModal = false;

    public bool $busy = false;

    public string $search = '';

    /**
     * @var array<int, int>
     */
    public array $selectedMaterialIds = [];

    /**
     * @var array<int|string, int|string|null>
     */
    public array $minimumStockByMaterialId = [];

    public function mount(int $inventoryId): void
    {
        $this->inventoryId = $inventoryId;
    }

    #[On('open-teacher-inventory-attach-simple-materials-modal')]
    public function openModal(?int $inventoryId = null): void
    {
        if ($inventoryId !== null && $inventoryId !== $this->inventoryId) {
            return;
        }

        $this->resetValidation();
        $this->search = '';
        $this->selectedMaterialIds = [];
        $this->minimumStockByMaterialId = [];
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetValidation();
        $this->search = '';
        $this->selectedMaterialIds = [];
        $this->minimumStockByMaterialId = [];
    }

    public function toggleMaterial(int $materialId): void
    {
        $selectedIds = collect($this->selectedMaterialIds)
            ->map(fn (mixed $id): int => (int) $id)
            ->values();

        $this->selectedMaterialIds = $selectedIds->contains($materialId)
            ? $selectedIds->reject(fn (int $id): bool => $id === $materialId)->values()->all()
            : $selectedIds->push($materialId)->unique()->values()->all();
    }

    public function save(): void
    {
        if ($this->busy) {
            return;
        }

        $this->busy = true;

        try {
            $inventory = $this->inventory();
            $availableMaterialIds = $this->availableMaterialIds($inventory);

            $this->selectedMaterialIds = $this->normalizedSelectedMaterialIds();
            $normalizedMinimumStocks = $this->normalizedMinimumStocks();
            $this->minimumStockByMaterialId = $normalizedMinimumStocks;

            $validated = $this->validate([
                'selectedMaterialIds' => ['required', 'array', 'min:1'],
                'selectedMaterialIds.*' => ['integer', Rule::in($availableMaterialIds->all())],
                'minimumStockByMaterialId' => ['array'],
                'minimumStockByMaterialId.*' => ['nullable', 'integer', 'min:0'],
            ], [
                'selectedMaterialIds.required' => __('Selecione pelo menos um item simples para adicionar ao estoque.'),
                'selectedMaterialIds.array' => __('A seleção de itens informada é inválida.'),
                'selectedMaterialIds.min' => __('Selecione pelo menos um item simples para adicionar ao estoque.'),
                'selectedMaterialIds.*.integer' => __('A seleção de itens informada é inválida.'),
                'selectedMaterialIds.*.in' => __('Selecione apenas itens simples disponíveis para este estoque.'),
                'minimumStockByMaterialId.array' => __('Os mínimos informados para o professor são inválidos.'),
                'minimumStockByMaterialId.*.integer' => __('O mínimo do professor deve ser um número inteiro.'),
                'minimumStockByMaterialId.*.min' => __('O mínimo do professor não pode ser negativo.'),
            ]);

            DB::transaction(function () use ($inventory, $validated, $normalizedMinimumStocks): void {
                $payload = collect($validated['selectedMaterialIds'])
                    ->mapWithKeys(fn (int $materialId): array => [
                        $materialId => [
                            'received_items' => 0,
                            'current_quantity' => 0,
                            'lost_items' => 0,
                            'minimum_stock' => $normalizedMinimumStocks[$materialId] ?? 0,
                        ],
                    ])
                    ->all();

                $inventory->materials()->syncWithoutDetaching($payload);
            });

            $this->dispatch('teacher-inventory-stock-updated', inventoryId: $inventory->id);
            $this->dispatch('toast', type: 'success', message: __('Itens simples adicionados ao estoque com sucesso.'));
            $this->closeModal();
        } finally {
            $this->busy = false;
        }
    }

    public function render(): View
    {
        $inventory = $this->inventory();
        $materials = $this->showModal
            ? $this->availableMaterials($inventory)
            : collect();

        return view('livewire.pages.app.teacher.inventory.attach-simple-materials-modal', [
            'inventory' => $inventory,
            'materials' => $materials,
            'hasAvailableMaterials' => $materials->isNotEmpty(),
        ]);
    }

    /**
     * @return Collection<int, Material>
     */
    private function availableMaterials(Inventory $inventory): Collection
    {
        $search = trim($this->search);

        return Material::query()
            ->where('type', 'simple')
            ->where('is_active', true)
            ->whereNotIn('id', $this->availableMaterialIds($inventory, invert: true)->all())
            ->when($search !== '', fn ($query) => $query->where('name', 'like', '%'.$search.'%'))
            ->orderBy('name')
            ->get(['id', 'name', 'photo', 'price']);
    }

    /**
     * @return Collection<int, int>
     */
    private function availableMaterialIds(Inventory $inventory, bool $invert = false): Collection
    {
        $linkedMaterialIds = $inventory->materials()
            ->where('materials.type', 'simple')
            ->pluck('materials.id')
            ->map(fn (mixed $id): int => (int) $id);

        $query = Material::query()
            ->where('type', 'simple')
            ->where('is_active', true);

        if ($invert) {
            return $linkedMaterialIds;
        }

        return $query
            ->whereNotIn('id', $linkedMaterialIds->all())
            ->pluck('id')
            ->map(fn (mixed $id): int => (int) $id)
            ->values();
    }

    private function inventory(): Inventory
    {
        $inventory = Inventory::query()
            ->with('responsibleUser:id,name')
            ->findOrFail($this->inventoryId);

        $this->authorize('update', $inventory);

        return $inventory;
    }

    /**
     * @return array<int, int>
     */
    private function normalizedSelectedMaterialIds(): array
    {
        return collect($this->selectedMaterialIds)
            ->map(fn (mixed $id): int => (int) $id)
            ->filter(fn (int $id): bool => $id > 0)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return array<int, int>
     */
    private function normalizedMinimumStocks(): array
    {
        return collect($this->minimumStockByMaterialId)
            ->mapWithKeys(function (mixed $value, mixed $materialId): array {
                $normalizedMaterialId = (int) $materialId;
                $normalizedValue = trim((string) $value);

                return [$normalizedMaterialId => $normalizedValue === '' ? 0 : (int) $normalizedValue];
            })
            ->filter(fn (int $value, int $materialId): bool => $materialId > 0 && $value >= 0)
            ->all();
    }
}
