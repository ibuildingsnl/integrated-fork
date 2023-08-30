<?php

namespace Integrated\Bundle\BrandBundle\EventListener;

use Integrated\Bundle\BrandBundle\Form\Type\BrandChoiceType;
use Integrated\Common\Content\ChannelableInterface;
use Integrated\Common\Content\Form\Event\BuilderEvent;
use Integrated\Common\Content\Form\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ContentBrandIntegrationListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly AuthorizationCheckerInterface $authorizationChecker,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            Events::POST_BUILD => ['buildForm', -61],
        ];
    }

    public function buildForm(BuilderEvent $event)
    {
        $form = $event->getBuilder();
        $content = $form->getData();
        if (!$content instanceof ChannelableInterface || !$form->has('channels')) {
            return;
        }
        $form->add('brands', BrandChoiceType::class, [
            'mapped' => false,
            'channel_choices' => $form->get('channels')->getOption('choices'),
            'channel_choice_attr' => $form->get('channels')->getOption('choice_attr'),
            'attr' => [
                'location' => 'sidebar',
                'style' => 'sidebar',
                'icon' => 'network-alt',
            ]
        ]);
        $form->addEventSubscriber(new BrandChannelsAssignmentListener($this->authorizationChecker));
        $form->remove('channels');
    }
}
