<?php

use App\Helpers\WeekHelper;
use Tests\TestCase;

uses(TestCase::class);

it('uses app locale by default', function () {
    config(['app.locale' => 'pt_BR']);

    expect(WeekHelper::dayName('2026-02-02'))->toBe('Segunda-feira');
});

it('allows overriding the locale', function () {
    config(['app.locale' => 'pt_BR']);

    expect(WeekHelper::dayName('2026-02-02', 'en'))->toBe('Monday');
});
