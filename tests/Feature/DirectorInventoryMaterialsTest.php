<?php

use App\Models\Course;
use App\Models\Material;
use App\Models\MaterialComponent;
use App\Models\Ministry;
use App\Models\Supplier;
use Livewire\Livewire;

it('creates a simple material', function (): void {
    Livewire::test('pages.app.director.inventory.create-modal')
        ->call('openModal')
        ->set('name', 'Manual do participante')
        ->set('type', 'simple')
        ->set('status', 'active')
        ->set('price', '12,50')
        ->set('minimum_stock', 3)
        ->set('description', 'Material impresso.')
        ->call('save')
        ->assertDispatched('director-material-created');

    $material = Material::query()->where('name', 'Manual do participante')->first();

    expect($material)->not->toBeNull();
    expect($material?->type)->toBe('simple');
    expect($material?->is_active)->toBeTrue();
    expect($material?->minimum_stock)->toBe(3);
});

it('creates a composite material', function (): void {
    Livewire::test('pages.app.director.inventory.create-modal')
        ->call('openModal')
        ->set('name', 'Kit do aluno')
        ->set('type', 'composite')
        ->set('status', 'active')
        ->set('minimum_stock', 1)
        ->call('save')
        ->assertDispatched('director-material-created');

    expect(Material::query()->where('name', 'Kit do aluno')->value('type'))->toBe('composite');
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
        ->set('component_material_id', $component->id)
        ->set('quantity', 2)
        ->call('addComponent')
        ->assertDispatched('director-material-components-updated');

    $materialComponent = MaterialComponent::query()
        ->where('parent_material_id', $material->id)
        ->where('component_material_id', $component->id)
        ->first();

    expect($materialComponent)->not->toBeNull();
    expect($materialComponent?->quantity)->toBe(2);

    Livewire::test('pages.app.director.inventory.manage-components-modal', ['materialId' => $material->id])
        ->call('openModal', $material->id)
        ->set('componentQuantities.'.$materialComponent->id, 5)
        ->call('updateComponentQuantity', $materialComponent->id)
        ->assertDispatched('director-material-components-updated');

    expect($materialComponent->fresh()?->quantity)->toBe(5);
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
        ->set('component_material_id', $material->id)
        ->set('quantity', 1)
        ->call('addComponent')
        ->assertHasErrors(['component_material_id']);

    MaterialComponent::query()->create([
        'parent_material_id' => $material->id,
        'component_material_id' => $component->id,
        'quantity' => 1,
    ]);

    Livewire::test('pages.app.director.inventory.manage-components-modal', ['materialId' => $material->id])
        ->call('openModal', $material->id)
        ->set('component_material_id', $component->id)
        ->set('quantity', 3)
        ->call('addComponent')
        ->assertHasErrors(['component_material_id']);
});
