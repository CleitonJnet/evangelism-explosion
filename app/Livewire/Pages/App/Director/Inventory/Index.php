<?php

namespace App\Livewire\Pages\App\Director\Inventory;

use App\Models\Inventory;
use Illuminate\Support\Facades\DB;
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

    public function render(): View
    {
        $inventories = Inventory::query()
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
            ->orderBy('name')
            ->paginate($this->perPage);

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
}
