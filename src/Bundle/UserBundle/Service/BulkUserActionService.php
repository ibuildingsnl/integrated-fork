<?php

namespace Integrated\Bundle\UserBundle\Service;

use Integrated\Bundle\UserBundle\Model\GroupInterface;
use Integrated\Bundle\UserBundle\Model\ScopeInterface;
use Integrated\Bundle\UserBundle\Model\UserInterface;

class BulkUserActionService
{
    public const ACTION_ENABLE_LOGIN = 'enable_login';
    public const ACTION_DISABLE_LOGIN = 'disable_login';
    public const ACTION_ASSIGN_GROUP = 'assign_group';
    public const ACTION_CHANGE_SCOPE = 'change_scope';
    public const ACTION_RESET_2FA = 'reset_2fa';

    /**
     * @param iterable<mixed> $users
     */
    public function apply(iterable $users, string $action, ?GroupInterface $group = null, ?ScopeInterface $scope = null): int
    {
        $updated = 0;

        foreach ($users as $user) {
            if (!$user instanceof UserInterface) {
                continue;
            }

            switch ($action) {
                case self::ACTION_ENABLE_LOGIN:
                    $user->setEnabled(true);
                    ++$updated;
                    break;

                case self::ACTION_DISABLE_LOGIN:
                    $user->setEnabled(false);
                    ++$updated;
                    break;

                case self::ACTION_ASSIGN_GROUP:
                    if ($group) {
                        $user->addGroup($group);
                        ++$updated;
                    }
                    break;

                case self::ACTION_CHANGE_SCOPE:
                    if ($scope) {
                        $user->setScope($scope);
                        ++$updated;
                    }
                    break;

                case self::ACTION_RESET_2FA:
                    $user->setGoogleAuthenticatorSecret(null);
                    $user->setGoogleAuthenticatorEnabled(false);
                    ++$updated;
                    break;
            }
        }

        return $updated;
    }
}
