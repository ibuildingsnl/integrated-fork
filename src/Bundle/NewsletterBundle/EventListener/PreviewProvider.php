<?php

namespace Integrated\Bundle\NewsletterBundle\EventListener;


use Integrated\Bundle\AssetBundle\Manager\AssetManager;
use Integrated\Bundle\NewsletterBundle\Document\Newsletter;
use Integrated\Common\Content\Form\Event\BuilderEvent;
use Integrated\Common\Content\Form\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\RouterInterface;

class PreviewProvider implements EventSubscriberInterface
{
    public function __construct(
        private readonly AssetManager $js,
        private readonly AssetManager $css,
        private readonly RouterInterface $router,
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
        if ($event->getContentType()->getName() !== 'Newsletter') {
            return;
        }

        $data = $event->getBuilder()->getData();

        if ($data instanceof Newsletter && $data->getId()) {
            $this->css->add('bundles/integratednewsletter/css/preview.css');
            $this->js->add(sprintf(
                'window.previewUrl = "%s";',
                $this->router->generate('integrated_newsletter_preview', ['id' => $data->getId()])
            ), true);
            $this->js->add('bundles/integratednewsletter/js/preview.js');
        }
    }
}
