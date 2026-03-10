<?php

namespace App\Livewire\Pages\App\Teacher\Inventory;

use App\Models\Inventory;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    private const STATE_NAMES_BY_UF = [
        'AC' => 'Acre',
        'AL' => 'Alagoas',
        'AP' => 'Amapa',
        'AM' => 'Amazonas',
        'BA' => 'Bahia',
        'CE' => 'Ceara',
        'DF' => 'Distrito Federal',
        'ES' => 'Espirito Santo',
        'GO' => 'Goias',
        'MA' => 'Maranhao',
        'MT' => 'Mato Grosso',
        'MS' => 'Mato Grosso do Sul',
        'MG' => 'Minas Gerais',
        'PA' => 'Para',
        'PB' => 'Paraiba',
        'PR' => 'Parana',
        'PE' => 'Pernambuco',
        'PI' => 'Piaui',
        'RJ' => 'Rio de Janeiro',
        'RN' => 'Rio Grande do Norte',
        'RS' => 'Rio Grande do Sul',
        'RO' => 'Rondonia',
        'RR' => 'Roraima',
        'SC' => 'Santa Catarina',
        'SP' => 'Sao Paulo',
        'SE' => 'Sergipe',
        'TO' => 'Tocantins',
    ];

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
        $search = trim($this->search);
        $matchingStateUfs = $this->matchingStateUfs($search);

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
            ->when($search !== '', function ($query) use ($search, $matchingStateUfs): void {
                $query->where(function ($innerQuery) use ($search, $matchingStateUfs): void {
                    $innerQuery
                        ->where('name', 'like', '%'.$search.'%')
                        ->orWhere('city', 'like', '%'.$search.'%')
                        ->orWhere('state', 'like', '%'.$search.'%')
                        ->orWhereHas('responsibleUser', fn ($userQuery) => $userQuery->where('name', 'like', '%'.$search.'%'));

                    if ($matchingStateUfs !== []) {
                        $innerQuery->orWhereIn('state', $matchingStateUfs);
                    }
                });
            })
            ->when($this->statusFilter !== '', fn ($query) => $query->where('is_active', $this->statusFilter === 'active'))
            ->orderBy('name');
    }

    /**
     * @return array<int, string>
     */
    private function matchingStateUfs(string $search): array
    {
        if ($search === '') {
            return [];
        }

        $normalizedSearch = Str::lower(Str::ascii($search));

        return array_keys(array_filter(
            self::STATE_NAMES_BY_UF,
            fn (string $stateName, string $uf): bool => str_contains(Str::lower(Str::ascii($stateName)), $normalizedSearch)
                || str_contains(Str::lower($uf), $normalizedSearch),
            ARRAY_FILTER_USE_BOTH,
        ));
    }
}
