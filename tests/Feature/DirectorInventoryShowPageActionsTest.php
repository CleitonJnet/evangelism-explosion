<?php

use App\Models\Inventory;
use App\Models\Material;
use App\Models\Role;
use App\Models\User;

function createDirectorForInventoryShowPage(): User
{
    $director = User::factory()->create();
    $directorRole = Role::query()->firstOrCreate(['name' => 'Director']);
    $director->roles()->syncWithoutDetaching([$directorRole->id]);

    return $director;
}

it('disables the composite item button when there are no simple items registered', function (): void {
    $director = createDirectorForInventoryShowPage();
    $inventory = Inventory::query()->create(['name' => 'Central', 'kind' => 'central']);

    $response = $this->actingAs($director)->get(route('app.director.inventory.show', ['inventory' => $inventory]));

    $response->assertSuccessful();
    $response->assertSee('Novo composto');
    $response->assertSee('Cadastre ou mantenha pelo menos um item simples ativo para liberar produtos compostos');
    $response->assertSee('hasActiveSimpleMaterials: false', false);
    $response->assertSee('pointer-events-none !border-slate-300 !bg-slate-100 !text-slate-400', false);
});

it('disables the composite item button when simple items exist but none are active', function (): void {
    $director = createDirectorForInventoryShowPage();
    $inventory = Inventory::query()->create(['name' => 'Central', 'kind' => 'central']);
    Material::query()->create([
        'name' => 'Manual base',
        'type' => 'simple',
        'is_active' => false,
    ]);

    $response = $this->actingAs($director)->get(route('app.director.inventory.show', ['inventory' => $inventory]));

    $response->assertSuccessful();
    $response->assertSee('Novo composto');
    $response->assertSee('Cadastre ou mantenha pelo menos um item simples ativo para liberar produtos compostos');
    $response->assertSee('hasActiveSimpleMaterials: false', false);
    $response->assertSee('pointer-events-none !border-slate-300 !bg-slate-100 !text-slate-400', false);
});

it('enables the composite item button when there is at least one active simple item registered', function (): void {
    $director = createDirectorForInventoryShowPage();
    $inventory = Inventory::query()->create(['name' => 'Central', 'kind' => 'central']);
    Material::query()->create([
        'name' => 'Manual inativo',
        'type' => 'simple',
        'is_active' => false,
    ]);
    Material::query()->create([
        'name' => 'Manual ativo',
        'type' => 'simple',
        'is_active' => true,
    ]);

    $response = $this->actingAs($director)->get(route('app.director.inventory.show', ['inventory' => $inventory]));

    $response->assertSuccessful();
    $response->assertSee('Novo composto');
    $response->assertSee('hasActiveSimpleMaterials: true', false);
    $response->assertSee("window.Livewire.dispatch('open-director-material-create-modal', { type: 'composite' });", false);
});
