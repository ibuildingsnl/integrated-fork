<?php

namespace Integrated\Bundle\ContentBundle\EventListener;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Document\Content\Embedded\Relation;
use Integrated\Common\Content\Form\Event\ValidationEvent;
use Integrated\Common\Content\Form\Events;
use Integrated\Common\Services\MainFlusher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ContentFeaturedImageListener implements EventSubscriberInterface
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
            Events::POST_VALIDATE => ['buildForm', -60],
        ];
    }

    public function buildForm(ValidationEvent $event): void
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

                $this->flusher->flush();
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

                $this->flusher->flush();
            }
        }
    }
}
