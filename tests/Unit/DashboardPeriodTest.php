<?php

use App\Support\Dashboard\Enums\DashboardPeriod;
use Carbon\CarbonImmutable;

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

it('uses the current calendar year for the annual range', function (): void {
    $range = DashboardPeriod::Year->range(CarbonImmutable::parse('2026-08-19'));

    expect($range['start']->toDateString())->toBe('2026-01-01')
        ->and($range['end']->toDateString())->toBe('2026-12-31');
});

it('uses the current semester inside the current year', function (): void {
    $firstSemester = DashboardPeriod::Semester->range(CarbonImmutable::parse('2026-03-10'));
    $secondSemester = DashboardPeriod::Semester->range(CarbonImmutable::parse('2026-08-19'));

    expect($firstSemester['start']->toDateString())->toBe('2026-01-01')
        ->and($firstSemester['end']->toDateString())->toBe('2026-06-30')
        ->and($secondSemester['start']->toDateString())->toBe('2026-07-01')
        ->and($secondSemester['end']->toDateString())->toBe('2026-12-31');
});

it('uses the current quarter inside the current year', function (): void {
    $firstQuarter = DashboardPeriod::Quarter->range(CarbonImmutable::parse('2026-02-10'));
    $thirdQuarter = DashboardPeriod::Quarter->range(CarbonImmutable::parse('2026-08-19'));

    expect($firstQuarter['start']->toDateString())->toBe('2026-01-01')
        ->and($firstQuarter['end']->toDateString())->toBe('2026-03-31')
        ->and($thirdQuarter['start']->toDateString())->toBe('2026-07-01')
        ->and($thirdQuarter['end']->toDateString())->toBe('2026-09-30');
});
