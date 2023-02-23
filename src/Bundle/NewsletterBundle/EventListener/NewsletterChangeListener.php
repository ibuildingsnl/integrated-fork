<?php

namespace Integrated\Bundle\NewsletterBundle\EventListener;

use Integrated\Bundle\NewsletterBundle\Document\Newsletter;
use Integrated\Common\Content\Form\Event\ValidationEvent;
use Integrated\Common\Content\Form\Events;
use Stratadox\Clock\Clock;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class NewsletterChangeListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly Clock $clock,
        private readonly string $storageDirectory,
    ) {
    }

    public static function getSubscribedEvents()
    {
        return [
            Events::POST_VALIDATE => 'onPostValidate',
        ];
    }

    public function onPostValidate(ValidationEvent $event): void
    {
        if ($event->getContent() instanceof Newsletter) {
            // @todo check if in generation window & if so, render & store newsletter
            dd($event->getContent());
        }
    }
}
