<?php

declare(strict_types=1);

namespace RalfHortt\WpCliScaffold\Support;

final class CreateBlockRunner
{
    private const CREATE_BLOCK_VERSION = '@wordpress/create-block@4';

    /**
     * @param  array<string, string>  $options
     * @param  array<string, mixed>  $assoc_args
     */
    public function run(string $slug, string $cwd, array $options, array $assoc_args): void
    {
        $targetDir = $options['target_dir'] ?? $slug;
        $targetPath = rtrim($cwd, '/') . '/' . ltrim($targetDir, '/');

        \RalfHortt\WpCliShared\Support\FileGuard::assertDirectoryWritable($targetPath, $assoc_args);

        $command = getenv('CREATE_BLOCK_COMMAND') ?: 'npx ' . self::CREATE_BLOCK_VERSION;

        $parts = [
            $command,
            escapeshellarg($slug),
        ];

        foreach ($options as $flag => $value) {
            if ($flag === 'target_dir') {
                $parts[] = '--target-dir=' . escapeshellarg($value);

                continue;
            }

            if ($value === 'true' || $value === true) {
                $parts[] = '--' . $flag;

                continue;
            }

            if ($value === 'false' || $value === false || $value === '') {
                continue;
            }

            $parts[] = '--' . $flag . '=' . escapeshellarg((string) $value);
        }

        $fullCommand = implode(' ', $parts);

        \WP_CLI::log('Executing: ' . $fullCommand);

        $previousDir = getcwd();
        chdir($cwd);

        $exitCode = 0;
        passthru($fullCommand, $exitCode);

        if ($previousDir !== false) {
            chdir($previousDir);
        }

        if ($exitCode !== 0) {
            \WP_CLI::error(sprintf('create-block failed with exit code %d.', $exitCode));
        }

        \WP_CLI::success(sprintf("Block '%s' created successfully.", $slug));
    }
}
