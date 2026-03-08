<?php

use App\Livewire\Pages\App\Director\Training\View as TrainingView;
use App\Models\Inventory;
use App\Models\Material;
use App\Models\MaterialComponent;
use App\Models\Ministry;
use App\Models\Role;
use App\Models\StockMovement;
use App\Models\Training;
use App\Models\User;
use Livewire\Livewire;

function createDirectorUser(): User
{
    $director = User::factory()->create();
    $directorRole = Role::query()->firstOrCreate(['name' => 'Director']);
    $director->roles()->syncWithoutDetaching([$directorRole->id]);

    return $director;
}

function createTeacherWithInventory(): array
{
    $teacher = User::factory()->create();
    $teacherRole = Role::query()->firstOrCreate(['name' => 'Teacher']);
    $teacher->roles()->syncWithoutDetaching([$teacherRole->id]);

    $inventory = Inventory::query()->create([
        'name' => 'Estoque do Professor',
        'kind' => 'teacher',
        'user_id' => $teacher->id,
        'is_active' => true,
    ]);

    return [$teacher, $inventory];
}

it('delivers a composite kit to a registered participant and marks training_user.kit', function (): void {
    $director = createDirectorUser();
    [$teacher, $inventory] = createTeacherWithInventory();
    $course = \App\Models\Course::factory()->create([
        'ministry_id' => Ministry::query()->create(['initials' => 'EE', 'name' => 'Evangelismo'])->id,
    ]);
    $training = Training::factory()->create([
        'course_id' => $course->id,
        'teacher_id' => $teacher->id,
    ]);
    $participant = User::factory()->create();
    $training->students()->attach($participant->id, ['kit' => 0, 'payment' => 1, 'accredited' => 0]);

    $kit = Material::query()->create(['name' => 'Kit do aluno', 'type' => 'composite']);
    $manual = Material::query()->create(['name' => 'Manual do aluno']);
    $course->materials()->attach([$kit->id, $manual->id]);

    MaterialComponent::query()->create([
        'parent_material_id' => $kit->id,
        'component_material_id' => $manual->id,
        'quantity' => 2,
    ]);

    app(\App\Services\Inventory\StockMovementService::class)->addStock($inventory, $kit, 5, $director);
    app(\App\Services\Inventory\StockMovementService::class)->addStock($inventory, $manual, 20, $director);

    Livewire::actingAs($director)
        ->test('pages.app.director.training.deliver-material-modal', ['trainingId' => $training->id])
        ->call('openModal', $training->id, $participant->id)
        ->set('inventory_id', $inventory->id)
        ->set('material_id', $kit->id)
        ->set('participant_id', $participant->id)
        ->set('quantity', 2)
        ->set('notes', 'Entrega no credenciamento')
        ->call('save')
        ->assertDispatched('training-material-delivered');

    expect($inventory->currentQuantityFor($kit))->toBe(3);
    expect($inventory->currentQuantityFor($manual))->toBe(16);

    $pivot = $training->students()->whereKey($participant->id)->firstOrFail()->pivot;

    expect((bool) $pivot->kit)->toBeTrue();
    expect((bool) $pivot->payment)->toBeTrue();

    $movements = StockMovement::query()
        ->where('training_id', $training->id)
        ->orderBy('id')
        ->get();

    expect($movements)->toHaveCount(2);
    expect($movements->pluck('movement_type')->all())->toBe([
        StockMovement::TYPE_EXIT,
        StockMovement::TYPE_KIT_COMPONENT_EXIT,
    ]);
    expect($movements->pluck('batch_uuid')->unique())->toHaveCount(1);
});

it('registers manual delivery for a non-linked participant without changing training_user', function (): void {
    $director = createDirectorUser();
    [$teacher, $inventory] = createTeacherWithInventory();
    $course = \App\Models\Course::factory()->create([
        'ministry_id' => Ministry::query()->create(['initials' => 'CT', 'name' => 'Capacitação'])->id,
    ]);
    $training = Training::factory()->create([
        'course_id' => $course->id,
        'teacher_id' => $teacher->id,
    ]);
    $material = Material::query()->create(['name' => 'Apostila impressa']);
    $course->materials()->attach($material->id);

    app(\App\Services\Inventory\StockMovementService::class)->addStock($inventory, $material, 8, $director);

    Livewire::actingAs($director)
        ->test('pages.app.director.training.deliver-material-modal', ['trainingId' => $training->id])
        ->call('openModal', $training->id)
        ->set('inventory_id', $inventory->id)
        ->set('material_id', $material->id)
        ->set('participant_note', 'Visitante sem cadastro')
        ->set('quantity', 1)
        ->set('notes', 'Entrega avulsa')
        ->call('save')
        ->assertDispatched('training-material-delivered');

    expect($inventory->currentQuantityFor($material))->toBe(7);
    expect($training->students()->count())->toBe(0);

    $movement = StockMovement::query()->where('training_id', $training->id)->firstOrFail();

    expect($movement->notes)->toContain('Visitante sem cadastro');
    expect($movement->training_id)->toBe($training->id);
});

