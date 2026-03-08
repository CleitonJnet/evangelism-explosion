<?php

namespace App\Exceptions\Inventory;

use App\Models\Inventory;
use App\Models\Material;
use RuntimeException;

class InsufficientStockException extends RuntimeException
{
    public static function forMaterial(Inventory $inventory, Material $material, int $requested, int $available): self
    {
        return new self(sprintf(
            'Saldo insuficiente para o material "%s" no estoque "%s": solicitado %d, disponível %d.',
            $material->name,
            $inventory->name,
            $requested,
            $available,
        ));
    }
}
