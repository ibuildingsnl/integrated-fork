<?php

namespace Integrated\Bundle\NewsletterBundle\EventListener;

use Integrated\Bundle\ContentBundle\Form\Type\CheckboxSwitcherType;
use Integrated\Bundle\ContentBundle\Form\Type\CustomFieldsType;
use Integrated\Common\Content\Form\Event\BuilderEvent;
use Integrated\Common\Content\Form\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ExcludeFromNewsletterListener implements EventSubscriberInterface
{
    public function __construct(private readonly array $allowedTypes)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::POST_BUILD => 'onPostBuild',
        ];
    }

    public function onPostBuild(BuilderEvent $event): void
    {
        if (!\in_array($event->getContentType()->getClass(), $this->allowedTypes)) {
            return;
        }

        $builder = $event->getBuilder();

        if (!$builder->has('customFields')) {
            $builder->add('customFields', CustomFieldsType::class, [
                'contentType' => $event->getContentType(),
                'attr' => [
                    'style' => 'editor',
                ],
            ]);
        }

        $builder->get('customFields')->add('ExcludeFromNewsletters', CheckboxSwitcherType::class, [
            'required' => false,
            'attr' => ['location' => 'options', 'align_with_widget' => true, 'style' => 'horizontal' ],
        ]);
    }
}
