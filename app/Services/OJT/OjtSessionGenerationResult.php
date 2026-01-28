<?php

namespace App\Services\OJT;

use App\Models\OjtSession;
use Illuminate\Support\Collection;

class OjtSessionGenerationResult
{
    /**
     * @param  Collection<int, OjtSession>  $created
     * @param  Collection<int, OjtSession>  $canceled
     */
    public function __construct(
        public Collection $created,
        public Collection $canceled,
    ) {}
}
