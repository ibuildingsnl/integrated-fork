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

        $singleSelectFields = $this->getSingleSelectFacetFields($page);
        if ([] === $singleSelectFields) {
            return null;
        }

        $query = $request->query->all();
        $normalizedQuery = $query;
        $changed = false;

        foreach ($singleSelectFields as $field) {
            if (!\array_key_exists($field, $normalizedQuery)) {
                continue;
            }

            $values = $this->sanitizeFacetValues($normalizedQuery[$field]);
            if ([] === $values) {
                unset($normalizedQuery[$field]);
                $changed = true;

                continue;
            }

            $normalizedValues = [$values[0]];
            if ($normalizedValues !== $this->sanitizeFacetValues($normalizedQuery[$field])) {
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
     * @return list<string>
     */
    private function getSingleSelectFacetFields(Page $page): array
    {
        $fields = [];

        foreach ($page->getBlockIds() as $blockId) {
            $block = $this->blockRepository->find($blockId);

            if (!$block instanceof FacetBlock || FacetBlock::SELECTION_MODE_SINGLE !== $block->getSelectionMode()) {
                continue;
            }

            foreach ($block->getFields() as $field) {
                $fieldName = trim((string) $field->getField());
                if ('' !== $fieldName) {
                    $fields[$fieldName] = true;
                }
            }
        }

        return array_keys($fields);
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
}
