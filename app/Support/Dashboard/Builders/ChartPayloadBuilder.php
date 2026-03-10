<?php

namespace App\Support\Dashboard\Builders;

use App\Support\Dashboard\Data\ChartData;
use App\Support\Dashboard\Data\ChartDatasetData;

class ChartPayloadBuilder
{
    /**
     * @param  array<int, ChartDatasetData>  $datasets
     * @param  array<string, mixed>  $options
     */
    public function make(
        string $id,
        string $title,
        string $type,
        array $labels,
        array $datasets,
        string $seriesType = 'category',
        array $options = [],
    ): ChartData {
        return new ChartData(
            id: $id,
            title: $title,
            type: $type,
            labels: $labels,
            datasets: $datasets,
            seriesType: $seriesType,
            options: $options,
        );
    }

    /**
     * @param  array<int, ChartDatasetData>  $datasets
     * @param  array<string, mixed>  $options
     */
    public function timeSeries(string $id, string $title, array $datasets, array $options = []): ChartData
    {
        return $this->make(
            id: $id,
            title: $title,
            type: 'line',
            labels: [],
            datasets: $datasets,
            seriesType: 'time',
            options: $options,
        );
    }

    /**
     * @param  array<int, string>  $labels
     * @param  array<int, ChartDatasetData>  $datasets
     * @param  array<string, mixed>  $options
     */
    public function bar(string $id, string $title, array $labels, array $datasets, array $options = []): ChartData
    {
        return $this->make($id, $title, 'bar', $labels, $datasets, 'category', $options);
    }

    /**
     * @param  array<int, string>  $labels
     * @param  array<int, ChartDatasetData>  $datasets
     * @param  array<string, mixed>  $options
     */
    public function doughnut(string $id, string $title, array $labels, array $datasets, array $options = []): ChartData
    {
        return $this->make($id, $title, 'doughnut', $labels, $datasets, 'category', $options);
    }
}
