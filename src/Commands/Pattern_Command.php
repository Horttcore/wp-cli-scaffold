<?php

declare(strict_types=1);

namespace RalfHortt\WpCliScaffold\Commands;

use RalfHortt\WpCliScaffold\Generators\PatternGenerator;
use RalfHortt\WpCliShared\Support\FileGuard;
use RalfHortt\WpCliShared\Support\PromptHelper;
use RalfHortt\WpCliShared\Support\Slug;
use RalfHortt\WpCliShared\ThemeResolver;
use RalfHortt\WpCliShared\WordPressData;

final class Pattern_Command
{
    /**
     * Scaffold a block pattern file in the active theme.
     *
     * ## OPTIONS
     *
     * [--theme=<theme>]
     * : Theme stylesheet slug override.
     *
     * [--title=<title>]
     * : Pattern title.
     *
     * [--description=<description>]
     * : Pattern description.
     *
     * [--categories=<categories>]
     * : Comma-separated pattern categories.
     *
     * [--block-types=<block-types>]
     * : Comma-separated block types.
     *
     * [--post-types=<post-types>]
     * : Comma-separated post types.
     *
     * [--template-types=<template-types>]
     * : Comma-separated template types.
     *
     * [--inserter=<inserter>]
     * : Show in inserter (true/false).
     *
     * [--source=<source>]
     * : Pattern source (theme or plugin).
     *
     * [--force]
     * : Overwrite existing file.
     *
     * [--no-interaction]
     * : Disable interactive prompts.
     *
     * @param  array<int, string>  $args
     * @param  array<string, mixed>  $assoc_args
     */
    public function __invoke(array $args, array $assoc_args): void
    {
        $themeSlug = \WP_CLI\Utils\get_flag_value($assoc_args, 'theme');
        $themeResolver = new ThemeResolver(is_string($themeSlug) ? $themeSlug : null);
        $wpData = new WordPressData(is_string($themeSlug) ? $themeSlug : null);
        $generator = new PatternGenerator();

        $themeResolver->warnIfNotBlockTheme();

        $title = PromptHelper::textOrFlag($assoc_args, 'title', 'Enter pattern title');
        $slug = Slug::fromTitle($title);
        $description = \WP_CLI\Utils\get_flag_value($assoc_args, 'description', '') ?? '';

        $categories = PromptHelper::multisearchOrFlag(
            $assoc_args,
            'categories',
            'Select pattern categories',
            fn (string $_): array => $wpData->getPatternCategories(),
        );

        $blockTypes = PromptHelper::multisearchOrFlag(
            $assoc_args,
            'block-types',
            'Select block types (optional)',
            fn (string $_): array => $wpData->getBlocksForPatterns(),
        );

        $postTypes = PromptHelper::multisearchOrFlag(
            $assoc_args,
            'post-types',
            'Select post types (optional)',
            fn (string $_): array => $wpData->getPostTypesForPatterns(),
        );

        $templateTypes = PromptHelper::multisearchOrFlag(
            $assoc_args,
            'template-types',
            'Select template types (optional)',
            fn (string $_): array => $wpData->getTemplateTypes(),
        );

        $inserter = PromptHelper::confirmOrFlag($assoc_args, 'inserter', 'Show pattern in inserter?', true);

        $source = \WP_CLI\Utils\get_flag_value($assoc_args, 'source');

        if ($source === null && PromptHelper::isInteractive($assoc_args)) {
            $source = PromptHelper::suggest('Enter pattern source', ['theme', 'plugin'], default: 'theme');
        }

        $source = (string) ($source ?? 'theme');

        $keywords = [];

        if (PromptHelper::isInteractive($assoc_args)) {
            $editKeywords = PromptHelper::confirm('Add keywords?', false);

            if ($editKeywords) {
                $keywordsInput = PromptHelper::textOrFlag($assoc_args, 'keywords', 'Enter keywords (comma-separated)', '', false);
                $keywords = array_values(array_filter(array_map('trim', explode(',', $keywordsInput))));
            }
        }

        $textdomain = $themeResolver->getTextDomain();
        $content = $generator->generate(
            $textdomain,
            $title,
            $slug,
            $description,
            $categories,
            $keywords,
            $blockTypes,
            $postTypes,
            $templateTypes,
            $inserter,
            $source,
        );

        $targetFile = $themeResolver->getPatternsPath() . '/' . $slug . '.php';
        FileGuard::assertCanWrite($targetFile, $assoc_args);

        if (file_put_contents($targetFile, $content) === false) {
            \WP_CLI::error('Failed to write pattern file.');
        }

        \WP_CLI::success(sprintf('Pattern created: %s', $targetFile));
    }
}
