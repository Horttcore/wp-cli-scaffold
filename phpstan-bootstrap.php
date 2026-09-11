<?php

declare(strict_types=1);

namespace WP_CLI\Utils {
    function get_flag_value(array $assoc_args, string $flag, mixed $default = null): mixed
    {
        return $assoc_args[$flag] ?? $default;
    }
}

namespace {
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

    if (! function_exists('wp_mkdir_p')) {
        function wp_mkdir_p(string $target): bool
        {
            return true;
        }
    }

    if (! class_exists('WP_Theme')) {
        class WP_Theme
        {
            public function exists(): bool
            {
                return true;
            }

            public function get_stylesheet_directory(): string
            {
                return '/tmp';
            }

            public function get(string $key): string
            {
                return '';
            }

            public function get_stylesheet(): string
            {
                return 'theme';
            }

            public function is_block_theme(): bool
            {
                return true;
            }
        }
    }

    if (! function_exists('wp_get_theme')) {
        function wp_get_theme(?string $stylesheet = null): WP_Theme
        {
            return new WP_Theme();
        }
    }

    if (! class_exists('WP_CLI')) {
        class WP_CLI
        {
            public static function add_command(string $name, mixed $callable): void {}

            public static function add_hook(string $when, callable $callback): void {}

            public static function has_command(string $name): bool
            {
                return false;
            }

            public static function get_runner(): ?object
            {
                return null;
            }

            public static function log(string $message): void {}

            public static function warning(string $message): void {}

            public static function success(string $message): void {}

            public static function error(string $message): never
            {
                throw new RuntimeException($message);
            }
        }
    }

    if (! defined('WP_PLUGIN_DIR')) {
        define('WP_PLUGIN_DIR', '/tmp/plugins');
    }
}
