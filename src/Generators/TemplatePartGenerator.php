<?php

declare(strict_types=1);

namespace RalfHortt\WpCliScaffold\Generators;

final class TemplatePartGenerator
{
    public function generate(string $area, string $title, string $description): string
    {
        $markup = $this->defaultMarkupForArea($area, $title);

        if ($description === '') {
            return $markup;
        }

        $descriptionMarkup = "<!-- wp:paragraph -->\n<p>" . esc_html($description) . "</p>\n<!-- /wp:paragraph -->\n\n";

        return $descriptionMarkup . $markup;
    }

    private function defaultMarkupForArea(string $area, string $title): string
    {
        $safeTitle = esc_html($title);

        if ($area === 'header') {
            return <<<HTML
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|30","bottom":"var:preset|spacing|30"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull" style="padding-top:var(--wp--preset--spacing--30);padding-bottom:var(--wp--preset--spacing--30)">
	<!-- wp:site-title {"level":0} /-->
	<!-- wp:navigation {"layout":{"type":"flex","justifyContent":"right"}} /-->
</div>
<!-- /wp:group -->
HTML;
        }

        if ($area === 'footer') {
            return <<<HTML
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|40"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull" style="padding-top:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--40)">
	<!-- wp:paragraph {"align":"center"} -->
	<p class="has-text-align-center">{$safeTitle}</p>
	<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
HTML;
        }

        if ($area === 'sidebar') {
            return <<<HTML
<!-- wp:group {"layout":{"type":"constrained"}} -->
<div class="wp-block-group">
	<!-- wp:heading {"level":3} -->
	<h3>{$safeTitle}</h3>
	<!-- /wp:heading -->

	<!-- wp:latest-posts {"postsToShow":5} /-->
</div>
<!-- /wp:group -->
HTML;
        }

        return <<<HTML
<!-- wp:group {"layout":{"type":"constrained"}} -->
<div class="wp-block-group">
	<!-- wp:heading {"level":3} -->
	<h3>{$safeTitle}</h3>
	<!-- /wp:heading -->
</div>
<!-- /wp:group -->
HTML;
    }
}
