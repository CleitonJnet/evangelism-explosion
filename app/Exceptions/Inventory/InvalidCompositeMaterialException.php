<?php

namespace App\Exceptions\Inventory;

use App\Models\Material;
use RuntimeException;

class InvalidCompositeMaterialException extends RuntimeException
{
    public static function notComposite(Material $material): self
    {
        return new self(sprintf('O material "%s" não é composto.', $material->name));
    }

    public static function withoutComponents(Material $material): self
    {
        return new self(sprintf('O material composto "%s" não possui componentes cadastrados.', $material->name));
    }
}
