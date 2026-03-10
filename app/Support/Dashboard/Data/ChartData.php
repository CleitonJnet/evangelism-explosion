<?php

namespace App\Support\Dashboard\Data;

readonly class ChartData
{
    /**
     * @param  array<int, string>  $labels
     * @param  array<int, ChartDatasetData>  $datasets
     * @param  array<string, mixed>  $options
     */
    public function __construct(
        public string $id,
        public string $title,
        public string $type,
        public array $labels,
        public array $datasets,
        public string $seriesType = 'category',
        public int $height = 320,
        public array $options = [],
    ) {}

    /**
     * @return array{
     *     id: string,
     *     title: string,
     *     type: string,
     *     labels: array<int, string>,
     *     datasets: array<int, array{
     *         label: string,
     *         data: array<int, string|int|float|array{x: string, y: int|float}>,
     *         backgroundColor: string|array<int, string>|null,
     *         borderColor: string|array<int, string>|null,
     *         type: ?string,
     *         fill: bool
     *     }>,
     *     seriesType: string,
     *     height: int,
     *     options: array<string, mixed>
     * }
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'type' => $this->type,
            'labels' => $this->labels,
            'datasets' => array_map(
                static fn (ChartDatasetData $dataset): array => $dataset->toArray(),
                $this->datasets,
            ),
            'seriesType' => $this->seriesType,
            'height' => $this->height,
            'options' => $this->options,
        ];
    }
}
