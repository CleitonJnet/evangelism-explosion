<?php

use App\Models\Inventory;
use App\Models\Material;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function createTeacherForInventoryAccessTest(): User
{
    $teacher = User::factory()->create();
    $teacherRole = Role::query()->firstOrCreate(['name' => 'Teacher']);
    $teacher->roles()->syncWithoutDetaching([$teacherRole->id]);

    return $teacher;
}

it('lists only inventories delegated to the authenticated teacher', function (): void {
    $teacher = createTeacherForInventoryAccessTest();
    $otherTeacher = createTeacherForInventoryAccessTest();

    $ownInventory = Inventory::query()->create([
        'name' => 'Estoque do Professor Responsável',
        'kind' => 'teacher',
        'user_id' => $teacher->id,
        'is_active' => true,
        'city' => 'Campinas',
        'state' => 'SP',
    ]);

    Inventory::query()->create([
        'name' => 'Estoque de Outro Professor',
        'kind' => 'teacher',
        'user_id' => $otherTeacher->id,
        'is_active' => true,
    ]);

    $response = $this->actingAs($teacher)->get(route('app.teacher.inventory.index'));

    $response->assertOk();
    $response->assertSeeText($ownInventory->name);
    $response->assertSeeText('Meu estoque');
    $response->assertSeeText('Rotina operacional delegada');
    $response->assertSeeText('Campinas / SP');
    $response->assertDontSeeText('Estoque de Outro Professor');
});

it('filters teacher inventories by responsible name, city, uf and full state name', function (): void {
    $teacher = createTeacherForInventoryAccessTest();
    $teacher->update(['name' => 'Professor Elias']);

    Inventory::query()->create([
        'name' => 'Estoque Interior',
        'kind' => 'teacher',
        'user_id' => $teacher->id,
        'is_active' => true,
        'city' => 'Campinas',
        'state' => 'SP',
    ]);

    Inventory::query()->create([
        'name' => 'Estoque Litoral',
        'kind' => 'teacher',
        'user_id' => $teacher->id,
        'is_active' => true,
        'city' => 'Santos',
        'state' => 'SP',
    ]);

    Livewire::actingAs($teacher)
        ->test(\App\Livewire\Pages\App\Teacher\Inventory\Index::class)
        ->set('search', 'Elias')
        ->assertSee('Estoque Interior')
        ->assertSee('Estoque Litoral')
        ->set('search', 'Campinas')
        ->assertSee('Estoque Interior')
        ->assertDontSee('Estoque Litoral')
        ->set('search', 'SP')
        ->assertSee('Estoque Interior')
        ->assertSee('Estoque Litoral')
        ->set('search', 'Sao Paulo')
        ->assertSee('Estoque Interior')
        ->assertSee('Estoque Litoral');
});

it('forbids a teacher from opening another teachers inventory pages', function (): void {
    $teacher = createTeacherForInventoryAccessTest();
    $otherTeacher = createTeacherForInventoryAccessTest();

    $foreignInventory = Inventory::query()->create([
        'name' => 'Estoque Restrito',
        'kind' => 'teacher',
        'user_id' => $otherTeacher->id,
        'is_active' => true,
    ]);

    $this->actingAs($teacher)
        ->get(route('app.teacher.inventory.show', ['inventory' => $foreignInventory]))
        ->assertForbidden();

    $this->actingAs($teacher)
        ->get(route('app.teacher.inventory.edit', ['inventory' => $foreignInventory]))
        ->assertForbidden();
});

it('allows a teacher to update delegated inventory details', function (): void {
    $teacher = createTeacherForInventoryAccessTest();

    $inventory = Inventory::query()->create([
        'name' => 'Estoque Inicial',
        'kind' => 'teacher',
        'user_id' => $teacher->id,
        'is_active' => true,
    ]);

    Livewire::actingAs($teacher)
        ->test('pages.app.teacher.inventory.edit-inventory-modal', ['inventoryId' => $inventory->id])
        ->call('openModal', $inventory->id)
        ->set('name', 'Estoque Atualizado')
        ->set('phone', '11999999999')
        ->set('address.city', 'Campinas')
        ->set('address.state', 'SP')
        ->call('save')
        ->assertDispatched('teacher-inventory-updated');

    expect($inventory->fresh()?->name)->toBe('Estoque Atualizado');
    expect($inventory->fresh()?->city)->toBe('Campinas');
    expect($inventory->fresh()?->state)->toBe('SP');
    expect($inventory->fresh()?->is_active)->toBeTrue();
});

