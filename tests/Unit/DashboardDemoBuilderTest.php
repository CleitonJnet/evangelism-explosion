<?php

use App\Services\Dashboard\DashboardDemoBuilder;
use App\Support\Dashboard\Enums\DashboardPeriod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('builds reusable dashboard payloads for chart and table widgets', function (): void {
    $payload = app(DashboardDemoBuilder::class)
        ->build(DashboardPeriod::Semester)
        ->toArray();

    expect($payload['period'])->toBe('semester')
        ->and($payload['kpis'])->toHaveCount(4)
        ->and($payload['charts'])->toHaveCount(3)
        ->and($payload['tables'])->toHaveCount(1)
        ->and($payload['charts'][0])->toMatchArray([
            'id' => 'registrations-timeline',
            'type' => 'line',
            'seriesType' => 'time',
        ])
        ->and($payload['charts'][0]['datasets'][0]['data'][0])->toHaveKeys(['x', 'y'])
        ->and($payload['charts'][1])->toMatchArray([
            'id' => 'discipleship-progress',
            'type' => 'bar',
            'seriesType' => 'category',
        ])
        ->and($payload['charts'][2])->toMatchArray([
            'id' => 'training-status-share',
            'type' => 'doughnut',
        ])
        ->and($payload['tables'][0]['rows'][0])->toHaveKeys([
            'position',
            'label',
            'value',
            'context',
        ]);
});
