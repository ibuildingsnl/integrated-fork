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

use Integrated\Bundle\ContentBundle\Document\Content\Event;
use Integrated\Common\Content\Form\Event\BuilderEvent;
use Integrated\Common\Content\Form\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class EventDateDefaultTimeListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            Events::POST_BUILD => 'onPostBuild',
        ];
    }

    public function onPostBuild(BuilderEvent $event): void
    {
        if (!is_a($event->getContentType()->getClass(), Event::class, true)) {
            return;
        }

        $event->getBuilder()->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit']);
    }

    public function onPreSubmit(FormEvent $event): void
    {
        $data = $event->getData();

        if (!\is_array($data)) {
            return;
        }

        foreach (['startDate', 'endDate'] as $field) {
            if (!isset($data[$field]) || !\is_array($data[$field])) {
                continue;
            }

            $date = trim((string) ($data[$field]['date'] ?? ''));
            $time = trim((string) ($data[$field]['time'] ?? ''));

            if ('' !== $date && '' === $time) {
                $data[$field]['time'] = '00:00';
            }
        }

        $event->setData($data);
    }
}
