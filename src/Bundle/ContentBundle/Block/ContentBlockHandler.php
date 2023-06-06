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
use Integrated\Bundle\ContentBundle\Document\Block\ContentBlock;
use Integrated\Bundle\ContentBundle\Solr\Query\Provider\IntegratedContentBlock;
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

    public function __construct(IntegratedContentBlock $provider, RequestStack $requestStack)
    {
        $this->provider = $provider;
        $this->requestStack = $requestStack;
    }

    /**
     * {@inheritdoc}
     */
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
        return $this->provider->get($block, $request->duplicate(), $options);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'filters' => [],   // add extra filters (overwrites search selection)
            'exclude' => true, // exclude already shown items
            'gridLevel' => 0,
        ]);

        $resolver->setAllowedTypes('filters', 'array');
        $resolver->setAllowedTypes('exclude', 'bool');
    }
}
