<?php

namespace App\Helpers;

class NameUser
{
    /**
     * Retorna as iniciais do primeiro e do último nome.
     */
    public static function initials(string $fullName): string
    {
        $fullName = trim($fullName);

        if ($fullName === '') {
            return '';
        }

        $normalized = preg_replace('/\s+/u', ' ', $fullName);

        if ($normalized === null || $normalized === '') {
            return '';
        }

        $parts = array_values(array_filter(explode(' ', $normalized), static fn (string $part): bool => $part !== ''));

        if ($parts === []) {
            return '';
        }

        $first = $parts[0];
        $last = $parts[count($parts) - 1];

        $firstInitial = self::mbFirstChar($first);
        $lastInitial = $last === $first ? '' : self::mbFirstChar($last);

        return $firstInitial.$lastInitial;
    }

    protected static function mbFirstChar(string $value): string
    {
        if ($value === '') {
            return '';
        }

        $firstChar = mb_substr($value, 0, 1, 'UTF-8');

        return mb_strtoupper($firstChar, 'UTF-8');
    }
}
