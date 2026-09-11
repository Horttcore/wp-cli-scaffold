<?php

declare(strict_types=1);

namespace RalfHortt\WpCliScaffold\Commands;

use RalfHortt\WpCliScaffold\Generators\TemplatePartGenerator;
use RalfHortt\WpCliShared\Support\FileGuard;
use RalfHortt\WpCliShared\Support\PromptHelper;
use RalfHortt\WpCliShared\Support\Slug;
use RalfHortt\WpCliShared\ThemeResolver;
use RalfHortt\WpCliShared\WordPressData;

final class TemplatePart_Command
{
    /**
     * Scaffold a block theme template part file.
     *
     * ## OPTIONS
     *
     * [<slug>]
     * : Template part slug (e.g. header, footer, sidebar).
     *
     * [--slug=<slug>]
     * : Template part slug (alternative to positional argument).
     *
     * [--theme=<theme>]
     * : Theme stylesheet slug override.
     *
     * [--title=<title>]
     * : Human title used in markup.
     *
     * [--description=<description>]
     * : Optional description paragraph.
     *
     * [--area=<area>]
     * : Template part area.
     * ---
     * default: uncategorized
     * options:
     *   - header
     *   - footer
     *   - sidebar
     *   - uncategorized
     * ---
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
        $generator = new TemplatePartGenerator();

        $themeResolver->warnIfNotBlockTheme();

        $templatePartAreas = $wpData->getTemplatePartAreas();

        if (isset($args[0])) {
            $slug = Slug::sanitize((string) $args[0]);
        } else {
            $defaultSlug = array_key_exists('header', $templatePartAreas)
                ? 'header'
                : (array_key_first($templatePartAreas) ?? 'header');

            $slug = Slug::sanitize(PromptHelper::suggestOrFlag(
                $assoc_args,
                'slug',
                'Select or enter template part slug',
                $templatePartAreas,
                $defaultSlug,
                allowFreeText: true,
            ));
        }

        $defaultArea = array_key_exists($slug, $templatePartAreas)
            ? $slug
            : (array_key_exists('uncategorized', $templatePartAreas)
                ? 'uncategorized'
                : (array_key_first($templatePartAreas) ?? 'uncategorized'));

        $area = PromptHelper::suggestOrFlag(
            $assoc_args,
            'area',
            'Select template part area',
            $templatePartAreas,
            $defaultArea,
        );

        $titleFlag = \WP_CLI\Utils\get_flag_value($assoc_args, 'title');
        $title = is_string($titleFlag) && $titleFlag !== ''
            ? $titleFlag
            : ucwords(str_replace('-', ' ', $slug));

        $description = (string) (\WP_CLI\Utils\get_flag_value($assoc_args, 'description', '') ?? '');
        $content = $generator->generate($area, $title, $description);

        $targetFile = $themeResolver->getPartsPath() . '/' . $slug . '.html';
        FileGuard::assertCanWrite($targetFile, $assoc_args);

        if (file_put_contents($targetFile, $content) === false) {
            \WP_CLI::error('Failed to write template part file.');
        }

        \WP_CLI::success(sprintf('Template part created: %s', $targetFile));
    }
}
