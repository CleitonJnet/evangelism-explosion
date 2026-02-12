<?php

namespace App\Services\Schedule;

use Illuminate\Support\Collection;

class GenerationResult
{
    /**
     * @param  Collection<int, mixed>  $items
     * @param  Collection<int, mixed>  $unallocated
     */
    public function __construct(
        public Collection $items,
        public Collection $unallocated,
    ) {}
}
