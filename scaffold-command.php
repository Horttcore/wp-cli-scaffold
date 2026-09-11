<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use RalfHortt\WpCliScaffold\Commands\CreateBlock_Command;
use RalfHortt\WpCliScaffold\Commands\Pattern_Command;
use RalfHortt\WpCliScaffold\Commands\QueryLoop_Command;
use RalfHortt\WpCliScaffold\Commands\StarterContent_Command;
use RalfHortt\WpCliShared\Bootstrap;

if (! class_exists('WP_CLI')) {
    return;
}

Bootstrap::registerPromptFallback();

WP_CLI::add_hook('after_add_command:scaffold', function (): void {
    WP_CLI::add_command('scaffold create-block', CreateBlock_Command::class);
    WP_CLI::add_command('scaffold pattern', Pattern_Command::class);
    WP_CLI::add_command('scaffold starter-content', StarterContent_Command::class);
    WP_CLI::add_command('scaffold query-loop', QueryLoop_Command::class);
});
