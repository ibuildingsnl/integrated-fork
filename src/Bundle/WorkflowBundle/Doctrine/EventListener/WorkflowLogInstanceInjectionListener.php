<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\WorkflowBundle\Doctrine\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\Persistence\ManagerRegistry;
use Integrated\Bundle\WorkflowBundle\Entity\Workflow\Log;

/**
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 */
class WorkflowLogInstanceInjectionListener implements EventSubscriber
{
    /**
     * @var ManagerRegistry
     */
    protected $manager;

    public function __construct(ManagerRegistry $manager)
    {
        $this->manager = $manager;
    }

    public function getSubscribedEvents()
    {
        return [
            Events::postLoad,
        ];
    }

    /**
     * Add the user instance or a proxy to this user instance to the Log entity.
     */
    public function postLoad(LifecycleEventArgs $args)
    {
        $object = $args->getObject();

        if (!$object instanceof Log) {
            return;
        }

        $metadata = $args->getObjectManager()->getClassMetadata($object::class);

        $prop = $metadata->getReflectionClass()->getProperty('user_class');
        $prop->setAccessible(true);

        $class = $prop->getValue($object);

        $prop = $metadata->getReflectionClass()->getProperty('user_id');
        $prop->setAccessible(true);

        $id = $prop->getValue($object);

        $prop = $metadata->getReflectionClass()->getProperty('user_instance');
        $prop->setAccessible(true);
        $prop->setValue($object, $this->getInstance($class, $id));
    }

    /**
     * Try to get a reference to the user object else fetch it immediately from the
     * repository.
     *
     * @param string $class
     * @param string $id
     *
     * @return object
     */
    protected function getInstance($class, $id)
    {
        if (!$class || !$id) {
            return null;
        }

        $manager = $this->manager->getManagerForClass($class);

        if (method_exists($manager, 'getReference')) {
            return $manager->getReference($class, $id);
        }

        return $manager->getRepository($class)->find($id);
    }
}
