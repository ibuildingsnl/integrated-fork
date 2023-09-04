<?php

namespace Integrated\Bundle\BrandBundle\EventListener;

use Integrated\Bundle\BrandBundle\Document\BrandRepository;
use Integrated\Bundle\BrandBundle\Document\ChannelLink;
use Integrated\Bundle\ContentBundle\Document\Channel\ChannelRepository;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Common\ContentType\ContentTypeInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class BrandDefaultDataListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly ContentTypeInterface $contentType,
        private readonly BrandRepository $brands,
        private readonly ChannelRepository $channels,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::POST_SET_DATA => 'setDefaultBrandsAndChannels',
        ];
    }

    public function setDefaultBrandsAndChannels(FormEvent $event): void
    {
        $form = $event->getForm();
        $content = $event->getData();

        if (!$form->has('brands') || !$content instanceof Content) {
            return;
        }

        $brandsForm = $form->get('brands');

        $defaultChannels = array_map(
            fn (array $channel) => $this->channels->find($channel['id']),
            $this->contentType->getOption('channels')['defaults'] ?? [],
        );

        $brandsData = [];
        foreach ($this->brands->all() as $brand) {
            if (!$brandsForm->has($brand->getId())) {
                continue;
            }

            $condition = fn (ChannelLink $link) => \in_array($link->channel, $defaultChannels) || $link->default;
            $publish = true;

            if ($brand->hasPublished($content)) {
                $condition = fn (ChannelLink $link) => $content->hasChannel($link->channel);
            } elseif (\count($content->getChannels()) || !$brand->hasAtLeastOneOfChannels(...$defaultChannels)) {
                $publish = false;
            }

            $brandsData[$brand->getId()] = [
                'publish' => $publish,
                'channels' => array_filter($brand->getChannelLinks()->toArray(), $condition),
            ];
        }

        $brandsForm->setData($brandsData);
    }
}
