<?php

declare(strict_types=1);

use RalfHortt\WpCliScaffold\Support\ThemeJsonSectionMerger;

it('merges associative sections recursively', function (): void {
    $merger = new ThemeJsonSectionMerger();

    $base = [
        'settings' => [
            'color' => [
                'custom' => false,
            ],
        ],
    ];

    $merged = $merger->merge($base, 'settings.color', ['link' => true]);

    expect($merged['settings']['color']['custom'])->toBeFalse()
        ->and($merged['settings']['color']['link'])->toBeTrue();
});

it('merges slug object lists by slug', function (): void {
    $merger = new ThemeJsonSectionMerger();

    $base = [
        'settings' => [
            'color' => [
                'palette' => [
                    ['slug' => 'brand', 'name' => 'Brand', 'color' => '#000000'],
                ],
            ],
        ],
    ];

    $incoming = [
        ['slug' => 'brand', 'color' => '#111111'],
        ['slug' => 'accent', 'name' => 'Accent', 'color' => '#ff0000'],
    ];

    $merged = $merger->merge($base, 'settings.color.palette', $incoming);
    $palette = $merged['settings']['color']['palette'];

    expect($palette)->toHaveCount(2)
        ->and($palette[0]['slug'])->toBe('brand')
        ->and($palette[0]['color'])->toBe('#111111')
        ->and($palette[1]['slug'])->toBe('accent');
});

it('creates missing nested path', function (): void {
    $merger = new ThemeJsonSectionMerger();

    $merged = $merger->merge([], 'styles.color', ['background' => '#ffffff']);

    expect($merged['styles']['color']['background'])->toBe('#ffffff');
});
