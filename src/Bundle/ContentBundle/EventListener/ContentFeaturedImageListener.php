<?php

namespace Integrated\Bundle\ContentBundle\EventListener;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Document\Content\Embedded\Relation;
use Integrated\Bundle\ContentBundle\Event\ContentDistributedEvent;
use Integrated\Common\Content\ContentInterface;
use Integrated\Common\Content\Embedded\RelationInterface;
use Integrated\Common\Content\Form\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ContentFeaturedImageListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly DocumentManager $documentManager,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::CONTENT_DISTRIBUTED => ['buildForm', -60],
        ];
    }

    public function buildForm(ContentDistributedEvent $event): void
    {
        $content = $this->documentManager->getRepository(Content::class)->find($event->getContent()->getId());

        if ($content === null) {
            $content = $event->getContent();
        }

        $changed = false;

        if (method_exists($content, 'getFeaturedImage')) {
            if ($content->getFeaturedImage() != null) {
                $image = $this->documentManager->getRepository(Content::class)->find(
                    $content->getFeaturedImage()->getId()
                );
                $changed = $this->syncSingleReferenceRelation($content, '__featured_image', 'embedded', $image) || $changed;
            }
        }

        if (method_exists($content, 'getPicture')) {
            if ($content->getPicture() != null) {
                $image = $this->documentManager->getRepository(Content::class)->find($content->getPicture()->getId());
                $changed = $this->syncSingleReferenceRelation($content, '__picture', 'embedded', $image) || $changed;
            }
        }

        if ($changed) {
            $this->documentManager->flush();
        }
    }

    private function syncSingleReferenceRelation(Content $content, string $relationId, string $relationType, ?ContentInterface $reference): bool
    {
        $existing = $content->getRelation($relationId);
        if ($reference === null) {
            if ($existing instanceof RelationInterface) {
                $content->removeRelation($existing);

                return true;
            }

            return false;
        }

        if ($existing instanceof RelationInterface) {
            $references = $existing->getReferences();
            $existingReference = \count($references) === 1 ? $references[0] : null;
            if ($existingReference instanceof ContentInterface
                && $existingReference->getId() === $reference->getId()
                && $existing->getRelationType() === $relationType
            ) {
                return false;
            }

            $content->removeRelation($existing);
        }

        $content->addRelation(
            (new Relation())
                ->setRelationId($relationId)
                ->setRelationType($relationType)
                ->addReference($reference)
        );

        return true;
    }
}
