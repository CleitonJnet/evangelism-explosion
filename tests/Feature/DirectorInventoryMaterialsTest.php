<?php

use App\Models\Course;
use App\Models\Inventory;
use App\Models\Material;
use App\Models\MaterialComponent;
use App\Models\Ministry;
use App\Models\StockMovement;
use App\Models\Supplier;
use Livewire\Livewire;

it('creates a simple material', function (): void {
    $ministry = Ministry::query()->create([
        'name' => 'Evangelismo',
        'initials' => 'EV',
    ]);
    $course = Course::factory()->create([
        'name' => 'Curso Base',
        'ministry_id' => $ministry->id,
    ]);

    Livewire::test('pages.app.director.inventory.create-modal')
        ->call('openModal', 'simple')
        ->set('name', 'Manual do participante')
        ->set('type', 'simple')
        ->set('status', 'active')
        ->set('price', '12,50')
        ->set('minimum_stock', 3)
        ->set('selectedCourseIds', [$course->id])
        ->set('description', 'Material impresso.')
        ->call('save')
        ->assertDispatched('director-material-created');

    $material = Material::query()->where('name', 'Manual do participante')->first();

    expect($material)->not->toBeNull();
    expect($material?->type)->toBe('simple');
    expect($material?->is_active)->toBeTrue();
    expect($material?->minimum_stock)->toBe(3);
    expect($material?->courses()->pluck('courses.id')->all())->toBe([$course->id]);
});

it('reopens creation modals with active status by default', function (): void {
    Livewire::test('pages.app.director.inventory.create-modal')
        ->call('openModal', 'simple')
        ->set('status', 'inactive')
        ->call('closeModal')
        ->call('openModal', 'simple')
        ->assertSet('status', 'active')
        ->assertSet('price', '0,00')
        ->assertSet('type', 'simple')
        ->call('closeModal')
        ->call('openModal', 'composite')
        ->assertSet('status', 'active')
        ->assertSet('price', '0,00')
        ->assertSet('type', 'composite');
});

it('creates a composite material with selected simple items', function (): void {
    $firstComponent = Material::query()->create([
        'name' => 'Livro base',
        'type' => 'simple',
    ]);

    $secondComponent = Material::query()->create([
        'name' => 'Crachá',
        'type' => 'simple',
    ]);

    Livewire::test('pages.app.director.inventory.create-modal')
        ->call('openModal', 'composite')
        ->set('name', 'Kit do aluno')
        ->set('type', 'composite')
        ->set('status', 'active')
        ->set('minimum_stock', 1)
        ->set('selectedComponentIds', [$firstComponent->id, $secondComponent->id])
        ->set('componentQuantities.'.$firstComponent->id, 2)
        ->set('componentQuantities.'.$secondComponent->id, 1)
        ->call('save')
        ->assertDispatched('director-material-created');

    $material = Material::query()->where('name', 'Kit do aluno')->first();

    expect($material?->type)->toBe('composite');
    expect($material?->componentMaterials()->pluck('materials.id')->all())->toBe([$firstComponent->id, $secondComponent->id]);
    expect($material?->components()->where('component_material_id', $firstComponent->id)->value('quantity'))->toBe(2);
    expect($material?->components()->where('component_material_id', $secondComponent->id)->value('quantity'))->toBe(1);
});

it('blocks composite items from being selected as components when creating a composite product', function (): void {
    $nestedComposite = Material::query()->create([
        'name' => 'Kit intermediario',
        'type' => 'composite',
    ]);

    Livewire::test('pages.app.director.inventory.create-modal')
        ->call('openModal', 'composite')
        ->set('name', 'Kit final')
        ->set('type', 'composite')
        ->set('status', 'active')
        ->set('minimum_stock', 1)
        ->set('selectedComponentIds', [$nestedComposite->id])
        ->set('componentQuantities.'.$nestedComposite->id, 1)
        ->call('save')
        ->assertHasErrors(['selectedComponentIds.0']);

    expect(MaterialComponent::query()->where('component_material_id', $nestedComposite->id)->exists())->toBeFalse();
});

