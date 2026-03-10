<?php

namespace App\Support\Dashboard\Data;

readonly class TimeSeriesPointData
{
    public function __construct(
        public string $x,
        public int|float $y,
    ) {}

    /**
     * @return array{x: string, y: int|float}
     */
    public function toArray(): array
    {
        return [
            'x' => $this->x,
            'y' => $this->y,
        ];
    }
}