it('does not change financial confirmation when delivering simple material to a participant', function (): void {
    $director = createDirectorUser();
    [$teacher, $inventory] = createTeacherWithInventory();
    $course = \App\Models\Course::factory()->create([
        'ministry_id' => Ministry::query()->create(['initials' => 'LC', 'name' => 'Liderança'])->id,
    ]);
    $training = Training::factory()->create([
        'course_id' => $course->id,
        'teacher_id' => $teacher->id,
    ]);
    $participant = User::factory()->create();
    $training->students()->attach($participant->id, ['kit' => 0, 'payment' => 1, 'accredited' => 0]);

    $material = Material::query()->create(['name' => 'Crachá']);
    $course->materials()->attach($material->id);
    app(\App\Services\Inventory\StockMovementService::class)->addStock($inventory, $material, 4, $director);

    Livewire::actingAs($director)
        ->test('pages.app.director.training.deliver-material-modal', ['trainingId' => $training->id])
        ->call('openModal', $training->id, $participant->id)
        ->set('inventory_id', $inventory->id)
        ->set('material_id', $material->id)
        ->set('participant_id', $participant->id)
        ->set('quantity', 1)
        ->call('save')
        ->assertDispatched('training-material-delivered');

    $pivot = $training->students()->whereKey($participant->id)->firstOrFail()->pivot;

    expect((bool) $pivot->payment)->toBeTrue();
    expect((bool) $pivot->kit)->toBeFalse();
});

it('stores the training id on stock movements created by delivery', function (): void {
    $director = createDirectorUser();
    [$teacher, $inventory] = createTeacherWithInventory();
    $course = \App\Models\Course::factory()->create([
        'ministry_id' => Ministry::query()->create(['initials' => 'MS', 'name' => 'Missões'])->id,
    ]);
    $training = Training::factory()->create([
        'course_id' => $course->id,
        'teacher_id' => $teacher->id,
    ]);
    $material = Material::query()->create(['name' => 'Bloco de notas']);
    $course->materials()->attach($material->id);

    app(\App\Services\Inventory\StockMovementService::class)->addStock($inventory, $material, 3, $director);

    Livewire::actingAs($director)
        ->test('pages.app.director.training.deliver-material-modal', ['trainingId' => $training->id])
        ->call('openModal', $training->id)
        ->set('inventory_id', $inventory->id)
        ->set('material_id', $material->id)
        ->set('participant_note', 'Apoio da equipe')
        ->set('quantity', 2)
        ->call('save')
        ->assertDispatched('training-material-delivered');

    $movement = StockMovement::query()->where('training_id', $training->id)->first();

    expect($movement)->not->toBeNull();
    expect($movement?->movement_type)->toBe(StockMovement::TYPE_EXIT);
});

it('shows linked materials and movement history on the training detail page', function (): void {
    $director = createDirectorUser();
    [$teacher, $inventory] = createTeacherWithInventory();
    $course = \App\Models\Course::factory()->create([
        'name' => 'Clínica Vida',
        'ministry_id' => Ministry::query()->create(['initials' => 'CV', 'name' => 'Cuidado'])->id,
    ]);
    $training = Training::factory()->create([
        'course_id' => $course->id,
        'teacher_id' => $teacher->id,
    ]);
    $material = Material::query()->create(['name' => 'Kit recomendado', 'type' => 'composite']);
    $component = Material::query()->create(['name' => 'Manual complementar']);
    $course->materials()->attach([$material->id, $component->id]);
    MaterialComponent::query()->create([
        'parent_material_id' => $material->id,
        'component_material_id' => $component->id,
        'quantity' => 1,
    ]);

    app(\App\Services\Inventory\StockMovementService::class)->addStock($inventory, $material, 2, $director);
    app(\App\Services\Inventory\StockMovementService::class)->addStock($inventory, $component, 2, $director);
    app(\App\Services\Training\TrainingMaterialDeliveryService::class)->deliver(
        training: $training,
        inventory: $inventory,
        material: $material,
        quantity: 1,
        actor: $director,
        participantLabel: 'Aluno extra',
    );

    Livewire::actingAs($director)
        ->test(TrainingView::class, ['training' => $training])
        ->assertSee('Apoio operacional de materiais')
        ->assertSee('Kit recomendado')
        ->assertSee('Resumo de consumo')
        ->assertSee('Histórico de consumo auditável')
        ->assertSee((string) $training->id);
});
