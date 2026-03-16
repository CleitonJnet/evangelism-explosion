<?php

namespace App\Support\Portals\Data;

readonly class PortalMenuSectionData
{
    /**
     * @param  array<int, PortalMenuItemData>  $items
     */
    public function __construct(
        public string $title,
        public array $items,
    ) {}

    /**
     * @return array{title: string, items: array<int, array{label: string, route: string, icon: string, description: ?string}>}
     */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'items' => array_map(
                static fn (PortalMenuItemData $item): array => $item->toArray(),
                $this->items,
            ),
        ];
    }
}
