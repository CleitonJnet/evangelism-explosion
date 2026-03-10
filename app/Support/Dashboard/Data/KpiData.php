<?php

namespace App\Support\Dashboard\Data;

readonly class KpiData
{
    public function __construct(
        public string $key,
        public string $label,
        public string|int|float $value,
        public ?string $description = null,
        public ?string $trend = null,
    ) {}

    /**
     * @return array{key: string, label: string, value: string|int|float, description: ?string, trend: ?string}
     */
    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'label' => $this->label,
            'value' => $this->value,
            'description' => $this->description,
            'trend' => $this->trend,
        ];
    }
}
