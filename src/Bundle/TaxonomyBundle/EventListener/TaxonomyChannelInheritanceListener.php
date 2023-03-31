<?php

namespace Integrated\Bundle\TaxonomyBundle\EventListener;

use Integrated\Bundle\ContentBundle\Document\Content\Taxonomy;
use Integrated\Bundle\TaxonomyBundle\Domain\TaxonomyRepositoryInterface;
use Integrated\Common\Content\Form\Event\ValidationEvent;
use Integrated\Common\Content\Form\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class TaxonomyChannelInheritanceListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly TaxonomyRepositoryInterface $taxonomies,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::POST_VALIDATE => 'afterValidation',
        ];
    }

    public function afterValidation(ValidationEvent $event): void
    {
        $taxonomy = $event->getContent();

        if (!$taxonomy instanceof Taxonomy || null === $taxonomy->getParentID()) {
            return;
        }

        $parent = $this->taxonomies->byId($taxonomy->getParentID());

        $taxonomy->setChannels($parent->getChannels());
    }
}
