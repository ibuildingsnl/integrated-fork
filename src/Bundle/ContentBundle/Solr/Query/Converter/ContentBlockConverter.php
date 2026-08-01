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
        ];

        if ($search = $request->query->get($block->getId().'-search')) {
            $settings['q'] = $search;
        }

        if ($selection = $block->getSearchSelection()) {
            $filters = $selection->getFilters();

            $settings['facets_search_selection'] = $this->getFacets($block, $filters);
            $settings['relation_search_selection'] = $filters['relation'] ?? [];
            $settings['params'] = $selection->getInternalParams();
        }

        $settings['facets'] = $this->getFacets($block, $request->query->all());
        $settings['filters'] = $options['filters'] ?? [];
        $settings['relation'] = $request->query->all('relation');

        if ($options['exclude'] && !$settings['q'] && 0 === \count(array_filter($settings['facets']))) {
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
}
