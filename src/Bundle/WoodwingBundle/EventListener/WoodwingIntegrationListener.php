<?php

namespace Integrated\Bundle\WoodwingBundle\EventListener;

use Integrated\Bundle\ContentBundle\Doctrine\ContentTypeManager;
use Integrated\Bundle\ContentBundle\Document\Content\Article;
use Integrated\Bundle\ContentBundle\Document\Content\ContentRepository;
use Integrated\Bundle\ContentBundle\Document\Content\PublicationRepositoryInterface;
use Integrated\Bundle\WoodwingBundle\Document\WoodwingPost;
use Integrated\Common\Content\Form\Event\ValidationEvent;
use Integrated\Common\Content\Form\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class WoodwingIntegrationListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly ContentTypeManager $types,
        private readonly ContentRepository $content,
        private readonly PublicationRepositoryInterface $publications,
        private readonly string $contentType,
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
        $original = $event->getContent();
        if (!$original instanceof Article) {
            return;
        }
        $type = $this->types->getType($this->contentType);
        $this->content->add($original);
        foreach ($this->publications->forContent($original) as $publication) {
            if ($publication->getChannel()->getType()->getName() !== 'WoodWing') {
                continue;
            }
            if (!$publication->getSettings()['send'] ?? false) {
                continue;
            }
            $publication->setSetting('send', false);

            $woodwingPost = $type->create();
            assert($woodwingPost instanceof WoodwingPost);
            $woodwingPost->setOriginal($original);
            $woodwingPost->populate();
            $woodwingPost->edition = $this->content->find($publication->getSettings()['edition'] ?? null);
            $woodwingPost->layout = $this->content->find($publication->getSettings()['layout'] ?? null);
            $this->content->add($woodwingPost);
        }
    }
}
