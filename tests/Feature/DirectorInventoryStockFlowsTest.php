<?php

use App\Livewire\Pages\App\Director\Inventory\Index as InventoryIndex;
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

function createDirectorUserForInventoryTests(): User
{
    $director = User::factory()->create();
    $directorRole = Role::query()->firstOrCreate(['name' => 'Director']);
    $director->roles()->syncWithoutDetaching([$directorRole->id]);

    return $director;
}

it('creates and edits an inventory', function (): void {
    $teacher = createTeacherUserForInventoryTests();

    Livewire::test('pages.app.director.inventory.create-inventory-modal')
        ->call('openModal')
        ->set('name', 'Estoque do Professor João')
        ->set('kind', 'teacher')
        ->set('user_id', $teacher->id)
        ->set('address.city', 'Campinas')
        ->set('address.state', 'SP')
        ->call('save')
        ->assertDispatched('director-inventory-created');

    $inventory = Inventory::query()->where('name', 'Estoque do Professor João')->first();

    expect($inventory)->not->toBeNull();
    expect($inventory?->kind)->toBe('teacher');
    expect($inventory?->user_id)->toBe($teacher->id);
    expect($inventory?->is_active)->toBeTrue();

    Livewire::test('pages.app.director.inventory.edit-inventory-modal', ['inventoryId' => $inventory->id])
        ->call('openModal', $inventory->id)
        ->set('name', 'Estoque Local Atualizado')
        ->call('promptStatusToggle')
        ->assertSet('showStatusConfirmationModal', true)
        ->call('confirmStatusToggle')
        ->assertSet('status', 'inactive')
        ->call('save')
        ->assertDispatched('director-inventory-updated');

    expect($inventory->fresh()?->name)->toBe('Estoque Local Atualizado');
    expect($inventory->fresh()?->is_active)->toBeFalse();
});

it('deletes an inventory from the listing after confirmation', function (): void {
    $director = createDirectorUserForInventoryTests();
    $inventory = Inventory::query()->create(['name' => 'Estoque temporário', 'kind' => 'central']);

    Livewire::actingAs($director)
        ->test(InventoryIndex::class)
        ->call('openDeleteModal', $inventory->id)
        ->assertSet('showDeleteModal', true)
        ->assertSet('selectedInventoryName', 'Estoque temporário')
        ->call('deleteSelectedInventory')
        ->assertSet('showDeleteModal', false);

    expect(Inventory::query()->find($inventory->id))->toBeNull();
});

