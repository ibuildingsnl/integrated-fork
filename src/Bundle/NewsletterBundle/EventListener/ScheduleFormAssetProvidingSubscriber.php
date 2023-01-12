<?php

namespace Integrated\Bundle\NewsletterBundle\EventListener;

use Integrated\Bundle\AssetBundle\Manager\AssetManager;
use Integrated\Common\Content\Form\Event\BuilderEvent;
use Integrated\Common\Content\Form\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ScheduleFormAssetProvidingSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly AssetManager $js,
        private readonly AssetManager $css,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            Events::POST_BUILD => 'onPostBuild',
        ];
    }

    public function onPostBuild(BuilderEvent $event): void
    {
        if ($event->getContentType()->getName() !== 'Newsletter') {
            return;
        }
        $this->js->add('bundles/integratednewsletter/js/frequencies.js');
        $this->css->add('bundles/integratednewsletter/css/frequencies.css');
    }
}
