<?php

declare(strict_types=1);

namespace RalfHortt\WpCliScaffold\Commands;

use RalfHortt\WpCliScaffold\Generators\QueryLoopGenerator;
use RalfHortt\WpCliShared\Support\FileGuard;
use RalfHortt\WpCliShared\Support\PromptHelper;
use RalfHortt\WpCliShared\Support\Slug;
use RalfHortt\WpCliShared\ThemeResolver;
use RalfHortt\WpCliShared\WordPressData;

final class QueryLoop_Command
{
    /**
     * Scaffold a query loop pattern in the active theme.
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
     * [--post-type=<post-type>]
     * : Post type for the query.
     *
     * [--per-page=<per-page>]
     * : Posts per page.
     *
     * [--featured-image]
     * : Include featured image.
     *
     * [--no-featured-image]
     * : Exclude featured image.
     *
     * [--excerpt]
     * : Include excerpt.
     *
     * [--no-excerpt]
     * : Exclude excerpt.
     *
     * [--pagination]
     * : Include pagination.
     *
     * [--no-pagination]
     * : Exclude pagination.
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
        $generator = new QueryLoopGenerator();

        $themeResolver->warnIfNotBlockTheme();

        $title = PromptHelper::textOrFlag($assoc_args, 'title', 'Enter query loop title');
        $slug = 'query-loop-' . Slug::fromTitle($title);
        $description = \WP_CLI\Utils\get_flag_value($assoc_args, 'description', '') ?? '';

        $postTypes = $wpData->getQueryLoopPostTypes();
        $defaultPostType = array_key_exists('post', $postTypes)
            ? 'post'
            : (array_key_first($postTypes) ?? 'post');

        $postType = PromptHelper::suggestOrFlag(
            $assoc_args,
            'post-type',
            'Select post type',
            $postTypes,
            $defaultPostType,
        );

        $perPageFlag = \WP_CLI\Utils\get_flag_value($assoc_args, 'per-page');

        if ($perPageFlag !== null) {
            $perPage = max(1, (int) $perPageFlag);
        } elseif (! PromptHelper::isInteractive($assoc_args)) {
            $perPage = $wpData->getPostsPerPage();
        } else {
            $perPageInput = PromptHelper::textOrFlag(
                $assoc_args,
                'per-page',
                'Posts per page',
                (string) $wpData->getPostsPerPage(),
            );
            $perPage = max(1, (int) $perPageInput);
        }

        $includeFeaturedImage = $this->resolveBoolFlag($assoc_args, 'featured-image', 'no-featured-image', true);
        $includeExcerpt = $this->resolveBoolFlag($assoc_args, 'excerpt', 'no-excerpt', true);
        $includePagination = $this->resolveBoolFlag($assoc_args, 'pagination', 'no-pagination', true);

        $textdomain = $themeResolver->getTextDomain();
        $content = $generator->generate(
            $textdomain,
            $title,
            $slug,
            $description,
            $postType,
            $perPage,
            $includeFeaturedImage,
            $includeExcerpt,
            $includePagination,
        );

        $targetFile = $themeResolver->getPatternsPath() . '/' . $slug . '.php';
        FileGuard::assertCanWrite($targetFile, $assoc_args);

        if (file_put_contents($targetFile, $content) === false) {
            \WP_CLI::error('Failed to write query loop pattern file.');
        }

        \WP_CLI::success(sprintf('Query loop pattern created: %s', $targetFile));
    }

    /**
     * @param  array<string, mixed>  $assoc_args
     */
    private function resolveBoolFlag(array $assoc_args, string $yesFlag, string $noFlag, bool $default): bool
    {
        if (\WP_CLI\Utils\get_flag_value($assoc_args, $yesFlag)) {
            return true;
        }

        if (\WP_CLI\Utils\get_flag_value($assoc_args, $noFlag)) {
            return false;
        }

        if (! PromptHelper::isInteractive($assoc_args)) {
            return $default;
        }

        return PromptHelper::confirmOrFlag($assoc_args, $yesFlag, 'Include ' . str_replace('-', ' ', $yesFlag) . '?', $default);
    }
}
