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

use Integrated\Common\Content\Form\Event\BuilderEvent;
use Integrated\Common\Content\Form\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class FeaturedExpirationSelectionListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            Events::POST_BUILD => 'onPostBuild',
        ];
    }

    public function onPostBuild(BuilderEvent $event): void
    {
        $contentType = $event->getContentType();
        $builder = $event->getBuilder();

        if (!$contentType->getOption('featured_expiration') || !$builder->has('featured')) {
            return;
        }

        $builder->add('featured_expiration', IntegerType::class, [
            'property_path' => 'featuredExpiration',
            'required' => false,
            'label' => 'Featured expires in x days',
            'attr' => [
                'placeholder' => 'Featured expires in x days',
                'location' => 'options',
                'style' => 'inline',
                'align_with_widget' => true,
            ],
        ]);
    }
}
