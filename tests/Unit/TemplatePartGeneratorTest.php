<?php

declare(strict_types=1);

use RalfHortt\WpCliScaffold\Generators\TemplatePartGenerator;

it('generates header template part with navigation', function (): void {
    $generator = new TemplatePartGenerator();

    $content = $generator->generate('header', 'Site Header', '');

    expect($content)->toContain('wp:site-title')
        ->and($content)->toContain('wp:navigation');
});

it('generates footer template part with escaped title', function (): void {
    $generator = new TemplatePartGenerator();

    $content = $generator->generate('footer', 'Footer <Title>', 'Footer note');

    expect($content)->toContain('Footer note')
        ->and($content)->toContain('Footer <Title>');
});

it('generates fallback template part for unknown area', function (): void {
    $generator = new TemplatePartGenerator();

    $content = $generator->generate('custom', 'Custom Area', '');

    expect($content)->toContain('wp:heading')
        ->and($content)->toContain('Custom Area');
});
