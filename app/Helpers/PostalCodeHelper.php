<?php

namespace App\Helpers;

class PostalCodeHelper
{
    /**
     * Remove tudo que não é dígito.
     * Ex.: "97.276-553" -> "97276553"
     */
    public static function normalize(?string $postalcode): ?string
    {
        if ($postalcode === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $postalcode) ?? '';

        return $digits === '' ? null : $digits;
    }

    public static function format_postalcode(?string $postalcode): ?string
    {
        if ($postalcode === null) {
            return null;
        }

        // Remove tudo que não for número
        $digits = preg_replace('/\D/', '', $postalcode) ?? '';

        if (strlen($digits) !== 8) {
            return $postalcode;
        }

        return sprintf('%s.%s-%s', substr($digits, 0, 2), substr($digits, 2, 3), substr($digits, 5, 3));

        // return sprintf('%s-%s', substr($digits, 0, 5), substr($digits, 5, 3));
    }
}
