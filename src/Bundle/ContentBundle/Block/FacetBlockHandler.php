<?php

namespace Integrated\Bundle\ContentBundle\Block;

use Integrated\Bundle\BlockBundle\Block\BlockHandler;
use Integrated\Bundle\ContentBundle\Document\Block\ContentBlock;
use Integrated\Bundle\ContentBundle\Document\Block\FacetBlock;
use Integrated\Common\Block\BlockHandlerRegistryInterface;
use Integrated\Common\Block\BlockInterface;
use Solarium\QueryType\Select\Result\Result;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FacetBlockHandler extends BlockHandler
{
    public function __construct(
        private readonly BlockHandlerRegistryInterface $blockRegistry,
        private readonly RequestStack $requestStack
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function execute(BlockInterface $block, array $options)
    {
        if (!$block instanceof FacetBlock) {
            return;
        }

        $contentBlock = $block->getBlock();

        if (!$contentBlock instanceof ContentBlock) {
            return;
        }

        $handler = $this->blockRegistry->getHandler($contentBlock->getType());

        if (!$handler instanceof ContentBlockHandler) {
            return;
        }

        $request = $this->requestStack->getCurrentRequest();

        if (!$request instanceof Request) {
            return;
        }

        $options['exclude'] = false;

        $pagination = $handler->getPagination($contentBlock, $request, $options);

        $result = $pagination->getCustomParameter('result');

        if (!$result instanceof Result) {
            return;
        }

        $facetSet = $result->getFacetSet();

        if (null === $facetSet) {
            return;
        }

        $facets = [];
        $orderedFacets = [];

        foreach ($block->getFields() as $field) {
            $facetValues = $facetSet->getFacet($field->getField())->getValues();

            $facets[$field->getField()] = [
                'name' => $field->getName(),
                'values' => $facetSet->getFacet($field->getField()),
            ];

            $sortedFacetValues = [];

            foreach ($facetValues as $name => $count) {
                $issue = 0;
                $year = 0;

                if (preg_match('/(\d+) (\d{4})/', $name, $matches)) {
                    $issue = (int) $matches[1];
                    $year = (int) $matches[2];
                } elseif (preg_match('/(\d{4})/', $name, $matches)) {
                    $year = (int) $matches[1];
                    $issue = 99;
                } else {
                    $issue = 0;
                    $year = 0;
                }

                $sortedFacetValues[] = [
                    'name' => $name,
                    'count' => $count,
                    'issue' => $issue,
                    'year' => $year,
                ];
            }

            usort($sortedFacetValues, function ($a, $b) {
                return $b['year'] <=> $a['year'] ?: $b['issue'] <=> $a['issue'];
            });

            $facetValuesSorted = [];
            foreach ($sortedFacetValues as $entry) {
                $facetValuesSorted[$entry['name']] = $entry['count'];
            }

            $orderedFacets[$field->getField()] = [
                'name' => $field->getName(),
                'values' => $facetValuesSorted,
            ];
        }

        if (!\count($facets)) {
            return;
        }

        return $this->render([
            'block' => $block,
            'facets' => $facets,
            'orderedFacets' => $orderedFacets,
            'options' => $options,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'filters' => [], // add extra filters (overwrites search selection)
            'gridLevel' => 0,
            'data' => '',
        ]);

        $resolver->setAllowedTypes('filters', 'array');
    }
}
