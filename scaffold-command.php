<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use RalfHortt\WpCliScaffold\Commands\CreateBlock_Command;
use RalfHortt\WpCliScaffold\Commands\Pattern_Command;
use RalfHortt\WpCliScaffold\Commands\QueryLoop_Command;
use RalfHortt\WpCliScaffold\Commands\StarterContent_Command;
use RalfHortt\WpCliScaffold\Commands\StyleVariation_Command;
use RalfHortt\WpCliScaffold\Commands\Template_Command;
use RalfHortt\WpCliScaffold\Commands\TemplatePart_Command;
use RalfHortt\WpCliScaffold\Commands\ThemeJsonSection_Command;
use RalfHortt\WpCliShared\Bootstrap;

if (! class_exists('WP_CLI')) {
    return;
}

Bootstrap::registerPromptFallback();

WP_CLI::add_hook('after_add_command:scaffold', function (): void {
    $registerIfMissing = static function (string $name, string $class): void {
        if (method_exists('WP_CLI', 'has_command') && WP_CLI::has_command($name)) {
            return;
        }

        WP_CLI::add_command($name, $class);
    };

    $registerIfMissing('scaffold create-block', CreateBlock_Command::class);
    $registerIfMissing('scaffold pattern', Pattern_Command::class);
    $registerIfMissing('scaffold starter-content', StarterContent_Command::class);
    $registerIfMissing('scaffold query-loop', QueryLoop_Command::class);
    $registerIfMissing('scaffold template', Template_Command::class);
    $registerIfMissing('scaffold template-part', TemplatePart_Command::class);
    $registerIfMissing('scaffold style-variation', StyleVariation_Command::class);
    $registerIfMissing('scaffold theme-json-section', ThemeJsonSection_Command::class);
});
