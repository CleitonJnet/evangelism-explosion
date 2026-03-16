<?php

namespace App\Support\Portals\Data;

use App\Support\Portals\Enums\Portal;

readonly class ResolvedPortalData
{
    public function __construct(
        public Portal $portal,
        public string $key,
        public string $label,
        public string $icon,
        public string $entryRoute,
        public string $description,
        public bool $isSuggestedDefault = false,
    ) {}

    /**
     * @return array{
     *     portal: string,
     *     key: string,
     *     label: string,
     *     icon: string,
     *     entryRoute: string,
     *     description: string,
     *     isSuggestedDefault: bool
     * }
     */
    public function toArray(): array
    {
        return [
            'portal' => $this->portal->value,
            'key' => $this->key,
            'label' => $this->label,
            'icon' => $this->icon,
            'entryRoute' => $this->entryRoute,
            'description' => $this->description,
            'isSuggestedDefault' => $this->isSuggestedDefault,
        ];
    }
}
