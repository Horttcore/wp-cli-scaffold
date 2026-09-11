<?php

declare(strict_types=1);

if (! function_exists('esc_html')) {
    function esc_html(string $text): string
    {
        return $text;
    }
}

if (! function_exists('esc_attr')) {
    function esc_attr(string $text): string
    {
        return $text;
    }
}

if (! function_exists('sanitize_title')) {
    function sanitize_title(string $title): string
    {
        return trim(strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $title) ?? ''), '-');
    }
}

if (! function_exists('wp_json_encode')) {
    function wp_json_encode(mixed $value, int $flags = 0, int $depth = 512): string|false
    {
        return json_encode($value, $flags, $depth);
    }
}
