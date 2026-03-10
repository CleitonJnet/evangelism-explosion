<?php

namespace App\Services\Inventory;

use App\Exceptions\Inventory\InsufficientStockException;
use App\Exceptions\Inventory\InvalidCompositeMaterialException;
use App\Models\Inventory;
use App\Models\Material;
use App\Models\StockMovement;
use App\Models\Training;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class StockMovementService
{
    public function addStock(
        Inventory $inventory,
        Material $material,
        int $quantity,
        ?User $actor = null,
        ?Training $training = null,
        ?string $notes = null,
        ?string $batchUuid = null,
        ?Model $reference = null,
    ): StockMovement {
        $this->ensurePositiveQuantity($quantity);

        return DB::transaction(function () use ($inventory, $material, $quantity, $actor, $training, $notes, $batchUuid, $reference): StockMovement {
            $batchUuid ??= (string) Str::uuid();
            $balance = $this->changeInventoryBalance($inventory, $material, $quantity, 0, $quantity);

            return $this->createMovement(
                inventory: $inventory,
                material: $material,
                movementType: StockMovement::TYPE_ENTRY,
                quantity: $quantity,
                balanceAfter: $balance,
                actor: $actor,
                training: $training,
                notes: $notes,
                batchUuid: $batchUuid,
                reference: $reference,
            );
        });
    }

    public function removeStock(
        Inventory $inventory,
        Material $material,
        int $quantity,
        ?User $actor = null,
        ?Training $training = null,
        ?string $notes = null,
        ?string $batchUuid = null,
        ?Model $reference = null,
    ): StockMovement {
        if ($material->isComposite()) {
            throw InvalidCompositeMaterialException::notComposite($material);
        }

        $this->ensurePositiveQuantity($quantity);

        return DB::transaction(function () use ($inventory, $material, $quantity, $actor, $training, $notes, $batchUuid, $reference): StockMovement {
            $batchUuid ??= (string) Str::uuid();
            $balance = $this->decrementInventoryBalance($inventory, $material, $quantity);

            return $this->createMovement(
                inventory: $inventory,
                material: $material,
                movementType: StockMovement::TYPE_EXIT,
                quantity: $quantity,
                balanceAfter: $balance,
                actor: $actor,
                training: $training,
                notes: $notes,
                batchUuid: $batchUuid,
                reference: $reference,
            );
        });
    }

    public function adjustStock(
        Inventory $inventory,
        Material $material,
        int $targetQuantity,
        ?User $actor = null,
        ?Training $training = null,
        ?string $notes = null,
        ?string $batchUuid = null,
        ?Model $reference = null,
    ): ?StockMovement {
        if ($targetQuantity < 0) {
            throw new InvalidArgumentException('O saldo ajustado não pode ser negativo.');
        }

        return DB::transaction(function () use ($inventory, $material, $targetQuantity, $actor, $training, $notes, $batchUuid, $reference): ?StockMovement {
            $currentQuantity = $this->currentQuantity($inventory, $material);

            if ($currentQuantity === $targetQuantity) {
                return null;
            }

            $batchUuid ??= (string) Str::uuid();
            $difference = $targetQuantity - $currentQuantity;
            $balance = $this->changeInventoryBalance($inventory, $material, $difference);

            return $this->createMovement(
                inventory: $inventory,
                material: $material,
                movementType: StockMovement::TYPE_ADJUSTMENT,
                quantity: abs($difference),
                balanceAfter: $balance,
                actor: $actor,
                training: $training,
                notes: $notes,
                batchUuid: $batchUuid,
                reference: $reference,
            );
        });
    }

    public function registerLoss(
        Inventory $inventory,
        Material $material,
        int $quantity,
        ?User $actor = null,
        ?Training $training = null,
        ?string $notes = null,
        ?string $batchUuid = null,
        ?Model $reference = null,
    ): StockMovement {
        $this->ensurePositiveQuantity($quantity);

        return DB::transaction(function () use ($inventory, $material, $quantity, $actor, $training, $notes, $batchUuid, $reference): StockMovement {
            $batchUuid ??= (string) Str::uuid();
            $balance = $this->decrementInventoryBalance($inventory, $material, $quantity, $quantity);

            return $this->createMovement(
                inventory: $inventory,
                material: $material,
                movementType: StockMovement::TYPE_LOSS,
                quantity: $quantity,
                balanceAfter: $balance,
                actor: $actor,
                training: $training,
                notes: $notes,
                batchUuid: $batchUuid,
                reference: $reference,
            );
        });
    }

    /**
     * @return array{batch_uuid: string, outgoing: StockMovement, incoming: StockMovement}
     */
    public function transferStock(
        Inventory $sourceInventory,
        Inventory $destinationInventory,
        Material $material,
        int $quantity,
        ?User $actor = null,
        ?Training $training = null,
        ?string $notes = null,
        ?string $batchUuid = null,
        ?Model $reference = null,
    ): array {
        if ($sourceInventory->is($destinationInventory)) {
            throw new InvalidArgumentException('A transferência exige estoques distintos.');
        }

        $this->ensurePositiveQuantity($quantity);

        return DB::transaction(function () use ($sourceInventory, $destinationInventory, $material, $quantity, $actor, $training, $notes, $batchUuid, $reference): array {
            $batchUuid ??= (string) Str::uuid();

            $sourceBalance = $this->decrementInventoryBalance($sourceInventory, $material, $quantity);
            $destinationBalance = $this->changeInventoryBalance($destinationInventory, $material, $quantity, 0, $quantity);

            return [
                'batch_uuid' => $batchUuid,
                'outgoing' => $this->createMovement(
                    inventory: $sourceInventory,
                    material: $material,
                    movementType: StockMovement::TYPE_TRANSFER_OUT,
                    quantity: $quantity,
                    balanceAfter: $sourceBalance,
                    actor: $actor,
                    training: $training,
                    notes: $notes,
                    batchUuid: $batchUuid,
                    reference: $reference,
                ),
                'incoming' => $this->createMovement(
                    inventory: $destinationInventory,
                    material: $material,
                    movementType: StockMovement::TYPE_TRANSFER_IN,
                    quantity: $quantity,
                    balanceAfter: $destinationBalance,
                    actor: $actor,
                    training: $training,
                    notes: $notes,
                    batchUuid: $batchUuid,
                    reference: $reference,
                ),
            ];
        });
    }

    /**
     * @return Collection<int, StockMovement>
     */
    public function removeCompositeMaterial(
        Inventory $inventory,
        Material $material,
        int $quantity,
        ?User $actor = null,
        ?Training $training = null,
        ?string $notes = null,
        ?string $batchUuid = null,
        ?Model $reference = null,
        bool $allowDynamicComposition = false,
    ): Collection {
        if (! $material->isComposite()) {
            throw InvalidCompositeMaterialException::notComposite($material);
        }

        $this->ensurePositiveQuantity($quantity);

        return DB::transaction(function () use ($inventory, $material, $quantity, $actor, $training, $notes, $batchUuid, $reference, $allowDynamicComposition): Collection {
            $components = $material->components()->with('componentMaterial')->lockForUpdate()->get();

            if ($components->isEmpty()) {
                throw InvalidCompositeMaterialException::withoutComponents($material);
            }

            $batchUuid ??= (string) Str::uuid();
            $movements = new Collection;
            $parentBalance = 0;

            if (! $allowDynamicComposition || $this->currentQuantity($inventory, $material) > 0) {
                $parentBalance = $this->decrementInventoryBalance($inventory, $material, $quantity);
            }

            $movements->push($this->createMovement(
                inventory: $inventory,
                material: $material,
                movementType: StockMovement::TYPE_EXIT,
                quantity: $quantity,
                balanceAfter: $parentBalance,
                actor: $actor,
                training: $training,
                notes: $notes,
                batchUuid: $batchUuid,
                reference: $reference,
            ));

            foreach ($components as $component) {
                $componentMaterial = $component->componentMaterial;

                if (! $componentMaterial) {
                    continue;
                }

                $componentQuantity = $component->quantity * $quantity;
                $componentBalance = $this->decrementInventoryBalance($inventory, $componentMaterial, $componentQuantity);

                $movements->push($this->createMovement(
                    inventory: $inventory,
                    material: $componentMaterial,
                    movementType: StockMovement::TYPE_KIT_COMPONENT_EXIT,
                    quantity: $componentQuantity,
                    balanceAfter: $componentBalance,
                    actor: $actor,
                    training: $training,
                    notes: $notes,
                    batchUuid: $batchUuid,
                    reference: $reference,
                ));
            }

            return $movements;
        });
    }

    private function ensurePositiveQuantity(int $quantity): void
    {
        if ($quantity <= 0) {
            throw new InvalidArgumentException('A quantidade deve ser maior que zero.');
        }
    }

    private function currentQuantity(Inventory $inventory, Material $material): int
    {
        return (int) DB::table('inventory_material')
            ->where('inventory_id', $inventory->id)
            ->where('material_id', $material->id)
            ->lockForUpdate()
            ->value('current_quantity');
    }

    private function decrementInventoryBalance(
        Inventory $inventory,
        Material $material,
        int $quantity,
        int $lostItems = 0,
    ): int {
        $currentQuantity = $this->currentQuantity($inventory, $material);

        if ($currentQuantity < $quantity) {
            throw InsufficientStockException::forMaterial($inventory, $material, $quantity, $currentQuantity);
        }

        return $this->changeInventoryBalance($inventory, $material, -$quantity, $lostItems);
    }

    private function changeInventoryBalance(
        Inventory $inventory,
        Material $material,
        int $quantityDelta,
        int $lostItemsDelta = 0,
        int $receivedItemsDelta = 0,
    ): int {
        $this->ensureInventoryMaterialRowExists($inventory, $material);

        $row = DB::table('inventory_material')
            ->where('inventory_id', $inventory->id)
            ->where('material_id', $material->id)
            ->lockForUpdate()
            ->first();

        $currentQuantity = (int) ($row->current_quantity ?? 0);
        $receivedItems = (int) ($row->received_items ?? 0);
        $lostItems = (int) ($row->lost_items ?? 0);
        $newBalance = $currentQuantity + $quantityDelta;

        if ($newBalance < 0) {
            throw InsufficientStockException::forMaterial($inventory, $material, abs($quantityDelta), $currentQuantity);
        }

        DB::table('inventory_material')
            ->where('inventory_id', $inventory->id)
            ->where('material_id', $material->id)
            ->update([
                'current_quantity' => $newBalance,
                'received_items' => $receivedItems + $receivedItemsDelta,
                'lost_items' => $lostItems + $lostItemsDelta,
            ]);

        return $newBalance;
    }

    private function ensureInventoryMaterialRowExists(Inventory $inventory, Material $material): void
    {
        $exists = DB::table('inventory_material')
            ->where('inventory_id', $inventory->id)
            ->where('material_id', $material->id)
            ->exists();

        if ($exists) {
            return;
        }

        DB::table('inventory_material')->insert([
            'inventory_id' => $inventory->id,
            'material_id' => $material->id,
            'received_items' => 0,
            'current_quantity' => 0,
            'lost_items' => 0,
        ]);
    }

    private function createMovement(
        Inventory $inventory,
        Material $material,
        string $movementType,
        int $quantity,
        int $balanceAfter,
        ?User $actor,
        ?Training $training,
        ?string $notes,
        string $batchUuid,
        ?Model $reference,
    ): StockMovement {
        return StockMovement::query()->create([
            'inventory_id' => $inventory->id,
            'material_id' => $material->id,
            'user_id' => $actor?->id,
            'training_id' => $training?->id,
            'movement_type' => $movementType,
            'quantity' => $quantity,
            'balance_after' => $balanceAfter,
            'batch_uuid' => $batchUuid,
            'notes' => $notes,
            'reference_type' => $reference?->getMorphClass(),
            'reference_id' => $reference?->getKey(),
        ]);
    }
}
