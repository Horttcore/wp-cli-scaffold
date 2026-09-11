<?php

declare(strict_types=1);

namespace RalfHortt\WpCliScaffold\Commands;

use RalfHortt\WpCliScaffold\Support\ThemeJsonSectionMerger;
use RalfHortt\WpCliShared\Support\FileGuard;
use RalfHortt\WpCliShared\Support\PromptHelper;
use RalfHortt\WpCliShared\ThemeResolver;

final class ThemeJsonSection_Command
{
    /**
     * Scaffold or merge a section in theme.json.
     *
     * ## OPTIONS
     *
     * --section=<section>
     * : Dot-notated theme.json path (e.g. settings.color.palette).
     *
     * [--json=<json>]
     * : JSON object or array to merge into the section.
     *
     * [--file=<file>]
     * : JSON file path to load merge payload from.
     *
     * [--theme=<theme>]
     * : Theme stylesheet slug override.
     *
     * [--dry-run]
     * : Print merged theme.json without writing.
     *
     * [--force]
     * : Overwrite existing theme.json without prompt.
     *
     * [--no-interaction]
     * : Disable interactive prompts.
     *
     * @param  array<int, string>  $args
     * @param  array<string, mixed>  $assoc_args
     */
    public function __invoke(array $args, array $assoc_args): void
    {
        unset($args);

        $themeSlug = \WP_CLI\Utils\get_flag_value($assoc_args, 'theme');
        $themeResolver = new ThemeResolver(is_string($themeSlug) ? $themeSlug : null);
        $merger = new ThemeJsonSectionMerger();

        $section = PromptHelper::suggestOrFlag(
            $assoc_args,
            'section',
            'Select or enter theme.json section path',
            $this->getSectionSuggestions(),
            'settings.color.palette',
            allowFreeText: true,
        );
        $payload = $this->resolvePayload($assoc_args);

        $themeJsonPath = $themeResolver->getThemeJsonPath();
        $current = $this->readThemeJson($themeJsonPath);
        $current['version'] = isset($current['version']) && is_int($current['version'])
            ? $current['version']
            : 3;

        $merged = $current;

        try {
            $merged = $merger->merge($current, $section, $payload);
        } catch (\InvalidArgumentException $exception) {
            \WP_CLI::error($exception->getMessage());
        }

        $json = wp_json_encode($merged, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        if (! is_string($json) || $json === '') {
            \WP_CLI::error('Failed to encode merged theme.json.');
        }

        $json .= "\n";

        if (\WP_CLI\Utils\get_flag_value($assoc_args, 'dry-run')) {
            \WP_CLI::log($json);
            \WP_CLI::success('Dry run complete. No file written.');

            return;
        }

        FileGuard::assertCanWrite($themeJsonPath, $assoc_args);

        if (file_put_contents($themeJsonPath, $json) === false) {
            \WP_CLI::error('Failed to write theme.json.');
        }

        \WP_CLI::success(sprintf('theme.json updated: %s', $themeJsonPath));
    }

    /**
     * @param  array<string, mixed>  $assoc_args
     */
    private function resolvePayload(array $assoc_args): mixed
    {
        $json = \WP_CLI\Utils\get_flag_value($assoc_args, 'json');
        $file = \WP_CLI\Utils\get_flag_value($assoc_args, 'file');

        if (is_string($json) && trim($json) !== '') {
            return $this->decodeJson($json, '--json');
        }

        if (is_string($file) && trim($file) !== '') {
            if (! file_exists($file) || ! is_readable($file)) {
                \WP_CLI::error(sprintf('Cannot read payload file: %s', $file));
            }

            $contents = file_get_contents($file);

            if (! is_string($contents)) {
                \WP_CLI::error(sprintf('Failed to read payload file: %s', $file));
            }

            return $this->decodeJson($contents, '--file');
        }

        if (! PromptHelper::isInteractive($assoc_args)) {
            \WP_CLI::error('Provide --json or --file in non-interactive mode.');
        }

        $input = PromptHelper::textarea(
            'Paste JSON payload for the section merge',
            'Example: [{"slug":"brand","name":"Brand","color":"#0055ff"}]'
        );

        return $this->decodeJson($input, 'interactive input');
    }

    private function decodeJson(string $json, string $source): mixed
    {
        try {
            return json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            \WP_CLI::error(sprintf('Invalid JSON from %s: %s', $source, $exception->getMessage()));

            throw new \RuntimeException('Unreachable after WP_CLI::error().');
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function readThemeJson(string $path): array
    {
        if (! file_exists($path)) {
            return [
                '$schema' => 'https://schemas.wp.org/trunk/theme.json',
                'version' => 3,
            ];
        }

        $contents = file_get_contents($path);

        if (! is_string($contents) || trim($contents) === '') {
            return [
                '$schema' => 'https://schemas.wp.org/trunk/theme.json',
                'version' => 3,
            ];
        }

        $decoded = json_decode($contents, true);

        if (! is_array($decoded)) {
            \WP_CLI::error(sprintf('Existing theme.json at %s is not valid JSON.', $path));
        }

        return $decoded;
    }

    /**
     * @return array<string, string>
     */
    private function getSectionSuggestions(): array
    {
        $sections = [
            'settings.color.palette',
            'settings.color.gradients',
            'settings.typography.fontSizes',
            'settings.typography.fontFamilies',
            'settings.spacing.spacingSizes',
            'settings.custom',
            'styles',
            'styles.blocks',
            'styles.elements',
            'styles.typography',
            'styles.color',
            'customTemplates',
            'templateParts',
        ];

        $result = [];

        foreach ($sections as $section) {
            $result[$section] = $section;
        }

        return $result;
    }
}
