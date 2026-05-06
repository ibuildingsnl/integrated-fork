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
        private readonly RequestStack $requestStack,
    ) {
    }

    public function execute(BlockInterface $block, array $options)
    {
        if (!$block instanceof FacetBlock) {
            return null;
        }

        $contentBlock = $block->getBlock();

        if (!$contentBlock instanceof ContentBlock) {
            return null;
        }

        $handler = $this->blockRegistry->getHandler($contentBlock->getType());

        if (!$handler instanceof ContentBlockHandler) {
            return null;
        }

        $request = $this->requestStack->getCurrentRequest();

        if (!$request instanceof Request) {
            return null;
        }

        $options['exclude'] = false; // don't exclude already shown items
        $options['facet_operators'] = $this->getFacetOperators($block, $options['facet_operators'] ?? []);
        $options['facet_selection_modes'] = $this->getFacetSelectionModes($block, $options['facet_selection_modes'] ?? []);

        $pagination = $handler->getPagination($contentBlock, $request, $options);

        $result = $pagination->getCustomParameter('result');

        if (!$result instanceof Result) {
            return null;
        }

        $facetSet = $result->getFacetSet();

        if (null === $facetSet) {
            return null;
        }

        $facets = [];
        $orderedFacets = [];
        foreach ($block->getFields() as $field) {
            $fieldName = $field->getField();
            $facetValues = $this->getFacetValues($facetSet->getFacet($fieldName));

            $facets[$field->getField()] = [
                'name' => $field->getName(),
                'values' => $facetValues,
            ];

            $orderedFacets[$fieldName] = [
                'name' => $field->getName(),
                'values' => $this->sortFacetValuesByYearIssue($facetValues),
            ];
        }

        if (!\count($facets)) {
            return null;
        }

        return $this->render([
            'block' => $block,
            'facets' => $facets,
            'orderedFacets' => $orderedFacets,
            'options' => $options,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'filters' => [], // add extra filters (overwrites search selection)
            'gridLevel' => 0,
            'data' => '',
        ]);

        $resolver->setAllowedTypes('filters', 'array');
    }

    /**
     * @param array<string, string> $existing
     *
     * @return array<string, string>
     */
    private function getFacetOperators(FacetBlock $block, array $existing): array
    {
        $operators = $existing;

        foreach ($block->getFields() as $field) {
            $fieldName = trim((string) $field->getField());
            if ('' === $fieldName) {
                continue;
            }

            $operators[$fieldName] = $block->getOperator();
        }

        return $operators;
    }

    /**
     * @param array<string, string> $existing
     *
     * @return array<string, string>
     */
    private function getFacetSelectionModes(FacetBlock $block, array $existing): array
    {
        $selectionModes = $existing;

        foreach ($block->getFields() as $field) {
            $fieldName = trim((string) $field->getField());
            if ('' === $fieldName) {
                continue;
            }

            $selectionModes[$fieldName] = $block->getSelectionMode();
        }

        return $selectionModes;
    }

    /**
     * @return array<string, int>
     */
    private function getFacetValues(mixed $facet): array
    {
        if (\is_object($facet) && method_exists($facet, 'getValues')) {
            $facet = $facet->getValues();
        }

        if (!\is_iterable($facet)) {
            return [];
        }

        $values = [];
        foreach ($facet as $name => $count) {
            $name = trim((string) $name);
            if ($name === '') {
                continue;
            }

            $values[$name] = (int) $count;
        }

        return $values;
    }

    /**
     * @param array<string, int> $facetValues
     *
     * @return array<string, int>
     */
    private function sortFacetValuesByYearIssue(array $facetValues): array
    {
        $rows = [];
        foreach ($facetValues as $name => $count) {
            $issue = -1;
            $year = 0;
            $special = true;

            if (preg_match('/Nummer\s+(\d+)\s+van\s+(\d{4})/i', $name, $matches) === 1) {
                $issue = (int) $matches[1];
                $year = (int) $matches[2];
                $special = false;
            } elseif (preg_match('/(\d{4})/', $name, $matches) === 1) {
                $issue = 0;
                $year = (int) $matches[1];
                $special = false;
            }

            $rows[] = [
                'name' => $name,
                'count' => $count,
                'issue' => $issue,
                'year' => $year,
                'special' => $special,
            ];
        }

        usort($rows, static function (array $left, array $right): int {
            return ($left['special'] <=> $right['special'])
                ?: ($right['year'] <=> $left['year'])
                ?: ($right['issue'] <=> $left['issue'])
                ?: strnatcasecmp($left['name'], $right['name']);
        });

        $sorted = [];
        foreach ($rows as $row) {
            $sorted[$row['name']] = $row['count'];
        }

        return $sorted;
    }
}
