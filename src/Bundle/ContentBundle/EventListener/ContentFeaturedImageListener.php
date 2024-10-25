<?php

namespace Integrated\Bundle\ContentBundle\EventListener;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Document\Content\Embedded\Relation;
use Integrated\Bundle\ContentBundle\Event\ContentDistributedEvent;
use Integrated\Common\Content\Form\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ContentFeaturedImageListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly DocumentManager $documentManager,
    ) {
    }

    public static function getSubscribedEvents()
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

        if (method_exists($content, 'getFeaturedImage')) {
            if ($content->getFeaturedImage() != null) {
                $image = $this->documentManager->getRepository(Content::class)->find(
                    $content->getFeaturedImage()->getId()
                );

                $content->addRelation(
                    (new Relation())
                        ->setRelationId('__featured_image')
                        ->setRelationType('embedded')
                        ->addReference($image)
                );

                $this->documentManager->flush();
            }
        }

        if (method_exists($content, 'getPicture')) {
            if ($content->getPicture() != null) {
                $image = $this->documentManager->getRepository(Content::class)->find($content->getPicture()->getId());

                $content->addRelation(
                    (new Relation())
                        ->setRelationId('__picture')
                        ->setRelationType('embedded')
                        ->addReference($image)
                );

                $this->documentManager->flush();
            }
        }
    }
}
