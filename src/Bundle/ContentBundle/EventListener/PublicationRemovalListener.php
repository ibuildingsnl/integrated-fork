<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\EventListener;

use Integrated\Bundle\ContentBundle\Document\Content\PublicationRepositoryInterface;
use Integrated\Bundle\ContentBundle\Event\ContentDeletedEvent;
use Integrated\Common\Content\Form\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PublicationRemovalListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly PublicationRepositoryInterface $publicationRepository
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            Events::CONTENT_DELETED => ['removePublications', -50],
        ];
    }

    public function removePublications(ContentDeletedEvent $event): void
    {
        $publications = $this->publicationRepository->forContent($event->getContent());
        foreach ($publications as $publication) {
            $this->publicationRepository->remove($publication);
        }
    }
}
