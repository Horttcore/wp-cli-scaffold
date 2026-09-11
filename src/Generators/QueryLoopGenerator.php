<?php

declare(strict_types=1);

namespace RalfHortt\WpCliScaffold\Generators;

final class QueryLoopGenerator
{
    public function generate(
        string $textdomain,
        string $title,
        string $slug,
        string $description,
        string $postType,
        int $perPage,
        bool $includeFeaturedImage,
        bool $includeExcerpt,
        bool $includePagination,
    ): string {
        $pattern = "<?php\n";
        $pattern .= "/**\n";
        $pattern .= ' * Title: ' . $title . "\n";
        $pattern .= ' * Slug: ' . $textdomain . '/' . $slug . "\n";
        $pattern .= " * Categories: query\n";
        $pattern .= " * Block Types: core/query\n";

        if ($description !== '') {
            $pattern .= ' * Description: ' . $description . "\n";
        }

        $pattern .= " */\n";
        $pattern .= "?>\n\n";

        $pattern .= '<!-- wp:query {"query":{"perPage":' . $perPage . ',"pages":0,"offset":0,"postType":"' . esc_attr($postType) . '","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"","inherit":true,"taxQuery":null,"parents":[]},"align":"full","layout":{"type":"default"}} -->' . "\n";
        $pattern .= '<div class="wp-block-query alignfull">' . "\n";
        $pattern .= "\t<!-- wp:post-template {\"align\":\"full\",\"layout\":{\"type\":\"default\"}} -->\n";
        $pattern .= "\t\t<!-- wp:group {\"align\":\"full\",\"layout\":{\"type\":\"constrained\"}} -->\n";
        $pattern .= "\t\t<div class=\"wp-block-group alignfull\">\n";

        if ($includeFeaturedImage) {
            $pattern .= "\t\t\t<!-- wp:post-featured-image {\"isLink\":true,\"aspectRatio\":\"3/2\"} /-->\n";
        }

        $pattern .= "\t\t\t<!-- wp:post-title {\"isLink\":true} /-->\n";

        if ($includeExcerpt) {
            $pattern .= "\t\t\t<!-- wp:post-excerpt /-->\n";
        }

        $pattern .= "\t\t\t<!-- wp:post-date {\"isLink\":true} /-->\n";
        $pattern .= "\t\t</div>\n";
        $pattern .= "\t\t<!-- /wp:group -->\n";
        $pattern .= "\t<!-- /wp:post-template -->\n";

        if ($includePagination) {
            $pattern .= "\t<!-- wp:query-pagination {\"paginationArrow\":\"arrow\",\"align\":\"wide\",\"layout\":{\"type\":\"flex\",\"justifyContent\":\"space-between\"}} -->\n";
            $pattern .= "\t\t<!-- wp:query-pagination-previous /-->\n";
            $pattern .= "\t\t<!-- wp:query-pagination-numbers /-->\n";
            $pattern .= "\t\t<!-- wp:query-pagination-next /-->\n";
            $pattern .= "\t<!-- /wp:query-pagination -->\n";
        }

        $pattern .= "\t<!-- wp:query-no-results -->\n";
        $pattern .= "\t\t<!-- wp:paragraph -->\n";
        $pattern .= "\t\t<p>No posts found.</p>\n";
        $pattern .= "\t\t<!-- /wp:paragraph -->\n";
        $pattern .= "\t<!-- /wp:query-no-results -->\n";
        $pattern .= "</div>\n";
        $pattern .= "<!-- /wp:query -->\n";

        return $pattern;
    }
}
