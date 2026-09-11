<?php

declare(strict_types=1);

namespace RalfHortt\WpCliScaffold\Generators;

final class StarterContentGenerator
{
    /**
     * @param  array<int, string>  $postTypes
     */
    public function generate(
        string $textdomain,
        string $title,
        string $slug,
        string $description,
        array $postTypes,
        string $markup,
    ): string {
        $pattern = "<?php\n";
        $pattern .= "/**\n";
        $pattern .= ' * Title: ' . $title . "\n";
        $pattern .= ' * Slug: ' . $textdomain . '/' . $slug . "\n";

        if ($description !== '') {
            $pattern .= ' * Description: ' . $description . "\n";
        }

        $pattern .= " * Keywords: starter\n";
        $pattern .= " * Block Types: core/post-content\n";
        $pattern .= ' * Post Types: ' . implode(', ', $postTypes) . "\n";
        $pattern .= " * Viewport width: 1400\n";
        $pattern .= " */\n";
        $pattern .= "?>\n\n";

        if (trim($markup) === '') {
            $pattern .= "<!-- wp:group -->\n";
            $pattern .= "<div class=\"wp-block-group\">\n";
            $pattern .= "\t<!-- wp:heading -->\n";
            $pattern .= "\t<h2 class=\"wp-block-heading\">" . esc_html($title) . "</h2>\n";
            $pattern .= "\t<!-- /wp:heading -->\n";
            $pattern .= "</div>\n";
            $pattern .= "<!-- /wp:group -->\n";
        } else {
            $pattern .= trim($markup) . "\n";
        }

        return $pattern;
    }
}
