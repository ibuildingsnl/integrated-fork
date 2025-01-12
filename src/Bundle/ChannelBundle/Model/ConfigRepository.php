<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ChannelBundle\Model;

use Doctrine\ORM\EntityRepository;
use Integrated\Common\Channel\Connector\Config\ConfigInterface;
use Integrated\Common\Channel\Connector\Config\ConfigManagerInterface;
use Integrated\Common\Content\Channel\ChannelInterface;

/**
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 */
class ConfigRepository extends EntityRepository implements ConfigManagerInterface
{
    public function create()
    {
        return $this->_class->getReflectionClass()->newInstance();
    }

    public function persist(ConfigInterface $object, $flush = true)
    {
        if (!$this->_class->getReflectionClass()->isInstance($object)) {
            throw new \InvalidArgumentException(
                \sprintf('The object (%s) is not a instance of %s', $object::class, $this->getClassName())
            );
        }

        $this->_em->persist($object);

        if ($flush) {
            $this->_em->flush();
        }
    }

    public function remove(ConfigInterface $object, $flush = true)
    {
        if (!$this->_class->getReflectionClass()->isInstance($object)) {
            throw new \InvalidArgumentException(
                \sprintf('The object (%s) is not a instance of %s', $object::class, $this->getClassName())
            );
        }

        $this->_em->remove($object);

        if ($flush) {
            $this->_em->flush();
        }
    }

    public function findByAdaptor($criteria)
    {
        return $this->findBy([
            'adaptor' => $criteria,
        ]);
    }

    public function findByChannel($criteria)
    {
        if ($criteria instanceof ChannelInterface) {
            $criteria = $criteria->getId();
        }

        $expr = $this->_em->getExpressionBuilder();

        return $this->createQueryBuilder('r')
            ->where($expr->like('r.channels', $expr->literal('%'.json_encode($criteria).'%')))
            ->getQuery()
            ->getResult();
    }
}
