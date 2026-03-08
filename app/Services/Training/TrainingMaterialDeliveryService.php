<?php

namespace App\Services\Training;

use App\Models\Inventory;
use App\Models\Material;
use App\Models\StockMovement;
use App\Models\Training;
use App\Models\User;
use App\Services\Inventory\StockMovementService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class TrainingMaterialDeliveryService
{
    public function __construct(
        protected StockMovementService $stockMovementService,
    ) {}

    /**
     * @return Collection<int, StockMovement>
     */
    public function deliver(
        Training $training,
        Inventory $inventory,
        Material $material,
        int $quantity,
        ?User $actor = null,
        ?User $participant = null,
        ?string $participantLabel = null,
        ?string $notes = null,
    ): Collection {
        return DB::transaction(function () use ($training, $inventory, $material, $quantity, $actor, $participant, $participantLabel, $notes): Collection {
            $notes = $this->buildNotes($participant, $participantLabel, $notes);

            $movements = $material->isComposite()
                ? $this->stockMovementService->removeCompositeMaterial(
                    inventory: $inventory,
                    material: $material,
                    quantity: $quantity,
                    actor: $actor,
                    training: $training,
                    notes: $notes,
                    reference: $participant,
                )
                : new Collection([
                    $this->stockMovementService->removeStock(
                        inventory: $inventory,
                        material: $material,
                        quantity: $quantity,
                        actor: $actor,
                        training: $training,
                        notes: $notes,
                        reference: $participant,
                    ),
                ]);

            if ($participant !== null && $material->isComposite()) {
                $training->students()->updateExistingPivot($participant->id, ['kit' => true]);
            }

            return $movements;
        });
    }

    private function buildNotes(?User $participant, ?string $participantLabel, ?string $notes): ?string
    {
        $segments = [];

        if ($participant !== null) {
            $segments[] = 'Participante: '.$participant->name;
        } elseif (filled($participantLabel)) {
            $segments[] = 'Participante avulso: '.trim((string) $participantLabel);
        }

        if (filled($notes)) {
            $segments[] = trim((string) $notes);
        }

        if ($segments === []) {
            return null;
        }

        return implode(' | ', $segments);
    }
}
