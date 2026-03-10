<?php

namespace App\Livewire\Pages\App\Director\Training;

use App\Livewire\Shared\Training\ViewPage;
use App\Models\Material;
use App\Models\StockMovement;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;

class View extends ViewPage
{
    protected string $trainingContext = 'director';

    /**
     * @var Collection<int, \App\Models\Material>
     */
    public Collection $courseMaterials;

    /**
     * @var Collection<int, \App\Models\Material>
     */
    public Collection $recommendedKits;

    /**
     * @var Collection<int, \App\Models\StockMovement>
     */
    public Collection $trainingStockMovements;

    /**
     * @var Collection<int, array{material_name: string, quantity: int, type: string}>
     */
    public Collection $consumedMaterialsSummary;

    #[On('training-material-delivered')]
    public function handleMaterialDelivered(?int $trainingId = null): void
    {
        if ($trainingId !== null && $trainingId !== $this->training->id) {
            return;
        }

        $this->loadTrainingData($this->training->id);
    }

    protected function loadContextTrainingData(): void
    {
        $this->training->loadMissing([
            'course.materials.components.componentMaterial',
            'stockMovements' => fn ($query) => $query
                ->with(['inventory', 'material', 'user'])
                ->latest()
                ->limit(20),
        ]);

        $this->courseMaterials = $this->training->course?->materials?->sortBy('name')->values() ?? new Collection;
        $this->recommendedKits = $this->courseMaterials
            ->filter(fn (Material $material): bool => $material->isComposite())
            ->values();
        $this->trainingStockMovements = $this->training->stockMovements->values();
        $this->consumedMaterialsSummary = StockMovement::query()
            ->with('material')
            ->where('training_id', $this->training->id)
            ->get()
            ->filter(fn (StockMovement $movement): bool => in_array($movement->movement_type, [
                StockMovement::TYPE_EXIT,
                StockMovement::TYPE_LOSS,
                StockMovement::TYPE_KIT_COMPONENT_EXIT,
                StockMovement::TYPE_TRANSFER_OUT,
            ], true))
            ->groupBy('material_id')
            ->map(function (Collection $movements): array {
                /** @var StockMovement|null $firstMovement */
                $firstMovement = $movements->first();

                return [
                    'material_name' => $firstMovement?->material?->name ?? __('Material'),
                    'quantity' => (int) $movements->sum('quantity'),
                    'type' => $firstMovement?->material?->isComposite() ? __('Composto') : __('Simples'),
                ];
            })
            ->sortBy('material_name')
            ->values();
    }

    protected function viewTemplate(): string
    {
        return 'livewire.pages.app.director.training.view';
    }
}
