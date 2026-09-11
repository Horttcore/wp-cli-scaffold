<?php

declare(strict_types=1);

namespace RalfHortt\WpCliScaffold\Commands;

use RalfHortt\WpCliScaffold\Generators\StyleVariationGenerator;
use RalfHortt\WpCliShared\ThemeToken;
use RalfHortt\WpCliShared\Support\FileGuard;
use RalfHortt\WpCliShared\Support\PromptHelper;
use RalfHortt\WpCliShared\Support\Slug;
use RalfHortt\WpCliShared\ThemeResolver;
use RalfHortt\WpCliShared\WordPressData;

final class StyleVariation_Command
{
    /**
     * Scaffold a style variation file in styles/.
     *
     * ## OPTIONS
     *
     * [<slug>]
     * : Style variation slug.
     *
     * [--slug=<slug>]
     * : Style variation slug (alternative to positional argument).
     *
     * [--theme=<theme>]
     * : Theme stylesheet slug override.
     *
     * [--title=<title>]
     * : Human title for the variation.
     *
     * [--description=<description>]
     * : Optional variation description.
     *
     * [--primary=<primary>]
     * : Primary color value.
     *
     * [--secondary=<secondary>]
     * : Secondary color value.
     *
     * [--background=<background>]
     * : Background color value.
     *
     * [--text=<text>]
     * : Text color value.
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
        $generator = new StyleVariationGenerator();

        $themeResolver->warnIfNotBlockTheme();

        if (isset($args[0])) {
            $slug = Slug::sanitize((string) $args[0]);
        } else {
            $slugOptions = $this->getStyleVariationSuggestions($themeResolver);
            $defaultSlug = array_key_exists('default', $slugOptions)
                ? 'default'
                : (array_key_first($slugOptions) ?? 'default');

            $slug = Slug::sanitize(PromptHelper::suggestOrFlag(
                $assoc_args,
                'slug',
                'Select or enter style variation slug',
                $slugOptions,
                $defaultSlug,
                allowFreeText: true,
            ));
        }

        $titleFlag = \WP_CLI\Utils\get_flag_value($assoc_args, 'title');
        $title = is_string($titleFlag) && $titleFlag !== ''
            ? $titleFlag
            : ucwords(str_replace('-', ' ', $slug));

        $description = (string) (\WP_CLI\Utils\get_flag_value($assoc_args, 'description', '') ?? '');
        $colorGuesses = $this->guessColorDefaults($wpData);
        $colorSuggestions = $this->getColorSuggestions($wpData);

        $primary = $this->resolveOptionalColor(
            $assoc_args,
            'primary',
            'Primary color (optional)',
            $colorSuggestions,
            $colorGuesses['primary']
        );
        $secondary = $this->resolveOptionalColor(
            $assoc_args,
            'secondary',
            'Secondary color (optional)',
            $colorSuggestions,
            $colorGuesses['secondary']
        );
        $background = $this->resolveOptionalColor(
            $assoc_args,
            'background',
            'Background color (optional)',
            $colorSuggestions,
            $colorGuesses['background']
        );
        $text = $this->resolveOptionalColor(
            $assoc_args,
            'text',
            'Text color (optional)',
            $colorSuggestions,
            $colorGuesses['text']
        );

        $content = $generator->generate($title, $slug, $description, $primary, $secondary, $background, $text);

        $targetFile = $themeResolver->getStylesPath() . '/' . $slug . '.json';
        FileGuard::assertCanWrite($targetFile, $assoc_args);

        if (file_put_contents($targetFile, $content) === false) {
            \WP_CLI::error('Failed to write style variation file.');
        }

        \WP_CLI::success(sprintf('Style variation created: %s', $targetFile));
    }

    private function nullableString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }

    /**
     * @return array<string, string>
     */
    private function getStyleVariationSuggestions(ThemeResolver $themeResolver): array
    {
        $suggestions = [
            'default' => 'Default (default)',
            'light' => 'Light (light)',
            'dark' => 'Dark (dark)',
            'ocean' => 'Ocean (ocean)',
            'forest' => 'Forest (forest)',
        ];

        $stylesPath = $themeResolver->getStylesPath();
        $entries = @scandir($stylesPath);

        if ($entries === false) {
            return $suggestions;
        }

        foreach ($entries as $entry) {
            if (! is_string($entry) || ! str_ends_with($entry, '.json')) {
                continue;
            }

            $slug = basename($entry, '.json');
            $slug = sanitize_title($slug);

            if ($slug === '') {
                continue;
            }

            $suggestions[$slug] = sprintf('%s (%s)', ucwords(str_replace('-', ' ', $slug)), $slug);
        }

        ksort($suggestions);

        return $suggestions;
    }

    /**
     * @return array<string, string>
     */
    private function getColorSuggestions(WordPressData $wpData): array
    {
        $suggestions = [];

        foreach ($wpData->getTokensByType('color') as $token) {
            foreach ($token->suggestionForms() as $form) {
                $suggestions[$form] = $form;
            }
        }

        ksort($suggestions);

        return $suggestions;
    }

    /**
     * @return array{primary: string, secondary: string, background: string, text: string}
     */
    private function guessColorDefaults(WordPressData $wpData): array
    {
        $defaults = [
            'primary' => '',
            'secondary' => '',
            'background' => '',
            'text' => '',
        ];

        $tokens = $wpData->getTokensByType('color');
        $bySlug = [];

        foreach ($tokens as $token) {
            $bySlug[strtolower($token->slug)] = $token;
        }

        $defaults['primary'] = $this->firstMatchingColor($bySlug, ['primary', 'brand', 'accent']);
        $defaults['secondary'] = $this->firstMatchingColor($bySlug, ['secondary', 'muted', 'subtle']);
        $defaults['background'] = $this->firstMatchingColor($bySlug, ['base', 'background', 'surface', 'light']);
        $defaults['text'] = $this->firstMatchingColor($bySlug, ['contrast', 'foreground', 'text', 'dark']);

        return $defaults;
    }

    /**
     * @param  array<string, ThemeToken>  $tokens
     * @param  array<int, string>  $needles
     */
    private function firstMatchingColor(array $tokens, array $needles): string
    {
        foreach ($needles as $needle) {
            foreach ($tokens as $slug => $token) {
                if (str_contains($slug, $needle)) {
                    return $token->resolved;
                }
            }
        }

        return '';
    }

    /**
     * @param  array<string, mixed>  $assoc_args
     * @param  array<string, string>  $suggestions
     */
    private function resolveOptionalColor(
        array $assoc_args,
        string $flag,
        string $label,
        array $suggestions,
        string $default = '',
    ): ?string {
        $value = $this->nullableString(\WP_CLI\Utils\get_flag_value($assoc_args, $flag));

        if ($value !== null) {
            return $value;
        }

        if (! PromptHelper::isInteractive($assoc_args)) {
            return $default !== '' ? $default : null;
        }

        if (! PromptHelper::confirm(sprintf('Set %s?', $flag), $default !== '')) {
            return null;
        }

        return $this->nullableString(PromptHelper::suggest(
            $label,
            $suggestions,
            $default,
            allowFreeText: true,
        ));
    }
}
