<?php

declare(strict_types=1);

namespace RalfHortt\WpCliScaffold\Generators;

final class TemplateGenerator
{
    public function generate(string $title, string $slug, string $description): string
    {
        $markup = $this->defaultMarkupForTemplate($slug);

        if ($description !== '') {
            $descriptionMarkup = "<!-- wp:paragraph -->\n<p>" . esc_html($description) . "</p>\n<!-- /wp:paragraph -->\n\n";
            $markup = $descriptionMarkup . $markup;
        }

        return $markup;
    }

    private function defaultMarkupForTemplate(string $slug): string
    {
        if ($slug === '404') {
            return <<<HTML
<!-- wp:template-part {"slug":"header","tagName":"header"} /-->

<!-- wp:group {"tagName":"main","layout":{"type":"constrained"}} -->
<main class="wp-block-group">
	<!-- wp:heading {"level":1} -->
	<h1>Page not found</h1>
	<!-- /wp:heading -->

	<!-- wp:paragraph -->
	<p>It looks like nothing was found at this location.</p>
	<!-- /wp:paragraph -->

	<!-- wp:search {"label":"Search","showLabel":false,"buttonText":"Search"} /-->
</main>
<!-- /wp:group -->

<!-- wp:template-part {"slug":"footer","tagName":"footer"} /-->
HTML;
        }

        $usesQuery = in_array($slug, ['home', 'archive', 'search', 'index'], true);

        if ($usesQuery) {
            return <<<HTML
<!-- wp:template-part {"slug":"header","tagName":"header"} /-->

<!-- wp:group {"tagName":"main","layout":{"type":"constrained"}} -->
<main class="wp-block-group">
	<!-- wp:query-title {"type":"archive"} /-->

	<!-- wp:query {"query":{"perPage":10,"pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","inherit":true},"layout":{"type":"default"}} -->
	<div class="wp-block-query">
		<!-- wp:post-template -->
			<!-- wp:post-title {"isLink":true} /-->
			<!-- wp:post-excerpt /-->
			<!-- wp:separator /-->
		<!-- /wp:post-template -->

		<!-- wp:query-pagination {"layout":{"type":"flex","justifyContent":"space-between"}} -->
			<!-- wp:query-pagination-previous /-->
			<!-- wp:query-pagination-numbers /-->
			<!-- wp:query-pagination-next /-->
		<!-- /wp:query-pagination -->
	</div>
	<!-- /wp:query -->
</main>
<!-- /wp:group -->

<!-- wp:template-part {"slug":"footer","tagName":"footer"} /-->
HTML;
        }

        $genericTitle = ucwords(str_replace('-', ' ', $slug));
        $singleTitle = in_array($slug, ['single', 'page'], true)
            ? '<!-- wp:post-title {"level":1} /-->'
            : '<!-- wp:heading {"level":1} --><h1>' . esc_html($genericTitle) . '</h1><!-- /wp:heading -->';

        $contentBlock = in_array($slug, ['single', 'page'], true)
            ? '<!-- wp:post-content {"layout":{"type":"constrained"}} /-->'
            : '<!-- wp:paragraph --><p>Add your content here.</p><!-- /wp:paragraph -->';

        return <<<HTML
<!-- wp:template-part {"slug":"header","tagName":"header"} /-->

<!-- wp:group {"tagName":"main","layout":{"type":"constrained"}} -->
<main class="wp-block-group">
	{$singleTitle}

	{$contentBlock}
</main>
<!-- /wp:group -->

<!-- wp:template-part {"slug":"footer","tagName":"footer"} /-->
HTML;
    }
}
