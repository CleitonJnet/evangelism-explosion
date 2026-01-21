<?php

namespace App\Helpers;

class PhoneHelper
{
    /**
     * Remove tudo que não é dígito.
     * Ex.: "(21) 97276-5535" -> "21972765535"
     */
    public static function normalize(?string $phone): ?string
    {
        if ($phone === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        return $digits === '' ? null : $digits;
    }

    public static function format_phone(?string $phone): ?string
    {
        if ($phone === null) {
            return null;
        }

        // Remove tudo que não for número
        $digits = preg_replace('/\D/', '', $phone) ?? '';

        // Remove DDI (Brasil = 55), se existir
        if (strlen($digits) > 11 && str_starts_with($digits, '55')) {
            $digits = substr($digits, 2);
        }

        // Agora esperamos algo no formato: DDD + número (10 ou 11 dígitos)
        if (strlen($digits) < 10) {
            // Número inválido ou incompleto
            return $phone;
        }

        $ddd = substr($digits, 0, 2);
        $number = substr($digits, 2);

        // Telefone fixo → 8 dígitos
        if (strlen($number) === 8) {
            return sprintf(
                '(%s) %s-%s',
                $ddd,
                substr($number, 0, 4),
                substr($number, 4, 4)
            );
        }

        // Celular → 9 dígitos
        if (strlen($number) === 9) {
            return sprintf(
                '(%s) %s-%s',
                $ddd,
                substr($number, 0, 5),
                substr($number, 5, 4)
            );
        }

        // Caso inesperado (ex.: ramais, números longos, etc.)
        return $phone;
    }
}
