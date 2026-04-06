<?php

declare(strict_types=1);

namespace Integrated\Bundle\WebsiteBundle\Service;

use Integrated\Bundle\BlockBundle\Document\Block\BlockRepository;
use Integrated\Bundle\ContentBundle\Document\Block\FacetBlock;
use Integrated\Bundle\PageBundle\Document\Page\Page;
use Symfony\Component\HttpFoundation\Request;

class FacetQueryCanonicalizer
{
    public function __construct(
        private readonly BlockRepository $blockRepository,
    ) {
    }

    public function getNormalizedPath(Page $page, Request $request): ?string
    {
        if (!$request->isMethod('GET')) {
            return null;
        }

        if ($request->query->count() === 0) {
            return null;
        }

        $facetFieldSelectionModes = $this->getFacetFieldSelectionModes($page);
        if ([] === $facetFieldSelectionModes) {
            return null;
        }

        $query = $request->query->all();
        $normalizedQuery = $query;
        $changed = false;

        foreach ($facetFieldSelectionModes as $field => $selectionMode) {
            if (!\array_key_exists($field, $normalizedQuery)) {
                continue;
            }

            $values = $this->sanitizeFacetValues($normalizedQuery[$field]);
            if ([] === $values) {
                unset($normalizedQuery[$field]);
                $changed = true;

                continue;
            }

            $normalizedValues = $this->normalizeFacetValues($values, $selectionMode);
            if ($normalizedValues !== $values) {
                $changed = true;
            }

            $normalizedQuery[$field] = $normalizedValues;
        }

        if (!$changed) {
            return null;
        }

        $queryString = http_build_query($normalizedQuery, '', '&', \PHP_QUERY_RFC3986);

        return '' !== $queryString ? $request->getPathInfo().'?'.$queryString : $request->getPathInfo();
    }

    /**
     * @return array<string, string>
     */
    private function getFacetFieldSelectionModes(Page $page): array
    {
        $fields = [];
        $blockIds = $page->getBlockIds();
        if ($blockIds === []) {
            return $fields;
        }

        foreach ($this->blockRepository->findByIds($blockIds) as $block) {
            if (!$block instanceof FacetBlock) {
                continue;
            }

            foreach ($block->getFields() as $field) {
                $fieldName = trim((string) $field->getField());
                if ('' !== $fieldName) {
                    if (!isset($fields[$fieldName])) {
                        $fields[$fieldName] = $block->getSelectionMode();
                    }
                }
            }
        }

        return $fields;
    }

    /**
     * @return list<string>
     */
    private function sanitizeFacetValues(mixed $value): array
    {
        if (!\is_array($value)) {
            if (!\is_scalar($value)) {
                return [];
            }

            $value = [(string) $value];
        }

        $result = [];
        foreach ($value as $item) {
            if (!\is_scalar($item)) {
                continue;
            }

            $item = trim((string) $item);
            if ('' !== $item) {
                $result[] = $item;
            }
        }

        return array_values(array_unique($result));
    }

    /**
     * @param list<string> $values
     *
     * @return list<string>
     */
    private function normalizeFacetValues(array $values, string $selectionMode): array
    {
        if (FacetBlock::SELECTION_MODE_SINGLE === $selectionMode) {
            return [$values[0]];
        }

        sort($values, \SORT_NATURAL | \SORT_FLAG_CASE);

        return array_values($values);
    }
}