it('blocks inventory deletion when it already has stock movements', function (): void {
    $director = createDirectorUserForInventoryTests();
    $inventory = Inventory::query()->create(['name' => 'Estoque auditado', 'kind' => 'central']);
    $material = Material::query()->create(['name' => 'Manual auditado']);

    app(\App\Services\Inventory\StockMovementService::class)->addStock($inventory, $material, 3, $director);

    Livewire::actingAs($director)
        ->test(InventoryIndex::class)
        ->call('openDeleteModal', $inventory->id)
        ->assertSet('showDeleteModal', true)
        ->assertSet(
            'selectedInventoryDeletionBlockedReason',
            'Este estoque não pode ser excluído porque já possui movimentações registradas no histórico auditável.',
        )
        ->call('deleteSelectedInventory')
        ->assertSet('showDeleteModal', true);

    expect(Inventory::query()->find($inventory->id))->not->toBeNull();
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

it('registers stock entry from the product modal entry tab', function (): void {
    $director = User::factory()->create();
    $inventory = Inventory::query()->create(['name' => 'Central', 'kind' => 'central']);
    $material = Material::query()->create(['name' => 'Livro de classe']);

    Livewire::actingAs($director)
        ->test('pages.app.director.inventory.edit-modal', ['materialId' => $material->id, 'inventoryId' => $inventory->id])
        ->call('openModal', $material->id, 'entry')
        ->set('entry_quantity', 7)
        ->set('entry_notes', 'Entrada pela modal do produto')
        ->call('saveEntry')
        ->assertDispatched('director-inventory-stock-updated');

    expect($inventory->currentQuantityFor($material))->toBe(7);
});

it('guides material registration when there are no materials to move', function (): void {
    $director = User::factory()->create();
    $inventory = Inventory::query()->create(['name' => 'Central', 'kind' => 'central']);

    Livewire::actingAs($director)
        ->test('pages.app.director.inventory.stock-action-modal', ['inventoryId' => $inventory->id])
        ->call('openModal', $inventory->id, 'entry')
        ->assertSee('Nenhum material cadastrado ainda')
        ->assertSee('item simples ou um produto composto');
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

it('transfers stock from the product modal transfer tab', function (): void {
    $director = User::factory()->create();
    $source = Inventory::query()->create(['name' => 'Central', 'kind' => 'central']);
    $destination = Inventory::query()->create(['name' => 'Professor', 'kind' => 'teacher']);
    $material = Material::query()->create(['name' => 'Caderno']);

    app(\App\Services\Inventory\StockMovementService::class)->addStock($source, $material, 6, $director);

    Livewire::actingAs($director)
        ->test('pages.app.director.inventory.edit-modal', ['materialId' => $material->id, 'inventoryId' => $source->id])
        ->call('openModal', $material->id, 'transfer')
        ->set('transfer_destination_inventory_id', $destination->id)
        ->set('transfer_quantity', 4)
        ->set('transfer_notes', 'Transferência pela modal do produto')
        ->call('saveTransfer')
        ->assertDispatched('director-inventory-stock-updated');

    expect($source->currentQuantityFor($material))->toBe(2);
    expect($destination->currentQuantityFor($material))->toBe(4);
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

it('shows simple and composite products even before any stock entry', function (): void {
    $director = User::factory()->create();
    $inventory = Inventory::query()->create(['name' => 'Central', 'kind' => 'central']);

    $simpleMaterial = Material::query()->create([
        'name' => 'Livro sem saldo',
        'type' => 'simple',
        'minimum_stock' => 2,
    ]);

    $compositeMaterial = Material::query()->create([
        'name' => 'Kit sem saldo',
        'type' => 'composite',
        'minimum_stock' => 1,
    ]);

    MaterialComponent::query()->create([
        'parent_material_id' => $compositeMaterial->id,
        'component_material_id' => $simpleMaterial->id,
        'quantity' => 1,
    ]);

    Livewire::actingAs($director)
        ->test(View::class, ['inventory' => $inventory])
        ->assertSee('Kit sem saldo')
        ->assertSee('Livro sem saldo')
        ->assertSee('0');
});

it('refreshes the product tables after a material is deleted', function (): void {
    $director = User::factory()->create();
    $inventory = Inventory::query()->create(['name' => 'Central', 'kind' => 'central']);
    $material = Material::query()->create([
        'name' => 'Produto removível',
        'type' => 'simple',
    ]);

    $component = Livewire::actingAs($director)
        ->test(View::class, ['inventory' => $inventory])
        ->assertSee('Produto removível');

    $material->delete();

    $component
        ->dispatch('director-material-deleted', materialId: $material->id)
        ->assertDontSee('Produto removível');
});

it('refreshes the product tables after a material is created', function (): void {
    $director = User::factory()->create();
    $inventory = Inventory::query()->create(['name' => 'Central', 'kind' => 'central']);

    $component = Livewire::actingAs($director)
        ->test(View::class, ['inventory' => $inventory])
        ->assertDontSee('Produto recém-cadastrado');

    $material = Material::query()->create([
        'name' => 'Produto recém-cadastrado',
        'type' => 'simple',
    ]);

    $component
        ->dispatch('director-material-created', materialId: $material->id)
        ->assertSee('Produto recém-cadastrado');
});
