<?php

namespace Integrated\Bundle\BrandBundle\EventListener;

use Integrated\Bundle\AssetBundle\Manager\AssetManager;
use Integrated\Bundle\BrandBundle\Document\BrandRepository;
use Integrated\Bundle\BrandBundle\Form\Type\BrandChoiceType;
use Integrated\Bundle\ContentBundle\Document\Channel\ChannelRepository;
use Integrated\Common\Content\ChannelableInterface;
use Integrated\Common\Content\Form\Event\BuilderEvent;
use Integrated\Common\Content\Form\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ContentBrandIntegrationListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly AuthorizationCheckerInterface $authorizationChecker,
        private readonly BrandRepository $brands,
        private readonly ChannelRepository $channels,
        private readonly AssetManager $js,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::POST_BUILD => ['buildForm', -80],
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
            'priority' => 990,
            'mapped' => false,
            'channel_choices' => $form->get('channels')->getOption('choices'),
            'channel_choice_attr' => $form->get('channels')->getOption('choice_attr'),
            'attr' => [
                'location' => 'sidebar',
                'style' => 'sidebar',
                'icon' => 'network-reverse',
                'class' => 'brands',
                'state' => 'show',
            ],
        ]);
        $form->addEventSubscriber(new BrandChannelsAssignmentListener($this->authorizationChecker));
        $form->addEventSubscriber(new BrandDefaultDataListener($event->getContentType(), $this->brands, $this->channels));
        $form->remove('channels');

        $this->js->add('bundles/integratedbrand/js/brand_channel_selection.js');
        $this->js->add('bundles/integratedbrand/js/primary_channel.js');
    }
}
