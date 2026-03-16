<?php

namespace App\Support\Portals\Data;

readonly class PortalMenuItemData
{
    public function __construct(
        public string $label,
        public string $route,
        public string $icon,
        public ?string $description = null,
    ) {}

    /**
     * @return array{label: string, route: string, icon: string, description: ?string}
     */
    public function toArray(): array
    {
        return [
            'label' => $this->label,
            'route' => $this->route,
            'icon' => $this->icon,
            'description' => $this->description,
        ];
    }
}
