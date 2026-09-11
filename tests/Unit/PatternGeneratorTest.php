<?php

declare(strict_types=1);

use RalfHortt\WpCliScaffold\Generators\PatternGenerator;

it('generates pattern header and slug', function (): void {
    $generator = new PatternGenerator();

    $content = $generator->generate(
        'my-theme',
        'Hero Banner',
        'hero-banner',
        'Main hero section',
        ['banner'],
        ['hero', 'cover'],
        ['core/group'],
        ['page'],
        ['front-page'],
        true,
        'theme'
    );

    expect($content)->toContain('Title: Hero Banner')
        ->and($content)->toContain('Slug: my-theme/hero-banner')
        ->and($content)->toContain('Description: Main hero section')
        ->and($content)->toContain('Categories: banner')
        ->and($content)->toContain('Keywords: hero, cover')
        ->and($content)->toContain('Source: theme');
});

it('omits inserter header when disabled', function (): void {
    $generator = new PatternGenerator();

    $content = $generator->generate(
        'my-theme',
        'Hidden Pattern',
        'hidden-pattern',
        '',
        [],
        [],
        [],
        [],
        [],
        false,
        'theme'
    );

    expect($content)->toContain('Inserter: false');
});
