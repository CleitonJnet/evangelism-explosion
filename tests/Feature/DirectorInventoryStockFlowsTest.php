<?php

use App\Livewire\Pages\App\Director\Inventory\View;
use App\Models\Inventory;
use App\Models\Material;
use App\Models\MaterialComponent;
use App\Models\Role;
use App\Models\User;
use Livewire\Livewire;

function createTeacherUserForInventoryTests(): User
{
    $teacher = User::factory()->create();
    $teacherRole = Role::query()->firstOrCreate(['name' => 'Teacher']);
    $teacher->roles()->syncWithoutDetaching([$teacherRole->id]);

    return $teacher;
}

it('creates and edits an inventory', function (): void {
    $teacher = createTeacherUserForInventoryTests();

    Livewire::test('pages.app.director.inventory.create-inventory-modal')
        ->call('openModal')
        ->set('name', 'Estoque do Professor João')
        ->set('kind', 'teacher')
        ->set('user_id', $teacher->id)
        ->set('status', 'active')
        ->set('address.city', 'Campinas')
        ->set('address.state', 'SP')
        ->call('save')
        ->assertDispatched('director-inventory-created');

    $inventory = Inventory::query()->where('name', 'Estoque do Professor João')->first();

    expect($inventory)->not->toBeNull();
    expect($inventory?->kind)->toBe('teacher');
    expect($inventory?->user_id)->toBe($teacher->id);

    Livewire::test('pages.app.director.inventory.edit-inventory-modal', ['inventoryId' => $inventory->id])
        ->call('openModal', $inventory->id)
        ->set('name', 'Estoque Local Atualizado')
        ->set('status', 'inactive')
        ->call('save')
        ->assertDispatched('director-inventory-updated');

    expect($inventory->fresh()?->name)->toBe('Estoque Local Atualizado');
    expect($inventory->fresh()?->is_active)->toBeFalse();
});

it('registers stock entry through the stock action modal', function (): void {
    $director = User::factory()->create();
    $inventory = Inventory::query()->create(['name' => 'Central', 'kind' => 'central']);
    $material = Material::query()->create(['name' => 'Apostila']);

    Livewire::actingAs($director)
        ->test('pages.app.director.inventory.stock-action-modal', ['inventoryId' => $inventory->id])
        ->call('openModal', $inventory->id, 'entry')
        ->set('material_id', $material->id)
        ->set('quantity', 10)
        ->set('notes', 'Entrada inicial')
        ->call('save')
        ->assertDispatched('director-inventory-stock-updated');

    expect($inventory->currentQuantityFor($material))->toBe(10);
});

it('registers composite stock exit and consumes its components', function (): void {
    $director = User::factory()->create();
    $inventory = Inventory::query()->create(['name' => 'Central', 'kind' => 'central']);
    $kit = Material::query()->create(['name' => 'Kit aluno', 'type' => 'composite']);
    $manual = Material::query()->create(['name' => 'Manual']);

    MaterialComponent::query()->create([
        'parent_material_id' => $kit->id,
        'component_material_id' => $manual->id,
        'quantity' => 2,
    ]);

    app(\App\Services\Inventory\StockMovementService::class)->addStock($inventory, $kit, 5, $director);
    app(\App\Services\Inventory\StockMovementService::class)->addStock($inventory, $manual, 20, $director);

    Livewire::actingAs($director)
        ->test('pages.app.director.inventory.stock-action-modal', ['inventoryId' => $inventory->id])
        ->call('openModal', $inventory->id, 'exit')
        ->set('material_id', $kit->id)
        ->set('quantity', 2)
        ->call('save')
        ->assertDispatched('director-inventory-stock-updated');

    expect($inventory->currentQuantityFor($kit))->toBe(3);
    expect($inventory->currentQuantityFor($manual))->toBe(16);
});

it('registers adjustment and loss', function (): void {
    $director = User::factory()->create();
    $inventory = Inventory::query()->create(['name' => 'Central', 'kind' => 'central']);
    $material = Material::query()->create(['name' => 'Crachá']);

    app(\App\Services\Inventory\StockMovementService::class)->addStock($inventory, $material, 10, $director);

    Livewire::actingAs($director)
        ->test('pages.app.director.inventory.stock-action-modal', ['inventoryId' => $inventory->id])
        ->call('openModal', $inventory->id, 'adjustment')
        ->set('material_id', $material->id)
        ->set('target_quantity', 7)
        ->call('save')
        ->assertDispatched('director-inventory-stock-updated');

    expect($inventory->currentQuantityFor($material))->toBe(7);

    Livewire::actingAs($director)
        ->test('pages.app.director.inventory.stock-action-modal', ['inventoryId' => $inventory->id])
        ->call('openModal', $inventory->id, 'loss')
        ->set('material_id', $material->id)
        ->set('quantity', 2)
        ->call('save')
        ->assertDispatched('director-inventory-stock-updated');

    expect($inventory->currentQuantityFor($material))->toBe(5);
});

it('transfers stock between inventories', function (): void {
    $director = User::factory()->create();
    $source = Inventory::query()->create(['name' => 'Central', 'kind' => 'central']);
    $destination = Inventory::query()->create(['name' => 'Professor', 'kind' => 'teacher']);
    $material = Material::query()->create(['name' => 'Pasta']);

    app(\App\Services\Inventory\StockMovementService::class)->addStock($source, $material, 8, $director);

    Livewire::actingAs($director)
        ->test('pages.app.director.inventory.transfer-modal', ['inventoryId' => $source->id])
        ->call('openModal', $source->id)
        ->set('destination_inventory_id', $destination->id)
        ->set('material_id', $material->id)
        ->set('quantity', 3)
        ->set('notes', 'Transferência para aula')
        ->call('save')
        ->assertDispatched('director-inventory-stock-updated');

    expect($source->currentQuantityFor($material))->toBe(5);
    expect($destination->currentQuantityFor($material))->toBe(3);
});

it('shows coherent balances and movement history on the inventory detail page', function (): void {
    $director = User::factory()->create();
    $inventory = Inventory::query()->create(['name' => 'Central', 'kind' => 'central']);
    $material = Material::query()->create(['name' => 'Livro base', 'minimum_stock' => 6]);

    app(\App\Services\Inventory\StockMovementService::class)->addStock($inventory, $material, 5, $director, notes: 'Saldo inicial');

    Livewire::actingAs($director)
        ->test(View::class, ['inventory' => $inventory])
        ->assertSee('Central')
        ->assertSee('Livro base')
        ->assertSee('Abaixo do mínimo')
        ->assertSee('entry')
        ->assertSee('Saldo inicial');
});
