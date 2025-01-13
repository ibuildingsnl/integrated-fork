<?php

namespace Integrated\Bundle\FormTypeBundle\EventListener;

use Integrated\Bundle\ContentBundle\Std\DOMDocument;
use Integrated\Bundle\FormTypeBundle\Form\Type\EditorType;
use Integrated\Common\Content\ContentInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class EditorSocialDataEventListener implements EventSubscriberInterface
{
    public function __construct()
    {
    }

    public static function getSubscribedEvents()
    {
        return [
            FormEvents::POST_SUBMIT => 'setSocialEmbedMeta',
        ];
    }

    public function setSocialEmbedMeta(FormEvent $event)
    {
        $form = $event->getForm();

        if (!$form->isRoot()) {
            return;
        }

        $content = $event->getData();

        if (!$content instanceof ContentInterface) {
            return;
        }

        $socialEmbeds = [];

        foreach ($event->getForm() as $child) {
            if ($child->getConfig()->getType()->getInnerType() instanceof EditorType) {
                $data = $child->getData();

                if (\is_string($data) && trim($data)) {
                    foreach (self::read($data) as $provider) {
                        $socialEmbeds[$provider] = true;
                    }
                }
            }
        }

        foreach ($socialEmbeds as $provider => $value) {
            $provider = strtolower($provider);
            $content->getMetadata()->set("embed_{$provider}", $value);
        }
    }

    public static function read($html)
    {
        $document = new DOMDocument();
        @$document->loadHTML($html);

        $xpath = new \DOMXPath($document);
        $query = "//*[contains(@class, 'embed-content')]";

        foreach ($xpath->query($query) as $elm) {
            $classes = explode(' ', $elm->getAttribute('class'));
            foreach ($classes as $class) {
                if ($class !== 'embed-content') {
                    yield $class;
                }
            }
        }
    }
}
