<?php

declare(strict_types=1);

namespace RalfHortt\WpCliScaffold\Support;

final class ThemeJsonSectionMerger
{
    /**
     * @param  array<string, mixed>  $themeJson
     * @return array<string, mixed>
     */
    public function merge(array $themeJson, string $section, mixed $incoming): array
    {
        $section = trim($section);

        if ($section === '') {
            throw new \InvalidArgumentException('Section path cannot be empty.');
        }

        $segments = array_values(array_filter(explode('.', $section), static fn (string $part): bool => $part !== ''));

        if ($segments === []) {
            throw new \InvalidArgumentException('Section path cannot be empty.');
        }

        $current = $this->getByPath($themeJson, $segments);
        $merged = $this->mergeValues($current, $incoming);
        $this->setByPath($themeJson, $segments, $merged);

        return $themeJson;
    }

    private function mergeValues(mixed $current, mixed $incoming): mixed
    {
        if ($current === null) {
            return $incoming;
        }

        if (! is_array($current) || ! is_array($incoming)) {
            return $incoming;
        }

        if ($this->isList($current) && $this->isList($incoming)) {
            if ($this->isSlugObjectList($current) && $this->isSlugObjectList($incoming)) {
                return $this->mergeSlugObjectLists($current, $incoming);
            }

            return array_values(array_merge($current, $incoming));
        }

        if (! $this->isList($current) && ! $this->isList($incoming)) {
            return array_replace_recursive($current, $incoming);
        }

        return $incoming;
    }

    /**
     * @param  array<int, mixed>  $existing
     * @param  array<int, mixed>  $incoming
     * @return array<int, mixed>
     */
    private function mergeSlugObjectLists(array $existing, array $incoming): array
    {
        $bySlug = [];

        foreach ($existing as $item) {
            if (is_array($item) && isset($item['slug']) && is_string($item['slug'])) {
                $bySlug[$item['slug']] = $item;
            }
        }

        foreach ($incoming as $item) {
            if (! is_array($item) || ! isset($item['slug']) || ! is_string($item['slug'])) {
                $existing[] = $item;

                continue;
            }

            $slug = $item['slug'];
            $existingItem = $bySlug[$slug] ?? [];
            $bySlug[$slug] = is_array($existingItem)
                ? array_replace_recursive($existingItem, $item)
                : $item;
        }

        $result = [];
        $used = [];

        foreach ($existing as $item) {
            if (! is_array($item) || ! isset($item['slug']) || ! is_string($item['slug'])) {
                $result[] = $item;

                continue;
            }

            $slug = $item['slug'];
            $result[] = $bySlug[$slug];
            $used[$slug] = true;
        }

        foreach ($incoming as $item) {
            if (! is_array($item) || ! isset($item['slug']) || ! is_string($item['slug'])) {
                continue;
            }

            $slug = $item['slug'];

            if (! isset($used[$slug])) {
                $result[] = $bySlug[$slug];
                $used[$slug] = true;
            }
        }

        return $result;
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, string>  $path
     */
    private function getByPath(array $data, array $path): mixed
    {
        $current = $data;

        foreach ($path as $segment) {
            if (! is_array($current) || ! array_key_exists($segment, $current)) {
                return null;
            }

            $current = $current[$segment];
        }

        return $current;
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, string>  $path
     */
    private function setByPath(array &$data, array $path, mixed $value): void
    {
        $current =& $data;
        $last = array_pop($path);

        foreach ($path as $segment) {
            if (! isset($current[$segment]) || ! is_array($current[$segment])) {
                $current[$segment] = [];
            }

            $current =& $current[$segment];
        }

        if ($last === null) {
            return;
        }

        $current[$last] = $value;
    }

    /**
     * @param  array<int, mixed>  $value
     */
    private function isSlugObjectList(array $value): bool
    {
        if ($value === []) {
            return false;
        }

        foreach ($value as $item) {
            if (! is_array($item) || ! isset($item['slug']) || ! is_string($item['slug'])) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  array<int|string, mixed>  $array
     */
    private function isList(array $array): bool
    {
        if ($array === []) {
            return true;
        }

        return array_keys($array) === range(0, count($array) - 1);
    }
}
