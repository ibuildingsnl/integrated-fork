<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Doctrine\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Doctrine\ODM\MongoDB\Events;
use Integrated\Bundle\ContentBundle\StaticContent\StaticContentRepository;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class StaticContentListener implements EventSubscriber
{
    /**
     * @var StaticContentRepository
     */
    private $repository;

    /**
     * StaticContentListener constructor.
     *
     * @param StaticContentRepository $repository
     */
    public function __construct(StaticContentRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return [
            Events::preRemove,
        ];
    }

    /**
     * @param LifecycleEventArgs $args
     *
     * @throws AccessDeniedException
     */
    public function preRemove(LifecycleEventArgs $args)
    {
        $document = $args->getDocument();

        if ($this->repository->has($document)) {
            throw new AccessDeniedException();
        }
    }
}
