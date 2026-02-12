<?php

namespace App\Helpers;

class EmailHelper
{
    /**
     * Normaliza um e-mail para armazenamento no banco.
     *
     * - Remove espaços extras (início e fim)
     * - Sanitiza caracteres estranhos
     * - Valida formato básico
     * - Converte para minúsculas
     * - Garante limite de tamanho (254 caracteres)
     *
     * Retorna:
     *  - string normalizada se for válido
     *  - null se for inválido ou vazio
     *
     * @return string|null
     *
     * Como usar:
     * $normalizedEmail = EmailHelper::normalize($inputEmail);
     * if ($normalizedEmail === null) {
     *    // E-mail inválido
     * } else {
     *    // E-mail válido e normalizado
     * }
     *
     * Observação: esta função não envia e-mails, apenas processa strings de e-mail.
     */
    public static function normalize(?string $email): ?string
    {
        if ($email === null) {
            return null;
        }

        // 1) Remove espaços no início e fim
        $email = trim($email);

        if ($email === '') {
            return null;
        }

        // 2) Sanitiza (remove caracteres claramente inválidos)
        $sanitized = filter_var($email, FILTER_SANITIZE_EMAIL);

        if ($sanitized === false || $sanitized === '') {
            return null;
        }

        // 3) Valida formato
        if (! filter_var($sanitized, FILTER_VALIDATE_EMAIL)) {
            return null;
        }

        // 4) Normaliza caixa: gravar sempre em minúsculas
        $normalized = mb_strtolower($sanitized, 'UTF-8');

        // 5) Limite de tamanho conforme recomendação (254 caracteres)
        if (strlen($normalized) > 254) {
            return null;
        }

        return $normalized;
    }

    /**
     * Verifica se o e-mail é válido (formato) sem necessariamente normalizar.
     */
    public static function isValid(?string $email): bool
    {
        if ($email === null) {
            return false;
        }

        $email = trim($email);

        if ($email === '') {
            return false;
        }

        $sanitized = filter_var($email, FILTER_SANITIZE_EMAIL);

        if ($sanitized === false || $sanitized === '') {
            return false;
        }

        if (strlen($sanitized) > 254) {
            return false;
        }

        return (bool) filter_var($sanitized, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Opcional: verifica se o domínio do e-mail possui DNS (MX ou A).
     * Útil para filtrar domínios obviamente inexistentes.
     *
     * Atenção: depende de DNS do servidor e pode não funcionar em todos ambientes.
     */
    public static function hasValidDomain(?string $email): bool
    {
        $normalized = self::normalize($email);

        if ($normalized === null) {
            return false;
        }

        $pos = strrpos($normalized, '@');

        if ($pos === false) {
            return false;
        }

        $domain = substr($normalized, $pos + 1);

        if ($domain === '' || $domain === false) {
            return false;
        }

        // Tenta encontrar registro MX ou A
        return checkdnsrr($domain, 'MX') || checkdnsrr($domain, 'A');
    }
}
