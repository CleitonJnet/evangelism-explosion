<?php

use App\Models\Course;
use App\Models\Inventory;
use App\Models\Material;
use App\Models\MaterialComponent;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;

it('creates the inventory foundation schema', function (): void {
    expect(Schema::hasTable('material_components'))->toBeTrue();
    expect(Schema::hasTable('course_material'))->toBeTrue();
    expect(Schema::hasTable('course_study_material'))->toBeTrue();
    expect(Schema::hasTable('stock_movements'))->toBeTrue();

    expect(Schema::hasColumns('materials', ['type', 'minimum_stock', 'is_active']))->toBeTrue();
    expect(Schema::hasColumns('inventories', ['kind', 'user_id', 'is_active']))->toBeTrue();
    expect(Schema::hasColumns('material_components', ['parent_material_id', 'component_material_id', 'quantity']))->toBeTrue();
    expect(Schema::hasColumns('course_material', ['course_id', 'material_id']))->toBeTrue();
    expect(Schema::hasColumns('course_study_material', ['course_id', 'material_id']))->toBeTrue();
    expect(Schema::hasColumns('stock_movements', [
        'inventory_id',
        'material_id',
        'user_id',
        'training_id',
        'movement_type',
        'quantity',
        'balance_after',
        'batch_uuid',
        'notes',
        'reference_type',
        'reference_id',
    ]))->toBeTrue();
});

it('allows a composite material to have components', function (): void {
    $kit = Material::query()->create([
        'name' => 'Kit Lideranca',
        'type' => 'composite',
        'minimum_stock' => 2,
        'is_active' => true,
    ]);

    $manual = Material::query()->create([
        'name' => 'Manual do Aluno',
    ]);

    $badge = Material::query()->create([
        'name' => 'Cracha',
    ]);

    MaterialComponent::query()->create([
        'parent_material_id' => $kit->id,
        'component_material_id' => $manual->id,
        'quantity' => 1,
    ]);

    MaterialComponent::query()->create([
        'parent_material_id' => $kit->id,
        'component_material_id' => $badge->id,
        'quantity' => 2,
    ]);

    $kit->load(['components.componentMaterial', 'componentMaterials', 'parentCompositions']);

    expect($kit->components)->toHaveCount(2);
    expect($kit->components->pluck('quantity')->all())->toBe([1, 2]);
    expect($kit->componentMaterials->pluck('name')->all())->toBe(['Manual do Aluno', 'Cracha']);
    expect($manual->parentCompositions()->first()?->parent_material_id)->toBe($kit->id);
});

it('prevents duplicate component registration for the same composite material', function (): void {
    $kit = Material::query()->create([
        'name' => 'Kit Multiplicador',
        'type' => 'composite',
    ]);

    $component = Material::query()->create([
        'name' => 'Apostila',
    ]);

    MaterialComponent::query()->create([
        'parent_material_id' => $kit->id,
        'component_material_id' => $component->id,
        'quantity' => 1,
    ]);

    MaterialComponent::query()->create([
        'parent_material_id' => $kit->id,
        'component_material_id' => $component->id,
        'quantity' => 3,
    ]);
})->throws(QueryException::class);

it('allows a material to be linked to multiple courses', function (): void {
    $material = Material::query()->create([
        'name' => 'Livro Base',
    ]);

    $firstCourse = Course::factory()->create();
    $secondCourse = Course::factory()->create();

    $material->courses()->attach([$firstCourse->id, $secondCourse->id]);
    $material->load('courses');

    expect($material->courses)->toHaveCount(2);
    expect($firstCourse->materials()->pluck('materials.id')->all())->toContain($material->id);
    expect($secondCourse->materials()->pluck('materials.id')->all())->toContain($material->id);
});

it('keeps study materials separate from general course categorization', function (): void {
    $material = Material::query()->create([
        'name' => 'Kit oficial do aluno',
        'type' => 'composite',
    ]);

    $course = Course::factory()->create();

    $course->studyMaterials()->attach($material->id);

    expect($course->studyMaterials()->pluck('materials.id')->all())->toContain($material->id);
    expect($course->materials()->pluck('materials.id')->all())->not->toContain($material->id);
    expect($material->studyCourses()->pluck('courses.id')->all())->toContain($course->id);
});

it('exposes the main inventory stock relationships', function (): void {
    $user = User::factory()->create();

    $inventory = Inventory::query()->create([
        'name' => 'Estoque Central',
        'kind' => 'central',
        'user_id' => $user->id,
        'is_active' => true,
    ]);

    $material = Material::query()->create([
        'name' => 'Pasta',
    ]);

    $movement = StockMovement::query()->create([
        'inventory_id' => $inventory->id,
        'material_id' => $material->id,
        'user_id' => $user->id,
        'movement_type' => StockMovement::TYPE_ENTRY,
        'quantity' => 10,
        'balance_after' => 10,
        'notes' => 'Carga inicial',
    ]);

    $inventory->load(['responsibleUser', 'stockMovements']);
    $material->load('stockMovements');
    $user->load(['inventories', 'stockMovements']);

    expect($inventory->responsibleUser?->is($user))->toBeTrue();
    expect($inventory->stockMovements->first()?->is($movement))->toBeTrue();
    expect($material->stockMovements->first()?->is($movement))->toBeTrue();
    expect($user->inventories->first()?->is($inventory))->toBeTrue();
    expect($user->stockMovements->first()?->is($movement))->toBeTrue();
});