it('allows a teacher to register stock movements only in the delegated inventory', function (): void {
    $teacher = createTeacherForInventoryAccessTest();
    $otherTeacher = createTeacherForInventoryAccessTest();

    $ownInventory = Inventory::query()->create([
        'name' => 'Meu Estoque',
        'kind' => 'teacher',
        'user_id' => $teacher->id,
        'is_active' => true,
    ]);

    $foreignInventory = Inventory::query()->create([
        'name' => 'Estoque Alheio',
        'kind' => 'teacher',
        'user_id' => $otherTeacher->id,
        'is_active' => true,
    ]);

    $material = Material::query()->create(['name' => 'Apostila do Professor']);
    $ownInventory->materials()->attach($material->id, [
        'received_items' => 0,
        'current_quantity' => 0,
        'lost_items' => 0,
    ]);

    Livewire::actingAs($teacher)
        ->test('pages.app.teacher.inventory.stock-action-modal', ['inventoryId' => $ownInventory->id])
        ->call('openModal', $ownInventory->id, 'entry')
        ->set('material_id', $material->id)
        ->set('quantity', 6)
        ->call('save')
        ->assertDispatched('teacher-inventory-stock-updated');

    expect($ownInventory->currentQuantityFor($material))->toBe(6);

    Livewire::actingAs($teacher)
        ->test('pages.app.teacher.inventory.stock-action-modal', ['inventoryId' => $foreignInventory->id])
        ->assertForbidden();
});

it('shows only delegated operational actions on the teacher inventory page', function (): void {
    $teacher = createTeacherForInventoryAccessTest();

    $inventory = Inventory::query()->create([
        'name' => 'Estoque Operacional',
        'kind' => 'teacher',
        'user_id' => $teacher->id,
        'is_active' => true,
    ]);

    $response = $this->actingAs($teacher)->get(route('app.teacher.inventory.show', ['inventory' => $inventory]));

    $response->assertOk();
    $response->assertSeeText('Para movimentar um produto, clique diretamente na linha desejada na tabela abaixo.');
    $response->assertSeeText('Editar estoque');
    $response->assertDontSee("window.Livewire.dispatch('open-teacher-inventory-stock-action-modal'", false);
    $response->assertDontSeeText('Novo item simples');
    $response->assertDontSeeText('Novo composto');
    $response->assertDontSeeText('Editar produto');
});

it('shows only materials transferred to the teachers inventory', function (): void {
    $teacher = createTeacherForInventoryAccessTest();

    $inventory = Inventory::query()->create([
        'name' => 'Estoque Delegado',
        'kind' => 'teacher',
        'user_id' => $teacher->id,
        'is_active' => true,
    ]);

    $transferredMaterial = Material::query()->create([
        'name' => 'Material Transferido',
        'type' => 'simple',
        'is_active' => true,
    ]);
    $centralOnlyMaterial = Material::query()->create([
        'name' => 'Material Só no Central',
        'type' => 'simple',
        'is_active' => true,
    ]);

    $inventory->materials()->attach($transferredMaterial->id, [
        'received_items' => 5,
        'current_quantity' => 5,
        'lost_items' => 0,
    ]);

    $response = $this->actingAs($teacher)->get(route('app.teacher.inventory.show', ['inventory' => $inventory]));

    $response->assertOk();
    $response->assertSeeText('Material Transferido');
    $response->assertDontSeeText('Material Só no Central');
});

