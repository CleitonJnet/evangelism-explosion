<?php

namespace App\Helpers;

class StringHelper
{
    /**
     * Lista de preposições que devem permanecer minúsculas
     */
    protected static array $prepositions = [
        'da',
        'das',
        'de',
        'do',
        'dos',
        'e',
    ];

    /**
     * Formata o nome próprio:
     * - Remove espaços extras (início, fim e no meio)
     * - Primeira letra de cada parte maiúscula
     * - Restante minúsculo
     * - Preposições minúsculas
     * - Trata apóstrofos: D’Ávila, O’Connor
     * - Trata nomes hifenizados: Maria-Clara, João-Pedro
     */
    public static function formatName(string $name): string
    {
        // 1) Remove espaços no início e no fim
        $name = trim($name);

        if ($name === '') {
            return '';
        }

        // 2) Converte qualquer sequência de espaços/brancos em UM espaço
        $name = preg_replace('/\s+/', ' ', $name);

        // 3) Tudo minúsculo primeiro (com suporte a acentos)
        $name = mb_strtolower($name, 'UTF-8');

        // 4) Separa o nome por espaço (agora garantidamente único)
        $parts = explode(' ', $name);

        $formatted = [];

        foreach ($parts as $part) {
            if ($part === '') {
                continue;
            }

            // Se for preposição, mantém minúscula
            if (in_array($part, self::$prepositions, true)) {
                $formatted[] = $part;
            } else {
                // Capitaliza parte "normal", com apóstrofos e hifens
                $formatted[] = self::capitalizeCompoundName($part);
            }
        }

        // 5) Junta com um único espaço entre cada palavra
        return implode(' ', $formatted);
    }

    /**
     * Opcional: alias sem duplicar lógica
     */
    public static function clearSpacesName(string $name): string
    {
        return self::formatName($name);
    }

    protected static function capitalizeCompoundName(string $part): string
    {
        $chunks = preg_split("/(['’])/u", $part, -1, PREG_SPLIT_DELIM_CAPTURE);

        if ($chunks === false) {
            return self::capitalizeHyphenated($part);
        }

        $result = '';

        foreach ($chunks as $chunk) {
            if ($chunk === "'" || $chunk === '’') {
                $result .= $chunk;

                continue;
            }

            $result .= self::capitalizeHyphenated($chunk);
        }

        return $result;
    }

    protected static function capitalizeHyphenated(string $string): string
    {
        $segments = preg_split('/(-)/u', $string, -1, PREG_SPLIT_DELIM_CAPTURE);

        if ($segments === false) {
            return self::mbUcfirst($string);
        }

        $result = '';

        foreach ($segments as $seg) {
            if ($seg === '-') {
                $result .= $seg;

                continue;
            }

            $result .= self::mbUcfirst($seg);
        }

        return $result;
    }

    protected static function mbUcfirst(string $string): string
    {
        if ($string === '') {
            return '';
        }

        $firstChar = mb_substr($string, 0, 1, 'UTF-8');
        $rest = mb_substr($string, 1, null, 'UTF-8');

        return mb_strtoupper($firstChar, 'UTF-8').mb_strtolower($rest, 'UTF-8');
    }
}
