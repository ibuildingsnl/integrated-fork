<?php

namespace Integrated\Bundle\NewsletterBundle\EventListener;

use Integrated\Bundle\NewsletterBundle\Document\Newsletter;
use Integrated\Bundle\NewsletterBundle\Service\NewsletterGenerator;
use Integrated\Common\Content\Form\Event\ValidationEvent;
use Integrated\Common\Content\Form\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class NewsletterChangeListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly NewsletterGenerator $generator
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::POST_VALIDATE => 'onPostValidate',
        ];
    }

    public function onPostValidate(ValidationEvent $event): void
    {
        $newsletter = $event->getContent();
        if ($newsletter instanceof Newsletter) {
            $this->generator->maybeGenerate($newsletter);
        }
    }
}
