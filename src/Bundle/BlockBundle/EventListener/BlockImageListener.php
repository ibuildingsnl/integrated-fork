<?php

namespace Integrated\Bundle\BlockBundle\EventListener;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\BlockBundle\Document\Block\Block;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Document\Content\Embedded\Relation;
use Integrated\Common\Content\Form\Event\BlockEvent;
use Integrated\Common\Content\Form\Event\ValidationEvent;
use Integrated\Common\Content\Form\Events;
use Integrated\Common\Services\MainFlusher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BlockImageListener implements EventSubscriberInterface
{
    private $documentManager;
    private $flusher;

    public function __construct(DocumentManager $documentManager, MainFlusher $flusher)
    {
        $this->documentManager = $documentManager;
        $this->flusher = $flusher;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::BLOCK_VALIDATE => ['buildForm', -60],
        ];
    }

    public function buildForm(BlockEvent $event): void
    {
        $block = $this->documentManager->getRepository(Block::class)->find($event->getBlock());

        if ($block === null) {
            $block = $event->getBlock();
        }

        if (method_exists($block, 'getFeaturedImage')) {
            if ($block->getFeaturedImage() != null) {
                $image = $this->documentManager->getRepository(Content::class)->find(
                    $block->getFeaturedImage()->getId()
                );

                $block->addRelation(
                    (new Relation())
                        ->setRelationId('__featured_image')
                        ->setRelationType('embedded')
                        ->addReference($image)
                );

                $this->flusher->flush();
            }
        }

        if (method_exists($block, 'getImage')) {
            if ($block->getPicture() != null) {
                $image = $this->documentManager->getRepository(Content::class)->find($block->getPicture()->getId());

                $block->addRelation(
                    (new Relation())
                        ->setRelationId('__image')
                        ->setRelationType('embedded')
                        ->addReference($image)
                );

                $this->flusher->flush();
            }
        }
    }
}