it('shows a red zero-balance alert for teacher inventory items with zero stock', function (): void {
    $teacher = createTeacherForInventoryAccessTest();

    $inventory = Inventory::query()->create([
        'name' => 'Estoque Delegado',
        'kind' => 'teacher',
        'user_id' => $teacher->id,
        'is_active' => true,
    ]);

    $material = Material::query()->create([
        'name' => 'Material Zerado',
        'type' => 'simple',
        'is_active' => true,
        'minimum_stock' => 5,
    ]);

    $inventory->materials()->attach($material->id, [
        'received_items' => 0,
        'current_quantity' => 0,
        'lost_items' => 0,
    ]);

    $response = $this->actingAs($teacher)->get(route('app.teacher.inventory.show', ['inventory' => $inventory]));

    $response->assertOk();
    $response->assertSeeText('Saldo zerado');
});

it('shows composite items dynamically when the teacher has all required transferred components', function (): void {
    $teacher = createTeacherForInventoryAccessTest();

    $inventory = Inventory::query()->create([
        'name' => 'Estoque Delegado',
        'kind' => 'teacher',
        'user_id' => $teacher->id,
        'is_active' => true,
    ]);

    $manual = Material::query()->create([
        'name' => 'Manual Transferido',
        'type' => 'simple',
        'is_active' => true,
    ]);
    $badge = Material::query()->create([
        'name' => 'Crachá Transferido',
        'type' => 'simple',
        'is_active' => true,
    ]);
    $kit = Material::query()->create([
        'name' => 'Kit do Professor',
        'type' => 'composite',
        'is_active' => true,
    ]);

    \App\Models\MaterialComponent::query()->create([
        'parent_material_id' => $kit->id,
        'component_material_id' => $manual->id,
        'quantity' => 2,
    ]);
    \App\Models\MaterialComponent::query()->create([
        'parent_material_id' => $kit->id,
        'component_material_id' => $badge->id,
        'quantity' => 1,
    ]);

    $inventory->materials()->attach($manual->id, [
        'received_items' => 6,
        'current_quantity' => 6,
        'lost_items' => 0,
    ]);
    $inventory->materials()->attach($badge->id, [
        'received_items' => 3,
        'current_quantity' => 3,
        'lost_items' => 0,
    ]);

    $response = $this->actingAs($teacher)->get(route('app.teacher.inventory.show', ['inventory' => $inventory]));

    $response->assertOk();
    $response->assertSeeText('Kit do Professor');
    $response->assertSeeText('Até 3');
    $response->assertSee("window.Livewire.dispatch('open-teacher-material-action-modal', { materialId: {$kit->id}, tab: 'exit' });", false);
});

it('opens the teacher material modal with operational tabs for a simple item', function (): void {
    $teacher = createTeacherForInventoryAccessTest();

    $inventory = Inventory::query()->create([
        'name' => 'Estoque Delegado',
        'kind' => 'teacher',
        'user_id' => $teacher->id,
        'is_active' => true,
    ]);

    $material = Material::query()->create([
        'name' => 'Manual Transferido',
        'type' => 'simple',
        'is_active' => true,
    ]);

    $inventory->materials()->attach($material->id, [
        'received_items' => 5,
        'current_quantity' => 5,
        'lost_items' => 0,
    ]);

    Livewire::actingAs($teacher)
        ->test('pages.app.teacher.inventory.material-action-modal', ['materialId' => $material->id, 'inventoryId' => $inventory->id])
        ->assertSee('Saída manual')
        ->call('openModal', $material->id, 'entry')
        ->assertSet('activeTab', 'entry')
        ->assertSee('Entrada manual')
        ->assertSee('Perda')
        ->assertDontSee('Ajuste')
        ->assertDontSee('Editar produto');
});

it('defaults the teacher simple item modal to exit', function (): void {
    $teacher = createTeacherForInventoryAccessTest();

    $inventory = Inventory::query()->create([
        'name' => 'Estoque Delegado',
        'kind' => 'teacher',
        'user_id' => $teacher->id,
        'is_active' => true,
    ]);

    $material = Material::query()->create([
        'name' => 'Manual Transferido',
        'type' => 'simple',
        'is_active' => true,
    ]);

    $inventory->materials()->attach($material->id, [
        'received_items' => 5,
        'current_quantity' => 5,
        'lost_items' => 0,
    ]);

    $response = $this->actingAs($teacher)->get(route('app.teacher.inventory.show', ['inventory' => $inventory]));

    $response->assertOk();
    $response->assertSee("window.Livewire.dispatch('open-teacher-material-action-modal', { materialId: {$material->id}, tab: 'exit' });", false);

    Livewire::actingAs($teacher)
        ->test('pages.app.teacher.inventory.material-action-modal', ['materialId' => $material->id, 'inventoryId' => $inventory->id])
        ->call('openModal', $material->id)
        ->assertSet('activeTab', 'exit');
});

