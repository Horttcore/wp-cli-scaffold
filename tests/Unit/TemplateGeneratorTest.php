<?php

declare(strict_types=1);

use RalfHortt\WpCliScaffold\Generators\TemplateGenerator;

it('generates 404 template markup', function (): void {
    $generator = new TemplateGenerator();

    $content = $generator->generate('Not Found', '404', 'Error page');

    expect($content)->toContain('wp:template-part')
        ->and($content)->toContain('Page not found')
        ->and($content)->toContain('Error page');
});

it('generates query template for archive slug', function (): void {
    $generator = new TemplateGenerator();

    $content = $generator->generate('Archive', 'archive', '');

    expect($content)->toContain('wp:query-title')
        ->and($content)->toContain('wp:query')
        ->and($content)->toContain('wp:query-pagination');
});

it('generates post content template for single slug', function (): void {
    $generator = new TemplateGenerator();

    $content = $generator->generate('Single', 'single', '');

    expect($content)->toContain('wp:post-title')
        ->and($content)->toContain('wp:post-content');
});
