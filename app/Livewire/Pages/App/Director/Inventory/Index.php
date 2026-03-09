<?php

namespace App\Livewire\Pages\App\Director\Inventory;

use App\Models\Inventory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public string $statusFilter = '';

    public string $kindFilter = '';

    public int $perPage = 12;

    public bool $showDeleteModal = false;

    public ?int $selectedInventoryId = null;

    public string $selectedInventoryName = '';

    public string $selectedInventoryDeletionBlockedReason = '';

    #[On('director-inventory-created')]
    #[On('director-inventory-updated')]
    #[On('director-inventory-stock-updated')]
    public function refreshListing(): void
    {
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingKindFilter(): void
    {
        $this->resetPage();
    }

    public function openDeleteModal(int $inventoryId): void
    {
        Gate::authorize('access-director');

        $inventory = Inventory::query()->find($inventoryId);

        if (! $inventory) {
            return;
        }

        $this->selectedInventoryId = $inventory->id;
        $this->selectedInventoryName = $inventory->name;
        $this->selectedInventoryDeletionBlockedReason = $inventory->stockMovements()->exists()
            ? __('Este estoque não pode ser excluído porque já possui movimentações registradas no histórico auditável.')
            : '';
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->selectedInventoryId = null;
        $this->selectedInventoryName = '';
        $this->selectedInventoryDeletionBlockedReason = '';
    }

    public function deleteSelectedInventory(): void
    {
        Gate::authorize('access-director');

        if (! $this->selectedInventoryId) {
            return;
        }

        $inventory = Inventory::query()->find($this->selectedInventoryId);

        if (! $inventory) {
            $this->closeDeleteModal();

            return;
        }

        if ($inventory->stockMovements()->exists()) {
            $this->selectedInventoryDeletionBlockedReason = __('Este estoque não pode ser excluído porque já possui movimentações registradas no histórico auditável.');

            return;
        }

        $inventory->delete();

        $this->closeDeleteModal();

        if ($this->isCurrentPageEmpty()) {
            $this->previousPage();
        }

        session()->flash('success', __('Estoque removido com sucesso.'));
    }

    public function render(): View
    {
        $inventories = $this->inventoriesQuery()->paginate($this->perPage);

        return view('livewire.pages.app.director.inventory.index', [
            'inventories' => $inventories,
            'statusOptions' => [
                ['value' => '', 'label' => __('Todos os status')],
                ['value' => 'active', 'label' => __('Ativos')],
                ['value' => 'inactive', 'label' => __('Inativos')],
            ],
            'kindOptions' => [
                ['value' => '', 'label' => __('Todos os tipos')],
                ['value' => 'central', 'label' => __('Central')],
                ['value' => 'teacher', 'label' => __('Professor')],
            ],
        ]);
    }

    private function isCurrentPageEmpty(): bool
    {
        $inventories = $this->inventoriesQuery()->paginate($this->perPage);

        return $inventories->isEmpty() && $inventories->currentPage() > 1;
    }

    private function inventoriesQuery(): Builder
    {
        return Inventory::query()
            ->with('responsibleUser:id,name')
            ->select('inventories.*')
            ->selectSub(
                DB::table('inventory_material')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('inventory_material.inventory_id', 'inventories.id')
                    ->where('current_quantity', '>', 0),
                'active_skus_count',
            )
            ->when($this->search !== '', function ($query): void {
                $query->where(function ($innerQuery): void {
                    $innerQuery
                        ->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('city', 'like', '%'.$this->search.'%')
                        ->orWhere('state', 'like', '%'.$this->search.'%')
                        ->orWhereHas('responsibleUser', fn ($userQuery) => $userQuery->where('name', 'like', '%'.$this->search.'%'));
                });
            })
            ->when($this->statusFilter !== '', fn ($query) => $query->where('is_active', $this->statusFilter === 'active'))
            ->when($this->kindFilter !== '', fn ($query) => $query->where('kind', $this->kindFilter))
            ->orderByRaw("CASE WHEN kind = 'central' THEN 0 ELSE 1 END")
            ->orderBy('name');
    }
}
