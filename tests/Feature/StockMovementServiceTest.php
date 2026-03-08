<?php

use App\Exceptions\Inventory\InsufficientStockException;
use App\Models\Inventory;
use App\Models\Material;
use App\Models\MaterialComponent;
use App\Models\StockMovement;
use App\Models\User;
use App\Services\Inventory\StockMovementService;
use Illuminate\Support\Facades\DB;

it('increments balance and creates an entry movement', function (): void {
    $service = new StockMovementService;
    $inventory = Inventory::query()->create(['name' => 'Estoque Central', 'kind' => 'central']);
    $material = Material::query()->create(['name' => 'Apostila']);
    $actor = User::factory()->create();

    $movement = $service->addStock($inventory, $material, 8, $actor, notes: 'Reposicao inicial');

    expect($movement->movement_type)->toBe(StockMovement::TYPE_ENTRY);
    expect($movement->quantity)->toBe(8);
    expect($movement->balance_after)->toBe(8);
    expect($inventory->currentQuantityFor($material))->toBe(8);
    expect((int) DB::table('inventory_material')->where('inventory_id', $inventory->id)->where('material_id', $material->id)->value('received_items'))->toBe(8);
});

it('decrements balance and creates an exit movement', function (): void {
    $service = new StockMovementService;
    $inventory = Inventory::query()->create(['name' => 'Estoque Local']);
    $material = Material::query()->create(['name' => 'Manual']);

    $service->addStock($inventory, $material, 10);
    $movement = $service->removeStock($inventory, $material, 4, notes: 'Entrega para turma');

    expect($movement->movement_type)->toBe(StockMovement::TYPE_EXIT);
    expect($movement->quantity)->toBe(4);
    expect($movement->balance_after)->toBe(6);
    expect($inventory->currentQuantityFor($material))->toBe(6);
});

it('does not allow negative stock on exit', function (): void {
    $service = new StockMovementService;
    $inventory = Inventory::query()->create(['name' => 'Estoque Professor']);
    $material = Material::query()->create(['name' => 'Livro']);

    $service->addStock($inventory, $material, 2);
    $service->removeStock($inventory, $material, 3);
})->throws(InsufficientStockException::class);

it('creates coherent transfer movements with the same batch uuid', function (): void {
    $service = new StockMovementService;
    $sourceInventory = Inventory::query()->create(['name' => 'Central', 'kind' => 'central']);
    $destinationInventory = Inventory::query()->create(['name' => 'Professor Ana', 'kind' => 'teacher']);
    $material = Material::query()->create(['name' => 'Pasta']);

    $service->addStock($sourceInventory, $material, 12);

    $transfer = $service->transferStock($sourceInventory, $destinationInventory, $material, 5, notes: 'Envio para evento');

    expect($transfer['outgoing']->movement_type)->toBe(StockMovement::TYPE_TRANSFER_OUT);
    expect($transfer['incoming']->movement_type)->toBe(StockMovement::TYPE_TRANSFER_IN);
    expect($transfer['outgoing']->batch_uuid)->toBe($transfer['incoming']->batch_uuid);
    expect($transfer['batch_uuid'])->toBe($transfer['incoming']->batch_uuid);
    expect($sourceInventory->currentQuantityFor($material))->toBe(7);
    expect($destinationInventory->currentQuantityFor($material))->toBe(5);
});

it('removes a composite material and decrements its components with the same batch uuid', function (): void {
    $service = new StockMovementService;
    $inventory = Inventory::query()->create(['name' => 'Central', 'kind' => 'central']);
    $composite = Material::query()->create(['name' => 'Kit Aluno', 'type' => 'composite']);
    $booklet = Material::query()->create(['name' => 'Apostila']);
    $badge = Material::query()->create(['name' => 'Cracha']);

    MaterialComponent::query()->create([
        'parent_material_id' => $composite->id,
        'component_material_id' => $booklet->id,
        'quantity' => 2,
    ]);

    MaterialComponent::query()->create([
        'parent_material_id' => $composite->id,
        'component_material_id' => $badge->id,
        'quantity' => 1,
    ]);

    $service->addStock($inventory, $composite, 4);
    $service->addStock($inventory, $booklet, 20);
    $service->addStock($inventory, $badge, 10);

    $movements = $service->removeCompositeMaterial($inventory, $composite, 3, notes: 'Consumo da turma');

    expect($movements)->toHaveCount(3);
    expect($movements->first()->movement_type)->toBe(StockMovement::TYPE_EXIT);
    expect($movements->skip(1)->pluck('movement_type')->all())->toBe([
        StockMovement::TYPE_KIT_COMPONENT_EXIT,
        StockMovement::TYPE_KIT_COMPONENT_EXIT,
    ]);
    expect($movements->pluck('batch_uuid')->unique()->count())->toBe(1);
    expect($inventory->currentQuantityFor($composite))->toBe(1);
    expect($inventory->currentQuantityFor($booklet))->toBe(14);
    expect($inventory->currentQuantityFor($badge))->toBe(7);
});

it('reuses the provided batch uuid in related movements', function (): void {
    $service = new StockMovementService;
    $sourceInventory = Inventory::query()->create(['name' => 'Central', 'kind' => 'central']);
    $destinationInventory = Inventory::query()->create(['name' => 'Professor Beto', 'kind' => 'teacher']);
    $material = Material::query()->create(['name' => 'Guia']);
    $batchUuid = '2f09310f-4f31-4dc5-92ff-5f9016af0001';

    $service->addStock($sourceInventory, $material, 6);
    $transfer = $service->transferStock($sourceInventory, $destinationInventory, $material, 2, batchUuid: $batchUuid);

    expect($transfer['outgoing']->batch_uuid)->toBe($batchUuid);
    expect($transfer['incoming']->batch_uuid)->toBe($batchUuid);
});