it('links a material to multiple courses', function (): void {
    $material = Material::query()->create([
        'name' => 'Apostila base',
    ]);

    $ministry = Ministry::query()->create([
        'name' => 'Evangelismo',
        'initials' => 'EV',
    ]);

    $firstCourse = Course::factory()->create([
        'name' => 'Curso A',
        'ministry_id' => $ministry->id,
    ]);

    $secondCourse = Course::factory()->create([
        'name' => 'Curso B',
        'ministry_id' => $ministry->id,
    ]);

    Livewire::test('pages.app.director.inventory.manage-courses-modal', ['materialId' => $material->id])
        ->call('openModal', $material->id)
        ->set('selectedCourseIds', [$firstCourse->id, $secondCourse->id])
        ->call('save')
        ->assertDispatched('director-material-courses-updated');

    expect($material->fresh()->courses()->pluck('courses.id')->all())->toBe([$firstCourse->id, $secondCourse->id]);
});

it('updates course links from the material edit modal', function (): void {
    $material = Material::query()->create([
        'name' => 'Manual editavel',
    ]);

    $ministry = Ministry::query()->create([
        'name' => 'Discipulado',
        'initials' => 'DI',
    ]);

    $firstCourse = Course::factory()->create([
        'name' => 'Curso Inicial',
        'ministry_id' => $ministry->id,
    ]);

    $secondCourse = Course::factory()->create([
        'name' => 'Curso Avançado',
        'ministry_id' => $ministry->id,
    ]);

    $material->courses()->sync([$firstCourse->id]);

    Livewire::test('pages.app.director.inventory.edit-modal', ['materialId' => $material->id])
        ->call('openModal', $material->id)
        ->set('selectedCourseIds', [$secondCourse->id])
        ->call('save')
        ->assertDispatched('director-material-updated');

    expect($material->fresh()->courses()->pluck('courses.id')->all())->toBe([$secondCourse->id]);
});

it('links a material to suppliers', function (): void {
    $material = Material::query()->create([
        'name' => 'Pasta do professor',
    ]);

    $firstSupplier = Supplier::query()->create([
        'name' => 'Fornecedor A',
    ]);

    $secondSupplier = Supplier::query()->create([
        'name' => 'Fornecedor B',
    ]);

    Livewire::test('pages.app.director.inventory.manage-suppliers-modal', ['materialId' => $material->id])
        ->call('openModal', $material->id)
        ->set('selectedSupplierIds', [$firstSupplier->id, $secondSupplier->id])
        ->call('save')
        ->assertDispatched('director-material-suppliers-updated');

    expect($material->fresh()->suppliers()->pluck('suppliers.id')->all())->toBe([$firstSupplier->id, $secondSupplier->id]);
});

it('adds and edits components of a composite material', function (): void {
    $material = Material::query()->create([
        'name' => 'Kit missão',
        'type' => 'composite',
    ]);

    $component = Material::query()->create([
        'name' => 'Crachá',
    ]);

    Livewire::test('pages.app.director.inventory.manage-components-modal', ['materialId' => $material->id])
        ->call('openModal', $material->id)
        ->set('selectedComponentIds', [$component->id])
        ->set('componentQuantities.'.$component->id, 2)
        ->call('saveComposition')
        ->assertDispatched('director-material-components-updated');

    $materialComponent = MaterialComponent::query()
        ->where('parent_material_id', $material->id)
        ->where('component_material_id', $component->id)
        ->first();

    expect($materialComponent)->not->toBeNull();
    expect($materialComponent?->quantity)->toBe(2);

    Livewire::test('pages.app.director.inventory.manage-components-modal', ['materialId' => $material->id])
        ->call('openModal', $material->id)
        ->set('selectedComponentIds', [$component->id])
        ->set('componentQuantities.'.$component->id, 5)
        ->call('saveComposition')
        ->assertDispatched('director-material-components-updated');

    expect($materialComponent->fresh()?->quantity)->toBe(5);
});

it('shows composition management inside the edit tab for composite materials', function (): void {
    $material = Material::query()->create([
        'name' => 'Kit missionario',
        'type' => 'composite',
    ]);

    Livewire::test('pages.app.director.inventory.edit-modal', ['materialId' => $material->id])
        ->call('openModal', $material->id, 'edit')
        ->assertSee('Gerenciar composição')
        ->assertSee('Composição do produto composto');
});