it('opens the teacher material modal with exit only for a dynamically available composite item', function (): void {
    $teacher = createTeacherForInventoryAccessTest();

    $inventory = Inventory::query()->create([
        'name' => 'Estoque Delegado',
        'kind' => 'teacher',
        'user_id' => $teacher->id,
        'is_active' => true,
    ]);

    $manual = Material::query()->create([
        'name' => 'Manual Transferido',
        'type' => 'simple',
        'is_active' => true,
    ]);
    $kit = Material::query()->create([
        'name' => 'Kit Dinâmico',
        'type' => 'composite',
        'is_active' => true,
    ]);

    \App\Models\MaterialComponent::query()->create([
        'parent_material_id' => $kit->id,
        'component_material_id' => $manual->id,
        'quantity' => 2,
    ]);

    $inventory->materials()->attach($manual->id, [
        'received_items' => 6,
        'current_quantity' => 6,
        'lost_items' => 0,
    ]);

    Livewire::actingAs($teacher)
        ->test('pages.app.teacher.inventory.material-action-modal', ['materialId' => $kit->id, 'inventoryId' => $inventory->id])
        ->call('openModal', $kit->id, 'entry')
        ->assertSet('activeTab', 'exit')
        ->assertSee('Saída manual')
        ->assertDontSee('Entrada manual')
        ->assertDontSee('Ajuste')
        ->assertDontSee('Perda')
        ->assertDontSee('Editar produto');
});

it('does not show composite items for the teacher when required components were not transferred', function (): void {
    $teacher = createTeacherForInventoryAccessTest();

    $inventory = Inventory::query()->create([
        'name' => 'Estoque Delegado',
        'kind' => 'teacher',
        'user_id' => $teacher->id,
        'is_active' => true,
    ]);

    $manual = Material::query()->create([
        'name' => 'Manual Transferido',
        'type' => 'simple',
        'is_active' => true,
    ]);
    $badge = Material::query()->create([
        'name' => 'Crachá Não Transferido',
        'type' => 'simple',
        'is_active' => true,
    ]);
    $kit = Material::query()->create([
        'name' => 'Kit Oculto',
        'type' => 'composite',
        'is_active' => true,
    ]);

    \App\Models\MaterialComponent::query()->create([
        'parent_material_id' => $kit->id,
        'component_material_id' => $manual->id,
        'quantity' => 1,
    ]);
    \App\Models\MaterialComponent::query()->create([
        'parent_material_id' => $kit->id,
        'component_material_id' => $badge->id,
        'quantity' => 1,
    ]);

    $inventory->materials()->attach($manual->id, [
        'received_items' => 5,
        'current_quantity' => 5,
        'lost_items' => 0,
    ]);

    $response = $this->actingAs($teacher)->get(route('app.teacher.inventory.show', ['inventory' => $inventory]));

    $response->assertOk();
    $response->assertDontSeeText('Kit Oculto');
});

