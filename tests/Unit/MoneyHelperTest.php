<?php

use App\Helpers\MoneyHelper;
use Tests\TestCase;

uses(TestCase::class);

it('formats and normalizes money using configured separators', function (): void {
    config()->set('money.symbol', 'R$');
    config()->set('money.decimal_separator', ',');
    config()->set('money.thousand_separator', '.');

    expect(MoneyHelper::format_money(1234.5))->toBe('R$ 1.234,50')
        ->and(MoneyHelper::formatInput(1234.5))->toBe('1.234,50')
        ->and(MoneyHelper::toFloat('1.234,50'))->toBe(1234.5)
        ->and(MoneyHelper::toFloat('1234.50'))->toBe(1234.5)
        ->and(MoneyHelper::toDatabase('1.234,50'))->toBe('1234.50');
});

it('supports alternate separators from configuration', function (): void {
    config()->set('money.symbol', '$');
    config()->set('money.decimal_separator', '.');
    config()->set('money.thousand_separator', ',');

    expect(MoneyHelper::format_money(1234.5))->toBe('$ 1,234.50')
        ->and(MoneyHelper::formatInput(1234.5))->toBe('1,234.50')
        ->and(MoneyHelper::toFloat('1,234.50'))->toBe(1234.5)
        ->and(MoneyHelper::toDatabase('1,234.50'))->toBe('1234.50');
});
