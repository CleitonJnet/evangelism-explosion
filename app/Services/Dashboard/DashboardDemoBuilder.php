<?php

namespace App\Services\Dashboard;

use App\Support\Dashboard\Builders\ChartPayloadBuilder;
use App\Support\Dashboard\Data\ChartDatasetData;
use App\Support\Dashboard\Data\DashboardPayload;
use App\Support\Dashboard\Data\KpiData;
use App\Support\Dashboard\Data\RankingRowData;
use App\Support\Dashboard\Data\RankingTableData;
use App\Support\Dashboard\Data\TimeSeriesPointData;
use App\Support\Dashboard\Enums\DashboardPeriod;
use Carbon\CarbonImmutable;

class DashboardDemoBuilder
{
    public function __construct(
        private ChartPayloadBuilder $chartPayloadBuilder,
    ) {}

    public function build(DashboardPeriod $period): DashboardPayload
    {
        $range = $period->range();
        $months = $period->months();
        $seriesPoints = [];
        $barLabels = [];
        $barValues = [];
        $doughnutValues = [];

        for ($index = 0; $index < $months; $index++) {
            $month = $range['start']->addMonths($index);
            $seriesPoints[] = new TimeSeriesPointData(
                x: $month->toDateString(),
                y: 16 + ($index * 4),
            );
            $barLabels[] = $month->translatedFormat('M');
            $barValues[] = 8 + ($index * 3);
            $doughnutValues[] = max(4, 22 - $index);
        }

        $timeSeriesChart = $this->chartPayloadBuilder->timeSeries(
            id: 'registrations-timeline',
            title: __('Inscrições por período'),
            datasets: [
                new ChartDatasetData(
                    label: __('Inscrições'),
                    data: array_map(
                        static fn (TimeSeriesPointData $point): array => $point->toArray(),
                        $seriesPoints,
                    ),
                    backgroundColor: 'rgba(14, 116, 144, 0.18)',
                    borderColor: 'rgb(14, 116, 144)',
                    fill: true,
                ),
            ],
            options: [
                'xAxis' => [
                    'unit' => $months <= 3 ? 'week' : 'month',
                ],
                'valueSuffix' => __(' inscrições'),
            ],
        );

        $barChart = $this->chartPayloadBuilder->bar(
            id: 'discipleship-progress',
            title: __('Acompanhamentos por janela'),
            labels: $barLabels,
            datasets: [
                new ChartDatasetData(
                    label: __('Acompanhamentos'),
                    data: $barValues,
                    backgroundColor: 'rgba(245, 158, 11, 0.75)',
                    borderColor: 'rgb(180, 83, 9)',
                ),
            ],
            options: [
                'stacked' => false,
                'valueSuffix' => __(' visitas'),
            ],
        );

        $doughnutChart = $this->chartPayloadBuilder->doughnut(
            id: 'training-status-share',
            title: __('Mix de status'),
            labels: [__('Planejamento'), __('Agendado'), __('Concluído')],
            datasets: [
                new ChartDatasetData(
                    label: __('Treinamentos'),
                    data: [
                        $doughnutValues[0] ?? 0,
                        $doughnutValues[1] ?? 0,
                        $doughnutValues[2] ?? 0,
                    ],
                    backgroundColor: [
                        'rgba(245, 158, 11, 0.82)',
                        'rgba(14, 165, 233, 0.82)',
                        'rgba(16, 185, 129, 0.82)',
                    ],
                    borderColor: [
                        'rgb(180, 83, 9)',
                        'rgb(2, 132, 199)',
                        'rgb(5, 150, 105)',
                    ],
                ),
            ],
            options: [
                'valueSuffix' => __(' treinamentos'),
                'legendPosition' => 'bottom',
            ],
        );

        return new DashboardPayload(
            period: $period->value,
            kpis: [
                new KpiData(
                    key: 'trainings',
                    label: __('Treinamentos no período'),
                    value: $this->formatKpiValue(12 + $months),
                    description: $this->describeRange($range['start'], $range['end']),
                    trend: __('Base para cards operacionais e dashboard'),
                ),
                new KpiData(
                    key: 'registrations',
                    label: __('Inscrições consolidadas'),
                    value: $this->formatKpiValue(80 + ($months * 11)),
                    description: __('Compatível com métricas de inscrições'),
                    trend: __('+12% vs. janela anterior'),
                ),
                new KpiData(
                    key: 'decisions',
                    label: __('Decisões registradas'),
                    value: $this->formatKpiValue(14 + ($months * 3)),
                    description: __('Preparado para STP/OJT e discipulado'),
                    trend: __('+7% vs. janela anterior'),
                ),
                new KpiData(
                    key: 'finance',
                    label: __('Saldo estimado'),
                    value: 'R$ '.number_format(18000 + ($months * 1450), 2, ',', '.'),
                    description: __('Preparado para financeiro'),
                    trend: __('Atualização mensal'),
                ),
            ],
            charts: [$timeSeriesChart, $barChart, $doughnutChart],
            tables: [
                new RankingTableData(
                    id: 'church-ranking',
                    title: __('Igrejas com maior participação'),
                    columns: [__('#'), __('Igreja'), __('Inscritos'), __('Contexto')],
                    rows: [
                        new RankingRowData(1, 'Igreja Central', 34, __('Treinamentos ativos')),
                        new RankingRowData(2, 'Igreja do Recife', 29, __('Expansão recente')),
                        new RankingRowData(3, 'Igreja Esperança', 22, __('Bom índice de retenção')),
                    ],
                ),
            ],
        );
    }

    private function formatKpiValue(int $value): string
    {
        return number_format($value, 0, ',', '.');
    }

    private function describeRange(CarbonImmutable $start, CarbonImmutable $end): string
    {
        return __('De :start até :end', [
            'start' => $start->translatedFormat('M/Y'),
            'end' => $end->translatedFormat('M/Y'),
        ]);
    }
}
