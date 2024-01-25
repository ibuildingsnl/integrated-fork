<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Common\Security\Resolver;

use Integrated\Bundle\UserBundle\Model\GroupableInterface;
use Integrated\Common\Security\PermissionInterface;

class PermissionResolver
{
    /**
     * @param PermissionInterface[] $permissions
     */
    public static function getPermissions(GroupableInterface $user, $permissions = []): array
    {
        $groups = [];

        foreach ($user->getGroups() as $group) {
            $groups[$group->getId()] = $group->getId();

            if (is_array($group->getRoles()) && \in_array('ROLE_ADMIN', $group->getRoles())) {
                return [
                    'read' => true,
                    'write' => true,
                ];
            }
        }

        $mask = 0;
        $readPermissionRequired = false;
        $writePermissionRequired = false;

        if ($groups) {
            foreach ($permissions as $permission) {
                if (PermissionInterface::READ === ($permission->getMask() & PermissionInterface::READ)) {
                    $readPermissionRequired = true;
                }

                if (PermissionInterface::WRITE === ($permission->getMask() & PermissionInterface::WRITE)) {
                    $writePermissionRequired = true;
                }

                if (isset($groups[$permission->getGroup()])) {
                    $mask |= $permission->getMask();
                }
            }
        }

        return [
            'read' => !$readPermissionRequired || PermissionInterface::READ === ($mask & PermissionInterface::READ),
            'write' => !$writePermissionRequired || PermissionInterface::WRITE === ($mask & PermissionInterface::WRITE),
        ];
    }
}
