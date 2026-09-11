<?php

declare(strict_types=1);

use RalfHortt\WpCliScaffold\Generators\StyleVariationGenerator;

it('generates valid style variation json with palette and styles', function (): void {
    $generator = new StyleVariationGenerator();

    $json = $generator->generate(
        'Ocean',
        'ocean',
        'Ocean variation',
        '#0a5cff',
        '#2d8cff',
        '#f4f8ff',
        '#0f172a'
    );

    $decoded = json_decode($json, true);

    expect($decoded)->toBeArray()
        ->and($decoded['version'])->toBe(3)
        ->and($decoded['title'])->toBe('Ocean')
        ->and($decoded['slug'])->toBe('ocean')
        ->and($decoded['settings']['color']['palette'])->toHaveCount(4)
        ->and($decoded['styles']['color']['background'])->toBe('#f4f8ff')
        ->and($decoded['styles']['color']['text'])->toBe('#0f172a');
});

it('omits optional style sections when colors are not provided', function (): void {
    $generator = new StyleVariationGenerator();

    $json = $generator->generate('Minimal', 'minimal', '', null, null, null, null);
    $decoded = json_decode($json, true);

    expect($decoded)->toBeArray()
        ->and(isset($decoded['settings']))->toBeFalse()
        ->and(isset($decoded['styles']))->toBeFalse();
});
