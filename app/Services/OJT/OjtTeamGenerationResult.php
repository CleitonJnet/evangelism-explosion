<?php

namespace App\Services\OJT;

use App\Models\OjtTeam;
use Illuminate\Support\Collection;

class OjtTeamGenerationResult
{
    /**
     * @param  Collection<int, OjtTeam>  $created
     * @param  array<int, array{type: string, message: string, session_id: int|null}>  $warnings
     */
    public function __construct(
        public Collection $created,
        public array $warnings,
    ) {}
}
