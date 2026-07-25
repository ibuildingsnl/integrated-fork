<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Common\Doctrine\Id;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Id\AbstractIdGenerator;
use Symfony\Component\Uid\Uuid;

/**
 * Generates a UUID (v4) in PHP.
 *
 * Replaces the Doctrine "UUID" generator strategy, which relied on the
 * database's `UUID()` function and is no longer supported since
 * doctrine/dbal 3.x.
 */
class UuidGenerator extends AbstractIdGenerator
{
    public function generateId(EntityManagerInterface $em, $entity): string
    {
        return Uuid::v4()->toRfc4122();
    }
}
