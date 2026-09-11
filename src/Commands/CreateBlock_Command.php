<?php

declare(strict_types=1);

namespace RalfHortt\WpCliScaffold\Commands;

use RalfHortt\WpCliScaffold\Support\CreateBlockRunner;
use RalfHortt\WpCliShared\PluginResolver;
use RalfHortt\WpCliShared\Support\PromptHelper;
use RalfHortt\WpCliShared\Support\Slug;
use RalfHortt\WpCliShared\ThemeResolver;
use RalfHortt\WpCliShared\WordPressData;

final class CreateBlock_Command
{
    /**
     * Scaffold a block using @wordpress/create-block.
     *
     * ## OPTIONS
     *
     * [<slug>]
     * : Block slug.
     *
     * [--destination=<destination>]
     * : Destination: theme or plugin.
     * ---
     * default: theme
     * options:
     *   - theme
     *   - plugin
     * ---
     *
     * [--plugin=<plugin>]
     * : Existing plugin slug when adding a block to a plugin.
     *
     * [--theme=<theme>]
     * : Theme stylesheet slug override.
     *
     * [--title=<title>]
     * : Block title.
     *
     * [--category=<category>]
     * : Block category.
     *
     * [--description=<description>]
     * : Short description.
     *
     * [--dynamic]
     * : Create a dynamic block variant.
     *
     * [--force]
     * : Overwrite existing files.
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
        $pluginResolver = new PluginResolver();
        $wpData = new WordPressData(is_string($themeSlug) ? $themeSlug : null);
        $runner = new CreateBlockRunner();

        $title = PromptHelper::textOrFlag($assoc_args, 'title', 'Enter block title');
        $slug = isset($args[0]) ? Slug::sanitize((string) $args[0]) : Slug::fromTitle($title);

        if ($slug === '') {
            \WP_CLI::error('Block slug is required.');
        }

        $destinationFlag = \WP_CLI\Utils\get_flag_value($assoc_args, 'destination');

        if (is_string($destinationFlag) && $destinationFlag !== '') {
            $destination = $destinationFlag;
        } elseif (PromptHelper::isInteractive($assoc_args)) {
            $destination = PromptHelper::suggest(
                'Select destination',
                ['theme', 'plugin'],
                default: 'theme'
            );
        } else {
            $destination = 'theme';
        }

        if (! in_array($destination, ['theme', 'plugin'], true)) {
            \WP_CLI::error('Invalid --destination. Use theme or plugin.');
        }

        $isDynamic = PromptHelper::confirmOrFlag($assoc_args, 'dynamic', 'Create a dynamic block?', false);

        $description = \WP_CLI\Utils\get_flag_value($assoc_args, 'description', '');
        $category = \WP_CLI\Utils\get_flag_value($assoc_args, 'category');
        $blockCategories = $wpData->getBlockCategories();

        if ($destination === 'theme') {
            $textdomain = $themeResolver->getTextDomain();
            $defaultCategory = array_key_exists('theme', $blockCategories)
                ? 'theme'
                : (array_key_first($blockCategories) ?? 'theme');

            if (! is_string($category) || $category === '') {
                $category = PromptHelper::isInteractive($assoc_args)
                    ? PromptHelper::suggest('Select block category', $blockCategories, default: $defaultCategory)
                    : $defaultCategory;
            }

            $cwd = $themeResolver->getThemePath();

            $options = [
                'no-plugin' => 'true',
                'textdomain' => $textdomain,
                'target_dir' => 'blocks/' . $slug,
                'namespace' => $textdomain,
                'title' => $title,
                'category' => $category,
            ];

            if ($description !== '') {
                $options['short-description'] = $description;
            }

            if ($isDynamic) {
                $options['variant'] = 'dynamic';
            }

            $runner->run($slug, $cwd, $options, $assoc_args);

            return;
        }

        $pluginFlag = \WP_CLI\Utils\get_flag_value($assoc_args, 'plugin');

        if ($pluginFlag) {
            $pluginSlug = Slug::sanitize((string) $pluginFlag);
            $textdomain = $pluginResolver->getPluginTextDomain($pluginSlug);
            $cwd = $pluginResolver->getPluginPath($pluginSlug);

            $defaultCategory = array_key_first($blockCategories) ?? 'widgets';

            if (! is_string($category) || $category === '') {
                $category = PromptHelper::isInteractive($assoc_args)
                    ? PromptHelper::suggest('Select block category', $blockCategories, default: $defaultCategory)
                    : $defaultCategory;
            }

            $options = [
                'no-plugin' => 'true',
                'textdomain' => $textdomain,
                'target_dir' => 'blocks/' . $slug,
                'namespace' => $pluginSlug . '/' . $slug,
                'title' => $title,
                'category' => $category,
            ];

            if ($description !== '') {
                $options['short-description'] = $description;
            }

            if ($isDynamic) {
                $options['variant'] = 'dynamic';
            }

            $runner->run($slug, $cwd, $options, $assoc_args);

            return;
        }

        if (! PromptHelper::isInteractive($assoc_args)) {
            \WP_CLI::error('New plugin mode requires --plugin for existing plugin or interactive selection.');
        }

        $mode = PromptHelper::suggest(
            'New plugin or add to existing plugin?',
            ['new', 'existing'],
            default: 'new'
        );

        if ($mode === 'existing') {
            $pluginSlug = PromptHelper::suggest(
                'Select plugin',
                $wpData->getInstalledPlugins()
            );
            $textdomain = $pluginResolver->getPluginTextDomain($pluginSlug);
            $cwd = $pluginResolver->getPluginPath($pluginSlug);

            $defaultCategory = array_key_first($blockCategories) ?? 'widgets';

            if (! is_string($category) || $category === '') {
                $category = PromptHelper::suggest('Select block category', $blockCategories, default: $defaultCategory);
            }

            $options = [
                'no-plugin' => 'true',
                'textdomain' => $textdomain,
                'target_dir' => 'blocks/' . $slug,
                'namespace' => $pluginSlug . '/' . $slug,
                'title' => $title,
                'category' => $category,
            ];

            if ($description !== '') {
                $options['short-description'] = $description;
            }

            if ($isDynamic) {
                $options['variant'] = 'dynamic';
            }

            $runner->run($slug, $cwd, $options, $assoc_args);

            return;
        }

        $textdomain = $slug;
        $cwd = $pluginResolver->getPluginsPath();
        $defaultCategory = array_key_first($blockCategories) ?? 'widgets';

        if (! is_string($category) || $category === '') {
            $category = PromptHelper::isInteractive($assoc_args)
                ? PromptHelper::suggest('Select block category', $blockCategories, default: $defaultCategory)
                : $defaultCategory;
        }

        $options = [
            'textdomain' => $textdomain,
            'namespace' => $slug,
            'title' => $title,
            'category' => $category,
        ];

        if ($description !== '') {
            $options['short-description'] = $description;
        }

        if ($isDynamic) {
            $options['variant'] = 'dynamic';
        }

        $runner->run($slug, $cwd, $options, $assoc_args);
    }
}
