<?php

namespace App\Support\Dashboard\Data;

readonly class RankingRowData
{
    public function __construct(
        public int $position,
        public string $label,
        public string|int|float $value,
        public ?string $context = null,
    ) {}

    /**
     * @return array{position: int, label: string, value: string|int|float, context: ?string}
     */
    public function toArray(): array
    {
        return [
            'position' => $this->position,
            'label' => $this->label,
            'value' => $this->value,
            'context' => $this->context,
        ];
    }
}
