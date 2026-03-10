<?php

namespace App\Livewire\Pages\App\Teacher\Inventory;

use App\Models\Inventory;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    public string $search = '';

    public string $statusFilter = '';

    public int $perPage = 12;

    #[On('teacher-inventory-updated')]
    #[On('teacher-inventory-stock-updated')]
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

    public function render(): View
    {
        $user = Auth::user();

        abort_unless($user instanceof User, 403);
        $this->authorize('viewAny', Inventory::class);

        return view('livewire.pages.app.teacher.inventory.index', [
            'inventories' => $this->inventoriesQuery($user)->paginate($this->perPage),
            'statusOptions' => [
                ['value' => '', 'label' => __('Todos os status')],
                ['value' => 'active', 'label' => __('Ativos')],
                ['value' => 'inactive', 'label' => __('Inativos')],
            ],
        ]);
    }

    private function inventoriesQuery(User $user): Builder
    {
        return Inventory::query()
            ->with('responsibleUser:id,name')
            ->where('user_id', $user->id)
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
                        ->orWhere('state', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->statusFilter !== '', fn ($query) => $query->where('is_active', $this->statusFilter === 'active'))
            ->orderBy('name');
    }
}
