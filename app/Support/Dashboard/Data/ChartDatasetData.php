<?php

namespace App\Support\Dashboard\Data;

readonly class ChartDatasetData
{
    /**
     * @param  array<int, string|int|float|array{x: string, y: int|float}>  $data
     */
    public function __construct(
        public string $label,
        public array $data,
        public string|array|null $backgroundColor = null,
        public string|array|null $borderColor = null,
        public ?string $type = null,
        public bool $fill = false,
        public bool $hidden = false,
    ) {}

    /**
     * @return array{
     *     label: string,
     *     data: array<int, string|int|float|array{x: string, y: int|float}>,
     *     backgroundColor: string|array<int, string>|null,
     *     borderColor: string|array<int, string>|null,
     *     type: ?string,
     *     fill: bool,
     *     hidden: bool
     * }
     */
    public function toArray(): array
    {
        return [
            'label' => $this->label,
            'data' => $this->data,
            'backgroundColor' => $this->backgroundColor,
            'borderColor' => $this->borderColor,
            'type' => $this->type,
            'fill' => $this->fill,
            'hidden' => $this->hidden,
        ];
    }
}
