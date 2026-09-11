<?php

declare(strict_types=1);

namespace RalfHortt\WpCliScaffold\Commands;

use RalfHortt\WpCliScaffold\Generators\StarterContentGenerator;
use RalfHortt\WpCliShared\Support\FileGuard;
use RalfHortt\WpCliShared\Support\PromptHelper;
use RalfHortt\WpCliShared\Support\Slug;
use RalfHortt\WpCliShared\ThemeResolver;
use RalfHortt\WpCliShared\WordPressData;

final class StarterContent_Command
{
    /**
     * Scaffold a starter content pattern in the active theme.
     *
     * ## OPTIONS
     *
     * [--theme=<theme>]
     * : Theme stylesheet slug override.
     *
     * [--title=<title>]
     * : Starter content title.
     *
     * [--description=<description>]
     * : Description.
     *
     * [--post-types=<post-types>]
     * : Comma-separated post types.
     *
     * [--content=<content>]
     * : Block markup content.
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
        $generator = new StarterContentGenerator();

        $themeResolver->warnIfNotBlockTheme();

        $title = PromptHelper::textOrFlag($assoc_args, 'title', 'Enter starter content name');
        $slug = Slug::fromTitle($title);
        $description = \WP_CLI\Utils\get_flag_value($assoc_args, 'description', '') ?? '';

        $defaultPostTypes = array_key_exists('page', $wpData->getPostTypes()) ? ['page'] : [];

        $postTypes = PromptHelper::multisearchOrFlag(
            $assoc_args,
            'post-types',
            'Select post types',
            fn (string $_): array => $wpData->getPostTypes(),
            $defaultPostTypes,
        );

        if ($postTypes === []) {
            \WP_CLI::error('At least one post type is required.');
        }

        $markup = \WP_CLI\Utils\get_flag_value($assoc_args, 'content', '') ?? '';

        if ($markup === '' && PromptHelper::isInteractive($assoc_args)) {
            $markup = PromptHelper::textarea(
                'Enter starter content (optional)',
                'Paste block markup here. Leave empty to generate a basic structure.'
            );
        }

        $textdomain = $themeResolver->getTextDomain();
        $content = $generator->generate($textdomain, $title, $slug, $description, $postTypes, $markup);

        $targetFile = $themeResolver->getPatternsPath() . '/' . $slug . '.php';
        FileGuard::assertCanWrite($targetFile, $assoc_args);

        if (file_put_contents($targetFile, $content) === false) {
            \WP_CLI::error('Failed to write starter content pattern file.');
        }

        \WP_CLI::success(sprintf('Starter content pattern created: %s', $targetFile));
    }
}
