<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\BlockBundle\Templating;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\DocumentNotFoundException;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Integrated\Bundle\BlockBundle\Block\BlockHandler;
use Integrated\Bundle\BlockBundle\Document\Block\Block;
use Integrated\Bundle\ThemeBundle\Templating\ThemeManager;
use Integrated\Common\Block\BlockHandlerInterface;
use Integrated\Common\Block\BlockHandlerRegistryInterface;
use Integrated\Common\Block\BlockInterface;
use Integrated\Common\Content\ContentInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Twig\Environment;

/**
 * @author Ger Jan van den Bosch <gerjan@e-active.nl>
 */
class BlockManager
{
    /**
     * @var BlockHandlerRegistryInterface
     */
    protected $blockRegistry;

    /**
     * @var ThemeManager
     */
    protected $themeManager;

    /**
     * @var DocumentRepository
     */
    protected $repository;

    /**
     * @var Environment
     */
    protected $twig;

    /**
     * @var ContentInterface
     */
    protected $document;

    public function __construct(BlockHandlerRegistryInterface $blockRegistry, ThemeManager $themeManager, DocumentManager $dm, Environment $twig)
    {
        $this->blockRegistry = $blockRegistry;
        $this->themeManager = $themeManager;
        $this->repository = $dm->getRepository(Block::class);
        $this->twig = $twig; // @todo templating service (INTEGRATED-443)
    }

    /**
     * @param BlockInterface|string $block
     *
     * @return string|null
     */
    public function render($block, array $options = [])
    {
        if (\is_string($block)) {
            $block = $this->getBlock($block);
        }

        if ($block instanceof BlockInterface) {
            try {
                if ($block instanceof Block && (!$block->isPublished() || $block->isDisabled())) {
                    return null;
                }
            } catch (DocumentNotFoundException $e) {
                return null;
            }

            $handler = $this->blockRegistry->getHandler($block->getType());

            if ($handler instanceof BlockHandlerInterface) {
                if ($handler instanceof BlockHandler) {
                    $handler->setTwig($this->twig);

                    if ($this->document instanceof ContentInterface) {
                        $handler->setDocument($this->document);
                    }

                    $handler->configureOptions($resolver = new OptionsResolver());
                    $options = $resolver->resolve($options);

                    if ($template = $this->themeManager->locateTemplate('blocks/'.$block->getType().'/'.$block->getLayout())) {
                        $handler->setTemplate($template);
                    }
                }

                return $handler->execute($block, $options);
            }
        }

        return null;
    }

    /**
     * @param string $id
     *
     * @return Block|null
     */
    public function getBlock($id)
    {
        return $this->repository->find($id);
    }

    /**
     * @return $this
     */
    public function setDocument(ContentInterface $document)
    {
        $this->document = $document;

        return $this;
    }
}
