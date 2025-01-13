<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\UserBundle\Doctrine\Subscriber;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Integrated\Bundle\UserBundle\Model\User;

/**
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 */
#[AsDoctrineListener(event: Events::postLoad)]
class OrmRelationSubscriber
{
    /**
     * @var ManagerRegistry
     */
    protected $dm;

    public function __construct(ManagerRegistry $dm)
    {
        $this->dm = $dm;
    }

    public function postLoad(LifecycleEventArgs $args)
    {
        $object = $args->getObject();

        if (!$object instanceof User) {
            return;
        }

        $metadata = $args->getObjectManager()->getClassMetadata($object::class);

        $prop = $metadata->getReflectionClass()->getProperty('relation');
        $prop->setAccessible(true);

        $id = $prop->getValue($object);

        $prop = $metadata->getReflectionClass()->getProperty('relation_instance');
        $prop->setAccessible(true);
        $prop->setValue($object, $this->dm->getManager()->getRepository('Integrated\\Bundle\\ContentBundle\\Document\\Content\\Content')->find($id));
    }
}
