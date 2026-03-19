<?php

namespace App\Support;

class HtmlSanitizer
{
    /**
     * Tags removed entirely from user-provided HTML.
     *
     * @var array<int, string>
     */
    private const BLOCKED_TAGS = [
        'script',
        'iframe',
        'object',
        'embed',
        'applet',
        'link',
        'meta',
        'base',
        'form',
        'input',
        'button',
        'textarea',
        'select',
    ];

    /**
     * Attributes removed from all elements.
     *
     * @var array<int, string>
     */
    private const BLOCKED_ATTRIBUTES = [
        'style',
        'srcdoc',
    ];

    public static function sanitize(?string $html): string
    {
        $html = (string) $html;
        if (trim($html) === '') {
            return '';
        }

        $previous = libxml_use_internal_errors(true);
        $dom = new \DOMDocument('1.0', 'UTF-8');

        $wrapped = '<!DOCTYPE html><html><body><div id="__sanitized_root__">' . $html . '</div></body></html>';
        $loaded = $dom->loadHTML($wrapped, LIBXML_HTML_NODEFDTD | LIBXML_HTML_NOIMPLIED);

        if (!$loaded) {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
            return '';
        }

        $xpath = new \DOMXPath($dom);
        $root = $xpath->query("//*[@id='__sanitized_root__']")->item(0);
        if (!$root instanceof \DOMElement) {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
            return '';
        }

        self::sanitizeNode($root);

        $output = '';
        foreach ($root->childNodes as $childNode) {
            $output .= $dom->saveHTML($childNode) ?: '';
        }

        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return $output;
    }

    private static function sanitizeNode(\DOMNode $node): void
    {
        $children = [];
        foreach ($node->childNodes as $childNode) {
            $children[] = $childNode;
        }

        foreach ($children as $childNode) {
            if (!$childNode instanceof \DOMElement) {
                continue;
            }

            $tag = strtolower($childNode->tagName);
            if (in_array($tag, self::BLOCKED_TAGS, true)) {
                $childNode->parentNode?->removeChild($childNode);
                continue;
            }

            self::sanitizeAttributes($childNode);
            self::sanitizeNode($childNode);
        }
    }

    private static function sanitizeAttributes(\DOMElement $element): void
    {
        $toRemove = [];

        foreach ($element->attributes as $attribute) {
            $name = strtolower($attribute->name);
            $value = trim((string) $attribute->value);

            if (str_starts_with($name, 'on')) {
                $toRemove[] = $attribute->name;
                continue;
            }

            if (in_array($name, self::BLOCKED_ATTRIBUTES, true)) {
                $toRemove[] = $attribute->name;
                continue;
            }

            if (in_array($name, ['href', 'src', 'xlink:href', 'formaction', 'action'], true)) {
                if (self::isDangerousUrl($value)) {
                    $toRemove[] = $attribute->name;
                }
            }
        }

        foreach ($toRemove as $attributeName) {
            $element->removeAttribute($attributeName);
        }
    }

    private static function isDangerousUrl(string $value): bool
    {
        if ($value === '') {
            return false;
        }

        $decoded = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $normalized = strtolower(trim($decoded));

        return str_starts_with($normalized, 'javascript:')
            || str_starts_with($normalized, 'vbscript:')
            || str_starts_with($normalized, 'data:text/html')
            || str_starts_with($normalized, 'data:application/xhtml+xml');
    }
}
