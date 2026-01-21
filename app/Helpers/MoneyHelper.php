<?php

namespace App\Helpers;

class MoneyHelper
{
    public static function format_money(string|int|float|null $money): ?string
    {
        if ($money === null || $money === '') {
            return null;
        }

        $currency = strtoupper((string) config('money.currency', 'BRL'));
        $symbol = (string) config("money.symbols.{$currency}", $currency);
        $decimalSeparator = (string) config("money.separators.{$currency}.decimal", '.');
        $thousandSeparator = (string) config("money.separators.{$currency}.thousand", ',');

        $normalized = self::normalizeMoneyValue($money, $currency);

        if ($normalized === null) {
            return is_string($money) ? $money : null;
        }

        return $symbol . ' ' . number_format($normalized, 2, $decimalSeparator, $thousandSeparator);
    }

    public static function toFloat(string|int|float|null $money): ?float
    {
        if ($money === null || $money === '') {
            return null;
        }

        $currency = strtoupper((string) config('money.currency', 'BRL'));

        return self::normalizeMoneyValue($money, $currency);
    }

    protected static function normalizeMoneyValue(string|int|float $money, string $currency): ?float
    {
        if (is_int($money) || is_float($money)) {
            return (float) $money;
        }

        $money = trim($money);

        if ($money === '') {
            return null;
        }

        $money = preg_replace('/[^\d,.\-]/', '', $money) ?? '';

        if ($money === '' || $money === '-') {
            return null;
        }

        if ($currency === 'BRL') {
            if (str_contains($money, ',')) {
                $money = str_replace('.', '', $money);
                $money = str_replace(',', '.', $money);
            } else {
                $money = str_replace(',', '', $money);
            }
        } else {
            $money = str_replace(',', '', $money);
        }

        if (!is_numeric($money)) {
            return null;
        }

        return (float) $money;
    }
}
