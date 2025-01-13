<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Form\EventListener;

use Integrated\Common\Content\Channel\ChannelInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class ChannelPermissionListener implements EventSubscriberInterface
{
    /**
     * @var ChannelInterface[]
     */
    private $notPermittedChannels;

    /**
     * @param ChannelInterface[] $notPermittedChannels
     */
    public function __construct(array $notPermittedChannels)
    {
        $this->notPermittedChannels = $notPermittedChannels;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::PRE_SUBMIT => 'onPreSubmit',
        ];
    }

    public function onPreSubmit(FormEvent $event)
    {
        if (!\count($this->notPermittedChannels)) {
            return;
        }

        $data = $event->getData();

        if (isset($data['channels']) && \is_array($data['channels'])) {
            foreach ($data['channels'] as $key => $value) {
                if (isset($this->notPermittedChannels[$value])) {
                    unset($data['channels'][$key]);
                }
            }
        }

        $event->setData($data);
    }
}
