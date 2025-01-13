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
        foreach ($block->getFields() as $field) {
            $facets[$field->getField()] = [
                'name' => $field->getName(),
                'values' => $facetSet->getFacet($field->getField()),
            ];
        }

        if (!\count($facets)) {
            return null;
        }

        return $this->render([
            'block' => $block,
            'facets' => $facets,
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
