<?php

namespace Integrated\Bundle\ContentBundle\Solr\Query\Converter;

use Integrated\Bundle\ContentBundle\Document\Block\ContentBlock;
use Symfony\Component\HttpFoundation\Request;

class ContentBlockConverter
{
    public function convert(ContentBlock $block, Request $request, array $options = []): array
    {
        if (!$channel = $request->attributes->get('_channel')) {
            throw new \RuntimeException('Channel is required'); // @todo improve (INTEGRATED-431)
        }

        $settings = [
            'exclude' => false,
            'q' => '',
            'channels' => [$channel],
            'pub_channels' => [$channel],
        ];

        if ($search = $request->query->get($block->getId().'-search')) {
            $settings['q'] = $search;
        }

        if ($selection = $block->getSearchSelection()) {
            $filters = $selection->getFilters();

            $settings['facets_search_selection'] = $this->getFacets($block, $filters);
            $settings['relation_search_selection'] = $filters['relation'] ?? [];
            $settings['params'] = $selection->getInternalParams();
            $settings['sort'] = $filters['sort'] ?? '';
            $settings['order'] = $filters['order'] ?? '';

            if (\is_string($settings['sort']) && str_starts_with(strtolower(trim($settings['sort'])), 'custom:')) {
                $settings['sorts'] = $this->parseCustomSorts(substr(trim($settings['sort']), 7));
            }
        }

        $settings['facet_selection_modes'] = $this->getFacetSelectionModes($block, $options['facet_selection_modes'] ?? []);
        $settings['facets'] = $this->normalizeFacetSelections(
            $this->getFacets($block, $request->query->all()),
            $settings['facet_selection_modes']
        );
        $settings['facet_operators'] = $this->getFacetOperators($block, $options['facet_operators'] ?? []);
        $settings['filters'] = $options['filters'] ?? [];
        $settings['relation'] = $request->query->all('relation');

        if (($options['exclude'] ?? false) && !$settings['q'] && 0 === \count(array_filter($settings['facets']))) {
            $settings['exclude'] = true;
        }

        return $settings;
    }

    protected function getFacets($subject, array $values = []): array
    {
        $facets = [];
        $facetFields = $subject->getFacetFields();

        $contentTypes = $values['contenttypes'] ?? [];

        if (\count($contentTypes) && !\in_array('type_name', $facetFields)) {
            $facetFields[] = 'type_name';
            $values['type_name'] = $contentTypes;
        }

        $properties = $values['properties'] ?? [];

        if (\count($properties) && !\in_array('facet_properties', $facetFields)) {
            $facetFields[] = 'facet_properties';
            $values['facet_properties'] = $properties;
        }

        foreach ($facetFields as $field) {
            $facets[$field] = $values[$field] ?? [];
        }

        return $facets;
    }

    /**
     * @return array<string, string>
     */
    protected function getFacetOperators(ContentBlock $block, mixed $operators = []): array
    {
        if (!\is_array($operators)) {
            return [];
        }

        $allowedFields = array_map(static fn ($field): string => trim((string) $field), $block->getFacetFields());
        $allowedFields = array_values(array_filter($allowedFields));

        $result = [];
        foreach ($operators as $field => $operator) {
            $field = trim((string) $field);
            if ('' === $field || !\in_array($field, $allowedFields, true)) {
                continue;
            }

            $operator = strtolower(trim((string) $operator));
            if (!\in_array($operator, ['and', 'or'], true)) {
                $operator = 'or';
            }

            $result[$field] = $operator;
        }

        return $result;
    }

    /**
     * @return array<string, string>
     */
    protected function getFacetSelectionModes(ContentBlock $block, mixed $selectionModes = []): array
    {
        if (!\is_array($selectionModes)) {
            return [];
        }

        $allowedFields = array_map(static fn ($field): string => trim((string) $field), $block->getFacetFields());
        $allowedFields = array_values(array_filter($allowedFields));

        $result = [];
        foreach ($selectionModes as $field => $selectionMode) {
            $field = trim((string) $field);
            if ('' === $field || !\in_array($field, $allowedFields, true)) {
                continue;
            }

            $selectionMode = strtolower(trim((string) $selectionMode));
            if (!\in_array($selectionMode, ['single', 'multi'], true)) {
                $selectionMode = 'single';
            }

            $result[$field] = $selectionMode;
        }

        return $result;
    }

    /**
     * @param array<string, array<mixed>> $facets
     * @param array<string, string>       $selectionModes
     *
     * @return array<string, array<mixed>>
     */
    protected function normalizeFacetSelections(array $facets, array $selectionModes): array
    {
        foreach ($facets as $field => $values) {
            if ('single' !== ($selectionModes[$field] ?? 'multi')) {
                continue;
            }

            $facets[$field] = \count($values) > 1 ? [reset($values)] : $values;
        }

        return $facets;
    }

    /**
     * @return array<string, string>
     */
    private function parseCustomSorts(string $sort): array
    {
        $sorts = [];

        foreach (explode(',', $sort) as $part) {
            $part = trim($part);
            if ('' === $part) {
                continue;
            }

            $pieces = preg_split('/\s+/', $part);
            if (!\is_array($pieces) || \count($pieces) < 2) {
                continue;
            }

            $order = strtolower((string) array_pop($pieces));
            $field = implode(' ', $pieces);

            if (!\in_array($order, ['asc', 'desc'], true) || !preg_match('/^[A-Za-z0-9_.-]+$/', $field)) {
                continue;
            }

            $sorts[$field] = $order;
        }

        return $sorts;
    }
}