it('lists dynamically available composite items in the teacher stock action modal', function (): void {
    $teacher = createTeacherForInventoryAccessTest();

    $inventory = Inventory::query()->create([
        'name' => 'Estoque Delegado',
        'kind' => 'teacher',
        'user_id' => $teacher->id,
        'is_active' => true,
    ]);

    $manual = Material::query()->create([
        'name' => 'Manual Transferido',
        'type' => 'simple',
        'is_active' => true,
    ]);
    $kit = Material::query()->create([
        'name' => 'Kit Dinâmico',
        'type' => 'composite',
        'is_active' => true,
    ]);

    \App\Models\MaterialComponent::query()->create([
        'parent_material_id' => $kit->id,
        'component_material_id' => $manual->id,
        'quantity' => 2,
    ]);

    $inventory->materials()->attach($manual->id, [
        'received_items' => 6,
        'current_quantity' => 6,
        'lost_items' => 0,
    ]);

    Livewire::actingAs($teacher)
        ->test('pages.app.teacher.inventory.stock-action-modal', ['inventoryId' => $inventory->id])
        ->call('openModal', $inventory->id, 'exit')
        ->assertViewHas('materialOptions', function (array $options) use ($kit): bool {
            $ids = collect($options)->pluck('value')->all();

            expect($ids)->toContain($kit->id);
            expect(collect($options)->pluck('label')->implode(' | '))->toContain('Kit Dinâmico');

            return true;
        });
});

it('allows a teacher to register exit for a dynamically available composite item', function (): void {
    $teacher = createTeacherForInventoryAccessTest();

    $inventory = Inventory::query()->create([
        'name' => 'Estoque Delegado',
        'kind' => 'teacher',
        'user_id' => $teacher->id,
        'is_active' => true,
    ]);

    $manual = Material::query()->create([
        'name' => 'Manual Transferido',
        'type' => 'simple',
        'is_active' => true,
    ]);
    $badge = Material::query()->create([
        'name' => 'Crachá Transferido',
        'type' => 'simple',
        'is_active' => true,
    ]);
    $kit = Material::query()->create([
        'name' => 'Kit Dinâmico',
        'type' => 'composite',
        'is_active' => true,
    ]);

    \App\Models\MaterialComponent::query()->create([
        'parent_material_id' => $kit->id,
        'component_material_id' => $manual->id,
        'quantity' => 2,
    ]);
    \App\Models\MaterialComponent::query()->create([
        'parent_material_id' => $kit->id,
        'component_material_id' => $badge->id,
        'quantity' => 1,
    ]);

    $inventory->materials()->attach($manual->id, [
        'received_items' => 6,
        'current_quantity' => 6,
        'lost_items' => 0,
    ]);
    $inventory->materials()->attach($badge->id, [
        'received_items' => 3,
        'current_quantity' => 3,
        'lost_items' => 0,
    ]);

    Livewire::actingAs($teacher)
        ->test('pages.app.teacher.inventory.stock-action-modal', ['inventoryId' => $inventory->id])
        ->call('openModal', $inventory->id, 'exit')
        ->set('material_id', $kit->id)
        ->set('quantity', 3)
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('teacher-inventory-stock-updated');

    expect($inventory->fresh()->currentQuantityFor($kit))->toBe(0);
    expect($inventory->fresh()->currentQuantityFor($manual))->toBe(0);
    expect($inventory->fresh()->currentQuantityFor($badge))->toBe(0);
});

it('does not list composite items in the teacher stock action modal when components are insufficient', function (): void {
    $teacher = createTeacherForInventoryAccessTest();

    $inventory = Inventory::query()->create([
        'name' => 'Estoque Delegado',
        'kind' => 'teacher',
        'user_id' => $teacher->id,
        'is_active' => true,
    ]);

    $manual = Material::query()->create([
        'name' => 'Manual Insuficiente',
        'type' => 'simple',
        'is_active' => true,
    ]);
    $kit = Material::query()->create([
        'name' => 'Kit Não Elegível',
        'type' => 'composite',
        'is_active' => true,
    ]);

    \App\Models\MaterialComponent::query()->create([
        'parent_material_id' => $kit->id,
        'component_material_id' => $manual->id,
        'quantity' => 10,
    ]);

    $inventory->materials()->attach($manual->id, [
        'received_items' => 6,
        'current_quantity' => 6,
        'lost_items' => 0,
    ]);

    Livewire::actingAs($teacher)
        ->test('pages.app.teacher.inventory.stock-action-modal', ['inventoryId' => $inventory->id])
        ->call('openModal', $inventory->id, 'exit')
        ->assertViewHas('materialOptions', function (array $options) use ($kit): bool {
            $ids = collect($options)->pluck('value')->all();

            expect($ids)->not->toContain($kit->id);

            return true;
        });
});

