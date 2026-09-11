<?php

declare(strict_types=1);

namespace RalfHortt\WpCliScaffold\Commands;

use RalfHortt\WpCliScaffold\Generators\TemplateGenerator;
use RalfHortt\WpCliShared\Support\FileGuard;
use RalfHortt\WpCliShared\Support\PromptHelper;
use RalfHortt\WpCliShared\Support\Slug;
use RalfHortt\WpCliShared\ThemeResolver;
use RalfHortt\WpCliShared\WordPressData;

final class Template_Command
{
    /**
     * Scaffold a block theme template file.
     *
     * ## OPTIONS
     *
     * [<slug>]
     * : Template slug (e.g. single, page, archive, home, index, 404).
     *
     * [--slug=<slug>]
     * : Template slug (alternative to positional argument).
     *
     * [--theme=<theme>]
     * : Theme stylesheet slug override.
     *
     * [--title=<title>]
     * : Template title used in starter content.
     *
     * [--description=<description>]
     * : Optional description paragraph.
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
        $generator = new TemplateGenerator();

        $themeResolver->warnIfNotBlockTheme();

        if (isset($args[0])) {
            $slug = Slug::sanitize((string) $args[0]);
        } else {
            $templateTypes = $wpData->getTemplateTypes();
            $defaultTemplate = array_key_exists('index', $templateTypes)
                ? 'index'
                : (array_key_first($templateTypes) ?? 'index');

            $slug = Slug::sanitize(PromptHelper::suggestOrFlag(
                $assoc_args,
                'slug',
                'Select or enter template slug',
                $templateTypes,
                $defaultTemplate,
                allowFreeText: true,
            ));
        }

        $titleFlag = \WP_CLI\Utils\get_flag_value($assoc_args, 'title');
        $title = is_string($titleFlag) && $titleFlag !== ''
            ? $titleFlag
            : ucwords(str_replace('-', ' ', $slug));

        $description = (string) (\WP_CLI\Utils\get_flag_value($assoc_args, 'description', '') ?? '');
        $content = $generator->generate($title, $slug, $description);

        $targetFile = $themeResolver->getTemplatesPath() . '/' . $slug . '.html';
        FileGuard::assertCanWrite($targetFile, $assoc_args);

        if (file_put_contents($targetFile, $content) === false) {
            \WP_CLI::error('Failed to write template file.');
        }

        \WP_CLI::success(sprintf('Template created: %s', $targetFile));
    }
}
