<?php

declare(strict_types=1);

namespace RalfHortt\WpCliScaffold\Generators;

final class StyleVariationGenerator
{
    public function generate(
        string $title,
        string $slug,
        string $description,
        ?string $primaryColor,
        ?string $secondaryColor,
        ?string $backgroundColor,
        ?string $textColor,
    ): string {
        $data = [
            '$schema' => 'https://schemas.wp.org/trunk/theme.json',
            'version' => 3,
            'title' => $title,
            'slug' => $slug,
        ];

        if ($description !== '') {
            $data['description'] = $description;
        }

        $palette = [];

        if ($primaryColor !== null && $primaryColor !== '') {
            $palette[] = [
                'slug' => 'primary',
                'name' => 'Primary',
                'color' => $primaryColor,
            ];
        }

        if ($secondaryColor !== null && $secondaryColor !== '') {
            $palette[] = [
                'slug' => 'secondary',
                'name' => 'Secondary',
                'color' => $secondaryColor,
            ];
        }

        if ($backgroundColor !== null && $backgroundColor !== '') {
            $palette[] = [
                'slug' => 'background',
                'name' => 'Background',
                'color' => $backgroundColor,
            ];
        }

        if ($textColor !== null && $textColor !== '') {
            $palette[] = [
                'slug' => 'text',
                'name' => 'Text',
                'color' => $textColor,
            ];
        }

        if ($palette !== []) {
            $data['settings'] = [
                'color' => [
                    'palette' => $palette,
                ],
            ];
        }

        if (($backgroundColor !== null && $backgroundColor !== '') || ($textColor !== null && $textColor !== '')) {
            $styles = [];

            if ($backgroundColor !== null && $backgroundColor !== '') {
                $styles['color']['background'] = $backgroundColor;
            }

            if ($textColor !== null && $textColor !== '') {
                $styles['color']['text'] = $textColor;
            }

            $data['styles'] = $styles;
        }

        return (string) wp_json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
    }
}
