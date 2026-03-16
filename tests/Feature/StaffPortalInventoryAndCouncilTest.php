<?php

use App\Models\Inventory;
use App\Models\Material;
use App\Models\Role;
use App\Models\User;
use App\Services\Inventory\StockMovementService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createStaffPortalUser(array $roles): User
{
    $user = User::factory()->create();

    $roleIds = collect($roles)
        ->map(fn (string $roleName): int => Role::query()->firstOrCreate(['name' => $roleName])->id)
        ->all();

    $user->roles()->syncWithoutDetaching($roleIds);

    return $user->fresh();
}

it('shows central inventory inside the staff portal using only central stock data', function (): void {
    $director = createStaffPortalUser(['Director']);
    $centralInventory = Inventory::query()->create([
        'name' => 'Estoque Nacional',
        'kind' => 'central',
        'city' => 'Sao Paulo',
        'state' => 'SP',
        'is_active' => true,
    ]);
    Inventory::query()->create([
        'name' => 'Estoque Base Sul',
        'kind' => 'base',
        'city' => 'Curitiba',
        'state' => 'PR',
        'is_active' => true,
    ]);
    $material = Material::query()->create([
        'name' => 'Manual do Conselho',
        'minimum_stock' => 5,
    ]);

    app(StockMovementService::class)->addStock($centralInventory, $material, 3, $director, notes: 'Saldo inicial');

    $this->actingAs($director)
        ->get(route('app.portal.staff.inventory.index'))
        ->assertSuccessful()
        ->assertSee('Estoque central')
        ->assertSee('Estoque Nacional')
        ->assertDontSee('Estoque Base Sul')
        ->assertSee('Alertas')
        ->assertSee('Abrir modulo central atual');
});

it('shows the council landing separated from event operation concerns', function (): void {
    $board = createStaffPortalUser(['Board']);

    $this->actingAs($board)
        ->get(route('app.portal.staff.council.index'))
        ->assertSuccessful()
        ->assertSee('Conselho Nacional')
        ->assertSee('Documentos institucionais')
        ->assertSee('Pautas e agendas')
        ->assertSee('Deliberacoes e acompanhamentos')
        ->assertDontSee('Abrir comparacao');
});

it('renders the consolidated staff menu with panel, bases, reports, inventory and council', function (): void {
    $director = createStaffPortalUser(['Director']);

    $this->actingAs($director)
        ->get(route('app.portal.staff.dashboard'))
        ->assertSuccessful()
        ->assertSeeInOrder([
            'Painel',
            'Bases acompanhadas',
            'Relatorios',
            'Estoque central',
            'Conselho',
        ]);
});
