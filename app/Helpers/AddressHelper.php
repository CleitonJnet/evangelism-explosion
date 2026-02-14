<?php

namespace App\Helpers;

class AddressHelper
{
    public static function format_address(
        ?string $street = null,
        ?string $number = null,
        ?string $complement = null,
        ?string $district = null,
        ?string $city = null,
        ?string $state = null,
        ?string $postal_code = null,
    ): string {
        $parts = array_filter([
            self::clean($street),
            self::clean($number),
            self::clean($complement),
            self::clean($district),
            self::clean($city),
            self::clean($state),
            self::clean($postal_code),
        ], fn (?string $part): bool => $part !== null && $part !== '');

        return implode(', ', $parts);
    }

    private static function clean(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }
}
