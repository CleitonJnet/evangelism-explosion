<?php

use App\Support\Dashboard\Enums\DashboardPeriod;

it('uses year as the default dashboard period', function (): void {
    expect(DashboardPeriod::default())->toBe(DashboardPeriod::Year)
        ->and(DashboardPeriod::fromValue(null))->toBe(DashboardPeriod::Year)
        ->and(DashboardPeriod::fromValue('invalid'))->toBe(DashboardPeriod::Year);
});

it('returns reusable dashboard period options', function (): void {
    expect(DashboardPeriod::options())->toBe([
        ['value' => 'quarter', 'label' => 'Trimestral'],
        ['value' => 'semester', 'label' => 'Semestral'],
        ['value' => 'year', 'label' => 'Anual'],
    ]);
});
