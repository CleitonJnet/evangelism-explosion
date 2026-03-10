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
});

it('disables the composite item button when simple items exist but none are active in the current stock', function (): void {
    $director = createDirectorForInventoryShowPage();
    $inventory = Inventory::query()->create(['name' => 'Central', 'kind' => 'central']);
    $material = Material::query()->create([
        'name' => 'Manual base',
        'type' => 'simple',
        'is_active' => false,
    ]);
    $inventory->materials()->attach($material->id, [
        'received_items' => 10,
        'current_quantity' => 10,
        'lost_items' => 0,
    ]);

    $response = $this->actingAs($director)->get(route('app.director.inventory.show', ['inventory' => $inventory]));

    $response->assertSuccessful();
    $response->assertSee('Novo composto');
    $response->assertSee('Cadastre ou mantenha pelo menos um item simples ativo para liberar produtos compostos');
    $response->assertSee('hasActiveSimpleMaterials: false', false);
});

it('disables the composite item button when active simple items exist only in another stock', function (): void {
    $director = createDirectorForInventoryShowPage();
    $inventory = Inventory::query()->create(['name' => 'Central A', 'kind' => 'central']);
    $otherInventory = Inventory::query()->create(['name' => 'Central B', 'kind' => 'central']);
    $material = Material::query()->create([
        'name' => 'Manual ativo',
        'type' => 'simple',
        'is_active' => true,
    ]);
    $otherInventory->materials()->attach($material->id, [
        'received_items' => 10,
        'current_quantity' => 10,
        'lost_items' => 0,
    ]);

    $response = $this->actingAs($director)->get(route('app.director.inventory.show', ['inventory' => $inventory]));

    $response->assertSuccessful();
    $response->assertSee('Novo composto');
    $response->assertSee('Cadastre ou mantenha pelo menos um item simples ativo para liberar produtos compostos');
    $response->assertSee('hasActiveSimpleMaterials: false', false);
});

it('enables the composite item button when there is at least one active simple item with stock in the current inventory', function (): void {
    $director = createDirectorForInventoryShowPage();
    $inventory = Inventory::query()->create(['name' => 'Central', 'kind' => 'central']);
    $material = Material::query()->create([
        'name' => 'Manual ativo',
        'type' => 'simple',
        'is_active' => true,
    ]);
    $inventory->materials()->attach($material->id, [
        'received_items' => 10,
        'current_quantity' => 10,
        'lost_items' => 0,
    ]);

    $response = $this->actingAs($director)->get(route('app.director.inventory.show', ['inventory' => $inventory]));

    $response->assertSuccessful();
    $response->assertSee('Novo composto');
    $response->assertSee("window.Livewire.dispatch('open-director-material-create-modal', { type: 'composite' });", false);
    $response->assertSee('hasActiveSimpleMaterials: true', false);
});

it('opens composite item rows in the director inventory on the exit tab', function (): void {
    $inventory = Inventory::query()->create([
        'name' => 'Estoque Central',
        'kind' => 'central',
        'is_active' => true,
    ]);

    $component = Material::query()->create([
        'name' => 'Manual',
        'type' => 'simple',
        'is_active' => true,
    ]);

    $composite = Material::query()->create([
        'name' => 'Kit missionario',
        'type' => 'composite',
        'is_active' => true,
    ]);

    \App\Models\MaterialComponent::query()->create([
        'parent_material_id' => $composite->id,
        'component_material_id' => $component->id,
        'quantity' => 1,
    ]);

    $inventory->materials()->attach($component->id, [
        'received_items' => 5,
        'current_quantity' => 5,
        'lost_items' => 0,
    ]);

    $response = $this->actingAs(createDirectorForInventoryShowPage())
        ->get(route('app.director.inventory.show', ['inventory' => $inventory]));

    $response->assertOk();
    $response->assertSee("window.Livewire.dispatch('open-director-material-edit-modal', { materialId: {$composite->id}, tab: 'exit' });", false);
});
