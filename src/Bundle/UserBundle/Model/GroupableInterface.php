<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\UserBundle\Model;

/**
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 */
interface GroupableInterface
{
    public function addGroup(GroupInterface $group);

    public function removeGroup(GroupInterface $group);

    /**
     * @return bool
     */
    public function hasGroup(GroupInterface $group);

    /**
     * @return GroupInterface[]
     */
    public function getGroups();

    /**
     * @param GroupInterface[] $groups
     */
    public function setGroups($groups);
}
