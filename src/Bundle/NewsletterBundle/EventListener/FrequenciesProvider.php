<?php

namespace Integrated\Bundle\NewsletterBundle\EventListener;

use Integrated\Bundle\AssetBundle\Manager\AssetManager;
use Integrated\Bundle\NewsletterBundle\Document\Newsletter;
use Integrated\Common\Content\Form\Event\BuilderEvent;
use Integrated\Common\Content\Form\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class FrequenciesProvider implements EventSubscriberInterface
{
    public function __construct(
        private readonly AssetManager $js,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::POST_BUILD => 'onPostBuild',
        ];
    }

    public function onPostBuild(BuilderEvent $event): void
    {
        if ($event->getContentType()->getClass() !== Newsletter::class) {
            return;
        }
        $this->js->add('bundles/integratednewsletter/js/frequencies.js');
    }
}