it('blocks self reference and duplicate component registration', function (): void {
    $material = Material::query()->create([
        'name' => 'Kit liderança',
        'type' => 'composite',
    ]);

    $component = Material::query()->create([
        'name' => 'Livro de apoio',
    ]);

    Livewire::test('pages.app.director.inventory.manage-components-modal', ['materialId' => $material->id])
        ->call('openModal', $material->id)
        ->set('selectedComponentIds', [$material->id])
        ->set('componentQuantities.'.$material->id, 1)
        ->call('saveComposition')
        ->assertHasErrors(['selectedComponentIds.0']);

    Livewire::test('pages.app.director.inventory.manage-components-modal', ['materialId' => $material->id])
        ->call('openModal', $material->id)
        ->set('selectedComponentIds', [$component->id, $component->id])
        ->set('componentQuantities.'.$component->id, 3)
        ->call('saveComposition')
        ->assertHasErrors(['selectedComponentIds.0']);
});

it('blocks composite items from being added to the composition of another composite product', function (): void {
    $material = Material::query()->create([
        'name' => 'Kit principal',
        'type' => 'composite',
    ]);

    $nestedComposite = Material::query()->create([
        'name' => 'Kit secundario',
        'type' => 'composite',
    ]);

    Livewire::test('pages.app.director.inventory.manage-components-modal', ['materialId' => $material->id])
        ->call('openModal', $material->id)
        ->set('selectedComponentIds', [$nestedComposite->id])
        ->set('componentQuantities.'.$nestedComposite->id, 1)
        ->call('saveComposition')
        ->assertHasErrors(['selectedComponentIds.0']);

    expect(MaterialComponent::query()
        ->where('parent_material_id', $material->id)
        ->where('component_material_id', $nestedComposite->id)
        ->exists())->toBeFalse();
});

it('can inactivate a material without deleting it', function (): void {
    $material = Material::query()->create([
        'name' => 'Livro do aluno',
        'is_active' => true,
        'status' => 'active',
    ]);

    Livewire::test('pages.app.director.inventory.edit-modal', ['materialId' => $material->id])
        ->call('openModal', $material->id)
        ->call('toggleActive')
        ->assertDispatched('director-material-updated');

    expect($material->fresh()?->is_active)->toBeFalse();
    expect($material->fresh()?->status)->toBe('inactive');
});

it('preserves the active tab when toggling material status', function (): void {
    $material = Material::query()->create([
        'name' => 'Livro do professor',
        'is_active' => true,
        'status' => 'active',
    ]);

    Livewire::test('pages.app.director.inventory.edit-modal', ['materialId' => $material->id])
        ->call('openModal', $material->id, 'edit')
        ->assertSet('activeTab', 'edit')
        ->call('toggleActive')
        ->assertSet('activeTab', 'edit')
        ->assertDispatched('director-material-updated');

    expect($material->fresh()?->is_active)->toBeFalse();
    expect($material->fresh()?->status)->toBe('inactive');
});

it('deletes permanently a material that has never been used', function (): void {
    $material = Material::query()->create([
        'name' => 'Item lançado errado',
        'is_active' => true,
        'status' => 'active',
    ]);

    Livewire::test('pages.app.director.inventory.edit-modal', ['materialId' => $material->id])
        ->call('openModal', $material->id)
        ->call('confirmPermanentDelete')
        ->call('deletePermanently')
        ->assertDispatched('director-material-deleted');

    expect(Material::query()->find($material->id))->toBeNull();
});

it('blocks permanent deletion when the material already has operational use', function (): void {
    $material = Material::query()->create([
        'name' => 'Manual entregue',
        'is_active' => true,
        'status' => 'active',
    ]);

    $inventory = Inventory::query()->create([
        'name' => 'Estoque central',
        'kind' => 'central',
        'is_active' => true,
    ]);

    $inventory->materials()->attach($material->id, [
        'received_items' => 5,
        'current_quantity' => 2,
        'lost_items' => 0,
    ]);

    StockMovement::query()->create([
        'inventory_id' => $inventory->id,
        'material_id' => $material->id,
        'movement_type' => StockMovement::TYPE_ENTRY,
        'quantity' => 5,
        'balance_after' => 5,
    ]);

    Livewire::test('pages.app.director.inventory.edit-modal', ['materialId' => $material->id])
        ->call('openModal', $material->id)
        ->call('confirmPermanentDelete')
        ->call('deletePermanently')
        ->assertHasErrors(['delete_blocker_0']);

    expect(Material::query()->find($material->id))->not->toBeNull();
});
