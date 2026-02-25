<?php

namespace App\Services\Training;

use DOMDocument;
use DOMElement;
use DOMNode;

class TestimonySanitizer
{
    public const MAX_CHARACTERS = 6000;

    /**
     * @var array<string, array<int, string>>
     */
    private const ALLOWED_TAGS = [
        'p' => ['style'],
        'div' => ['style'],
        'span' => ['style'],
        'br' => [],
        'strong' => [],
        'b' => [],
        'em' => [],
        'i' => [],
        'u' => [],
        's' => [],
        'strike' => [],
        'ul' => [],
        'ol' => [],
        'li' => ['style'],
        'h1' => ['style'],
        'h2' => ['style'],
        'h3' => ['style'],
        'h4' => ['style'],
        'blockquote' => ['style'],
    ];

    public static function sanitize(?string $html): ?string
    {
        $normalizedHtml = trim((string) $html);

        if ($normalizedHtml === '') {
            return null;
        }

        $previousUseInternalErrors = libxml_use_internal_errors(true);
        $document = new DOMDocument('1.0', 'UTF-8');
        $loaded = $document->loadHTML(
            '<?xml encoding="utf-8" ?><body>'.$normalizedHtml.'</body>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previousUseInternalErrors);

        if (! $loaded) {
            return strip_tags($normalizedHtml);
        }

        $body = $document->getElementsByTagName('body')->item(0);

        if (! $body instanceof DOMElement) {
            return strip_tags($normalizedHtml);
        }

        self::sanitizeNodeChildren($body);

        $sanitized = trim((string) $body->ownerDocument?->saveHTML($body));
        $sanitized = preg_replace('/^<body>|<\/body>$/', '', $sanitized ?? '');
        $sanitized = trim((string) $sanitized);

        if ($sanitized === '') {
            return null;
        }

        return $sanitized;
    }

    public static function plainTextLength(?string $html): int
    {
        $text = trim(html_entity_decode(strip_tags((string) $html), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $text = preg_replace('/\s+/u', ' ', $text) ?? '';

        return mb_strlen($text);
    }

    private static function sanitizeNodeChildren(DOMNode $parent): void
    {
        $children = [];

        foreach ($parent->childNodes as $child) {
            $children[] = $child;
        }

        foreach ($children as $child) {
            if (! $child instanceof DOMElement) {
                continue;
            }

            $tagName = strtolower($child->tagName);

            if (! array_key_exists($tagName, self::ALLOWED_TAGS)) {
                self::unwrapElement($child);

                continue;
            }

            self::sanitizeAttributes($child, self::ALLOWED_TAGS[$tagName]);
            self::sanitizeNodeChildren($child);
        }
    }

    /**
     * @param  array<int, string>  $allowedAttributes
     */
    private static function sanitizeAttributes(DOMElement $element, array $allowedAttributes): void
    {
        $attributesToRemove = [];

        foreach ($element->attributes as $attribute) {
            $attributeName = strtolower($attribute->name);

            if (! in_array($attributeName, $allowedAttributes, true)) {
                $attributesToRemove[] = $attribute->name;

                continue;
            }

            if ($attributeName === 'style') {
                $sanitizedStyle = self::sanitizeStyle($attribute->value);

                if ($sanitizedStyle === '') {
                    $attributesToRemove[] = $attribute->name;

                    continue;
                }

                $element->setAttribute('style', $sanitizedStyle);
            }
        }

        foreach ($attributesToRemove as $attributeName) {
            $element->removeAttribute($attributeName);
        }
    }

    private static function sanitizeStyle(string $style): string
    {
        $declarations = array_filter(array_map('trim', explode(';', $style)));
        $sanitizedDeclarations = [];

        foreach ($declarations as $declaration) {
            if (! str_contains($declaration, ':')) {
                continue;
            }

            [$property, $value] = array_map('trim', explode(':', $declaration, 2));
            $property = strtolower($property);
            $sanitizedValue = self::sanitizeStyleValue($property, $value);

            if ($sanitizedValue === null) {
                continue;
            }

            $sanitizedDeclarations[] = sprintf('%s:%s', $property, $sanitizedValue);
        }

        return implode(';', $sanitizedDeclarations);
    }

    private static function sanitizeStyleValue(string $property, string $value): ?string
    {
        return match ($property) {
            'text-align' => preg_match('/^(left|center|right|justify)$/i', $value) === 1 ? strtolower($value) : null,
            'font-weight' => preg_match('/^(normal|bold|[1-9]00)$/i', $value) === 1 ? strtolower($value) : null,
            'font-style' => preg_match('/^(normal|italic)$/i', $value) === 1 ? strtolower($value) : null,
            'text-decoration' => preg_match('/^(none|underline|line-through|underline line-through|line-through underline)$/i', $value) === 1
                ? strtolower($value)
                : null,
            'color' => preg_match('/^(#[0-9a-f]{3,8}|rgb[a]?\([^)]+\)|hsl[a]?\([^)]+\)|[a-z]{3,20})$/i', $value) === 1 ? $value : null,
            'font-size' => preg_match('/^\d+(\.\d+)?(px|em|rem|pt|%)$/i', $value) === 1 ? strtolower($value) : null,
            'font-family' => preg_match('/^[a-z0-9,\s"\047-]+$/i', $value) === 1 ? $value : null,
            default => null,
        };
    }

    private static function unwrapElement(DOMElement $element): void
    {
        $parent = $element->parentNode;

        if (! $parent instanceof DOMNode) {
            return;
        }

        while ($element->firstChild) {
            $parent->insertBefore($element->firstChild, $element);
        }

        $parent->removeChild($element);
    }
}
