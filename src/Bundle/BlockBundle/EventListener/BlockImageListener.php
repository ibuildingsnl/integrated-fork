<?php

namespace Integrated\Bundle\BlockBundle\EventListener;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\BlockBundle\Document\Block\Block;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\BlockBundle\Document\Block\Embedded\Relation;
use Integrated\Common\Content\Form\Event\BlockEvent;
use Integrated\Common\Content\Form\Events;
use Integrated\Common\Services\MainFlusher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BlockImageListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly DocumentManager $documentManager,
        private readonly MainFlusher $flusher
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            Events::BLOCK_VALIDATE => ['buildForm', -60],
        ];
    }

    public function buildForm(BlockEvent $event): void
    {
        $block = $this->documentManager->getRepository(Block::class)->find($event->getBlock()->getId());

        if ($block === null) {
            $block = $event->getBlock();
        }

        foreach ($block->getRelations() as $relation) {
            $block->removeRelation($relation);
        }

        if (method_exists($block, 'getSubscriptions')) {
            foreach ($block->getSubscriptions() as $subscription) {
                $this->processImage($block, function () use ($subscription) {
                    return $subscription->getImage();
                }, '__subscription_image');
            }
        }

        if (method_exists($block, 'getImage')) {
            $this->processImage($block, function () use ($block) {
                return $block->getImage();
            }, '__image');
        }

        if (method_exists($block, 'getImageOverlay')) {
            $this->processImage($block, function () use ($block) {
                return $block->getImageOverlay();
            }, '__image_overlay');
        }

        $this->flusher->flush();
    }

    private function processImage($block, $imageGetter, $relationId)
    {
        $image = $this->getImageFromBlock($imageGetter);
        if ($image) {
            $this->addRelationToBlock($block, $image, $relationId);
        }
    }

    private function getImageFromBlock($imageGetter)
    {
        if ($imageGetter() != null) {
            return $this->documentManager->getRepository(Content::class)->find(
                $imageGetter()->getId()
            );
        }

        return null;
    }

    private function addRelationToBlock($block, $image, $relationId)
    {
        if (!$block->getRelation($relationId)) {
            $block->addRelation(
                (new Relation())
                    ->setRelationId($relationId)
                    ->setRelationType('embedded')
                    ->addReference($image)
            );
        } else {
            $relation = $block->getRelation($relationId);
            $relation->addReference($image);
        }
    }
}
