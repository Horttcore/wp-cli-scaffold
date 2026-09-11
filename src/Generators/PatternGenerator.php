<?php

declare(strict_types=1);

namespace RalfHortt\WpCliScaffold\Generators;

final class PatternGenerator
{
    /**
     * @param  array<int, string>  $categories
     * @param  array<int, string>  $keywords
     * @param  array<int, string>  $blockTypes
     * @param  array<int, string>  $postTypes
     * @param  array<int, string>  $templateTypes
     */
    public function generate(
        string $textdomain,
        string $title,
        string $slug,
        string $description,
        array $categories,
        array $keywords,
        array $blockTypes,
        array $postTypes,
        array $templateTypes,
        bool $inserter,
        string $source,
    ): string {
        $content = "<?php\n";
        $content .= "/**\n";
        $content .= ' * Title: ' . $title . "\n";
        $content .= ' * Slug: ' . $textdomain . '/' . $slug . "\n";

        if ($description !== '') {
            $content .= ' * Description: ' . $description . "\n";
        }

        if ($categories !== []) {
            $content .= ' * Categories: ' . implode(', ', $categories) . "\n";
        }

        if ($keywords !== []) {
            $content .= ' * Keywords: ' . implode(', ', $keywords) . "\n";
        }

        if ($blockTypes !== []) {
            $content .= ' * Block Types: ' . implode(', ', $blockTypes) . "\n";
        }

        if ($postTypes !== []) {
            $content .= ' * Post Types: ' . implode(', ', $postTypes) . "\n";
        }

        if ($templateTypes !== []) {
            $content .= ' * Template Types: ' . implode(', ', $templateTypes) . "\n";
        }

        if (! $inserter) {
            $content .= " * Inserter: false\n";
        }

        if ($source !== '') {
            $content .= ' * Source: ' . $source . "\n";
        }

        $content .= " */\n";
        $content .= "?>\n\n";
        $content .= "<!-- wp:group -->\n";
        $content .= "<div class=\"wp-block-group\">\n";
        $content .= "\t<!-- wp:heading -->\n";
        $content .= "\t<h2 class=\"wp-block-heading\">" . esc_html($title) . "</h2>\n";
        $content .= "\t<!-- /wp:heading -->\n\n";

        if ($description !== '') {
            $content .= "\t<!-- wp:paragraph -->\n";
            $content .= "\t<p>" . esc_html($description) . "</p>\n";
            $content .= "\t<!-- /wp:paragraph -->\n";
        }

        $content .= "</div>\n";
        $content .= "<!-- /wp:group -->\n";

        return $content;
    }
}
