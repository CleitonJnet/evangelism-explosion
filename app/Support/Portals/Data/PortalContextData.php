<?php

namespace App\Support\Portals\Data;

use App\Support\Portals\Enums\Portal;

readonly class PortalContextData
{
    /**
     * @param  array<int, string>  $roleHints
     * @param  array<int, string>  $focusAreas
     */
    public function __construct(
        public Portal $portal,
        public string $headline,
        public string $description,
        public array $roleHints,
        public array $focusAreas,
    ) {}

    /**
     * @return array{
     *     portal: string,
     *     headline: string,
     *     description: string,
     *     roleHints: array<int, string>,
     *     focusAreas: array<int, string>
     * }
     */
    public function toArray(): array
    {
        return [
            'portal' => $this->portal->value,
            'headline' => $this->headline,
            'description' => $this->description,
            'roleHints' => $this->roleHints,
            'focusAreas' => $this->focusAreas,
        ];
    }
}
