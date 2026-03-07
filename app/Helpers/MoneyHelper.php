<?php

namespace App\Helpers;

class MoneyHelper
{
    public static function symbol(): string
    {
        return (string) config('money.symbol', 'R$');
    }

    public static function decimalSeparator(): string
    {
        return (string) config('money.decimal_separator', ',');
    }

    public static function thousandSeparator(): string
    {
        return (string) config('money.thousand_separator', '.');
    }

    public static function format_money(string|int|float|null $money): ?string
    {
        return self::format($money, withSymbol: true);
    }

    public static function format(string|int|float|null $money, bool $withSymbol = true): ?string
    {
        if ($money === null || $money === '') {
            return null;
        }

        $normalized = self::normalizeMoneyValue($money);

        if ($normalized === null) {
            return is_string($money) ? $money : null;
        }

        $formatted = number_format($normalized, 2, self::decimalSeparator(), self::thousandSeparator());

        if (! $withSymbol) {
            return $formatted;
        }

        return self::symbol().' '.$formatted;
    }

    public static function formatInput(string|int|float|null $money, ?string $default = null): ?string
    {
        if ($money === null || $money === '') {
            return $default;
        }

        return self::format($money, withSymbol: false) ?? $default;
    }

    public static function toFloat(string|int|float|null $money): ?float
    {
        if ($money === null || $money === '') {
            return null;
        }

        return self::normalizeMoneyValue($money);
    }

    public static function toDatabase(string|int|float|null $money): ?string
    {
        $floatValue = self::toFloat($money);

        if ($floatValue === null) {
            return null;
        }

        return number_format($floatValue, 2, '.', '');
    }

    protected static function normalizeMoneyValue(string|int|float $money): ?float
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

        $lastCommaPosition = strrpos($money, ',');
        $lastDotPosition = strrpos($money, '.');

        if ($lastCommaPosition !== false && $lastDotPosition !== false) {
            $decimalSeparator = $lastCommaPosition > $lastDotPosition ? ',' : '.';
            $thousandSeparator = $decimalSeparator === ',' ? '.' : ',';

            $money = str_replace($thousandSeparator, '', $money);
            $money = str_replace($decimalSeparator, '.', $money);
        } else {
            $separator = $lastCommaPosition !== false ? ',' : ($lastDotPosition !== false ? '.' : null);

            if ($separator !== null) {
                $separatorPosition = $lastCommaPosition !== false ? $lastCommaPosition : $lastDotPosition;
                $digitsAfterSeparator = strlen($money) - $separatorPosition - 1;
                $separatorOccurrences = substr_count($money, $separator);
                $configuredDecimalSeparator = self::decimalSeparator();
                $configuredThousandSeparator = self::thousandSeparator();

                $shouldTreatAsThousandSeparator = $separator === $configuredThousandSeparator
                    && $separator !== $configuredDecimalSeparator
                    && ($separatorOccurrences > 1 || $digitsAfterSeparator === 3);

                if ($shouldTreatAsThousandSeparator) {
                    $money = str_replace($separator, '', $money);
                } else {
                    $money = str_replace($separator, '.', $money);
                }
            }
        }

        if (! is_numeric($money)) {
            return null;
        }

        return (float) $money;
    }
}
