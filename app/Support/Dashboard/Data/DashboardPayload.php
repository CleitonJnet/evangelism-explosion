<?php

namespace App\Support\Dashboard\Data;

readonly class DashboardPayload
{
    /**
     * @param  array<int, KpiData>  $kpis
     * @param  array<int, ChartData>  $charts
     * @param  array<int, RankingTableData>  $tables
     */
    public function __construct(
        public string $period,
        public array $kpis,
        public array $charts,
        public array $tables,
    ) {}

    /**
     * @return array{
     *     period: string,
     *     kpis: array<int, array{key: string, label: string, value: string|int|float, description: ?string, trend: ?string}>,
     *     charts: array<int, array{
     *         id: string,
     *         title: string,
     *         type: string,
     *         labels: array<int, string>,
     *         datasets: array<int, array{
     *             label: string,
     *             data: array<int, string|int|float|array{x: string, y: int|float}>,
     *             backgroundColor: string|array<int, string>|null,
     *             borderColor: string|array<int, string>|null,
     *             type: ?string,
     *             fill: bool
     *         }>,
     *         seriesType: string,
     *         height: int,
     *         options: array<string, mixed>
     *     }>,
     *     tables: array<int, array{
     *         id: string,
     *         title: string,
     *         columns: array<int, string>,
     *         rows: array<int, array{position: int, label: string, value: string|int|float, context: ?string}>
     *     }>
     * }
     */
    public function toArray(): array
    {
        return [
            'period' => $this->period,
            'kpis' => array_map(
                static fn (KpiData $kpi): array => $kpi->toArray(),
                $this->kpis,
            ),
            'charts' => array_map(
                static fn (ChartData $chart): array => $chart->toArray(),
                $this->charts,
            ),
            'tables' => array_map(
                static fn (RankingTableData $table): array => $table->toArray(),
                $this->tables,
            ),
        ];
    }
}
