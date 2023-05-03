<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Common\Security;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

trait PermissionTrait
{
    /**
     * @var ArrayCollection
     */
    protected $permissions;

    private function initPermissions(): void
    {
        if (!$this->permissions instanceof Collection) {
            $this->permissions = new ArrayCollection();
        }
    }

    /**
     * @return Permission[]
     */
    public function getPermissions()
    {
        $this->initPermissions();

        return $this->permissions->toArray();
    }

    /**
     * @return $this
     */
    public function setPermission(iterable $permissions)
    {
        $this->permissions = new ArrayCollection();

        foreach ($permissions as $permission) {
            $this->addPermission($permission);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function addPermission(Permission $permission)
    {
        $this->initPermissions();

        if ($exist = $this->getPermission($permission->getGroup())) {
            $exist->setMask($permission->getMask());
        } else {
            $this->permissions->add($permission);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function removePermission(Permission $permission)
    {
        $this->initPermissions();
        $this->permissions->removeElement($permission);

        return $this;
    }

    /**
     * @param int $groupId
     *
     * @return Permission
     */
    public function getPermission($groupId)
    {
        $this->initPermissions();

        return $this->permissions->filter(function ($permission) use ($groupId) {
            if ($permission instanceof Permission) {
                if ($permission->getGroup() == $groupId) {
                    return true;
                }
            }

            return false;
        })->first();
    }
}
