<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Block;

use Integrated\Bundle\BlockBundle\Block\BlockHandler;
use Integrated\Bundle\BlockBundle\Document\Block\BlockRepository;
use Integrated\Bundle\ContentBundle\Document\Block\ContentBlock;
use Integrated\Bundle\ContentBundle\Document\Block\FacetBlock;
use Integrated\Bundle\ContentBundle\Solr\Query\Provider\IntegratedContentBlock;
use Integrated\Bundle\PageBundle\Document\Page\Page;
use Integrated\Common\Block\BlockInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContentBlockHandler extends BlockHandler
{
    /**
     * @var IntegratedContentBlock
     */
    private $provider;

    /**
     * @var RequestStack
     */
    private $requestStack;

    private ?BlockRepository $blockRepository;

    public function __construct(IntegratedContentBlock $provider, RequestStack $requestStack, ?BlockRepository $blockRepository = null)
    {
        $this->provider = $provider;
        $this->requestStack = $requestStack;
        $this->blockRepository = $blockRepository;
    }

    public function execute(BlockInterface $block, array $options)
    {
        if (!$block instanceof ContentBlock) {
            return;
        }

        $request = $this->requestStack->getCurrentRequest();

        if (!$request instanceof Request) {
            return;
        }

        $pagination = $this->getPagination($block, $request, $options);

        if (!\count($pagination)) {
            return;
        }

        return $this->render([
            'block' => $block,
            'pagination' => $pagination,
            'document' => $this->getDocument(),
            'options' => $options,
        ]);
    }

    public function getPagination(ContentBlock $block, Request $request, array $options = []): PaginationInterface
    {
        return $this->provider->get($block, $request->duplicate(), $this->resolveFacetOptions($block, $request, $options));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'filters' => [],   // add extra filters (overwrites search selection)
            'exclude' => true, // exclude already shown items
            'gridLevel' => 0,
            'data' => '',
        ]);

        $resolver->setAllowedTypes('filters', 'array');
        $resolver->setAllowedTypes('exclude', 'bool');
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return array<string, mixed>
     */
    private function resolveFacetOptions(ContentBlock $block, Request $request, array $options): array
    {
        if (!$this->blockRepository instanceof BlockRepository) {
            return $options;
        }

        $page = $request->attributes->get('_integrated_page_document');

        if (!$page instanceof Page) {
            return $options;
        }

        $facetOperators = $options['facet_operators'] ?? [];
        if (!\is_array($facetOperators)) {
            $facetOperators = [];
        }

        $facetSelectionModes = $options['facet_selection_modes'] ?? [];
        if (!\is_array($facetSelectionModes)) {
            $facetSelectionModes = [];
        }

        foreach ($page->getBlockIds() as $pageBlockId) {
            $pageBlock = $this->blockRepository->find($pageBlockId);
            if (!$pageBlock instanceof FacetBlock) {
                continue;
            }

            $linkedBlock = $pageBlock->getBlock();
            if ($linkedBlock->getId() !== $block->getId()) {
                continue;
            }

            foreach ($pageBlock->getFields() as $field) {
                $fieldName = trim((string) $field->getField());
                if ('' === $fieldName) {
                    continue;
                }

                $facetOperators[$fieldName] = $pageBlock->getOperator();
                $facetSelectionModes[$fieldName] = $pageBlock->getSelectionMode();
            }
        }

        $options['facet_operators'] = $facetOperators;
        $options['facet_selection_modes'] = $facetSelectionModes;

        return $options;
    }
}