it('does not list dynamically available composite items for teacher entry and adjustment actions', function (): void {
    $teacher = createTeacherForInventoryAccessTest();

    $inventory = Inventory::query()->create([
        'name' => 'Estoque Delegado',
        'kind' => 'teacher',
        'user_id' => $teacher->id,
        'is_active' => true,
    ]);

    $manual = Material::query()->create([
        'name' => 'Manual Transferido',
        'type' => 'simple',
        'is_active' => true,
    ]);
    $kit = Material::query()->create([
        'name' => 'Kit Dinâmico',
        'type' => 'composite',
        'is_active' => true,
    ]);

    \App\Models\MaterialComponent::query()->create([
        'parent_material_id' => $kit->id,
        'component_material_id' => $manual->id,
        'quantity' => 2,
    ]);

    $inventory->materials()->attach($manual->id, [
        'received_items' => 6,
        'current_quantity' => 6,
        'lost_items' => 0,
    ]);

    Livewire::actingAs($teacher)
        ->test('pages.app.teacher.inventory.stock-action-modal', ['inventoryId' => $inventory->id])
        ->call('openModal', $inventory->id, 'entry')
        ->assertViewHas('materialOptions', function (array $options) use ($kit): bool {
            expect(collect($options)->pluck('value')->all())->not->toContain($kit->id);

            return true;
        });

    Livewire::actingAs($teacher)
        ->test('pages.app.teacher.inventory.stock-action-modal', ['inventoryId' => $inventory->id])
        ->call('openModal', $inventory->id, 'adjustment')
        ->assertViewHas('materialOptions', function (array $options) use ($kit): bool {
            expect(collect($options)->pluck('value')->all())->not->toContain($kit->id);

            return true;
        });
});

it('does not allow the adjustment tab for teacher item modal', function (): void {
    $teacher = createTeacherForInventoryAccessTest();

    $inventory = Inventory::query()->create([
        'name' => 'Estoque Delegado',
        'kind' => 'teacher',
        'user_id' => $teacher->id,
        'is_active' => true,
    ]);

    $material = Material::query()->create([
        'name' => 'Manual Transferido',
        'type' => 'simple',
        'is_active' => true,
    ]);

    $inventory->materials()->attach($material->id, [
        'received_items' => 5,
        'current_quantity' => 5,
        'lost_items' => 0,
    ]);

    Livewire::actingAs($teacher)
        ->test('pages.app.teacher.inventory.material-action-modal', ['materialId' => $material->id, 'inventoryId' => $inventory->id])
        ->call('openModal', $material->id, 'adjustment')
        ->assertSet('activeTab', 'exit')
        ->assertDontSee('Ajuste');
});

it('limits teacher stock actions to materials already transferred to the inventory', function (): void {
    $teacher = createTeacherForInventoryAccessTest();

    $inventory = Inventory::query()->create([
        'name' => 'Estoque Delegado',
        'kind' => 'teacher',
        'user_id' => $teacher->id,
        'is_active' => true,
    ]);

    $allowedMaterial = Material::query()->create([
        'name' => 'Material Delegado',
        'type' => 'simple',
        'is_active' => true,
    ]);
    $blockedMaterial = Material::query()->create([
        'name' => 'Material Não Transferido',
        'type' => 'simple',
        'is_active' => true,
    ]);

    $inventory->materials()->attach($allowedMaterial->id, [
        'received_items' => 2,
        'current_quantity' => 2,
        'lost_items' => 0,
    ]);

    Livewire::actingAs($teacher)
        ->test('pages.app.teacher.inventory.stock-action-modal', ['inventoryId' => $inventory->id])
        ->call('openModal', $inventory->id, 'entry')
        ->assertViewHas('materialOptions', function (array $options) use ($allowedMaterial, $blockedMaterial) {
            $ids = collect($options)->pluck('value')->all();

            expect($ids)->toContain($allowedMaterial->id);
            expect($ids)->not->toContain($blockedMaterial->id);

            return true;
        })
        ->set('material_id', $blockedMaterial->id)
        ->set('quantity', 1)
        ->call('save')
        ->assertHasErrors(['quantity']);
});
