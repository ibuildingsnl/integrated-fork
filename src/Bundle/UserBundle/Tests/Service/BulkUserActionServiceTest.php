<?php

namespace Integrated\Bundle\UserBundle\Tests\Service;

use Integrated\Bundle\UserBundle\Model\Group;
use Integrated\Bundle\UserBundle\Model\Scope;
use Integrated\Bundle\UserBundle\Model\User;
use Integrated\Bundle\UserBundle\Service\BulkUserActionService;
use PHPUnit\Framework\TestCase;

class BulkUserActionServiceTest extends TestCase
{
    public function testEnableAndDisableLogin(): void
    {
        $service = new BulkUserActionService();
        $user = new User();
        $user->setEnabled(false);

        $enabled = $service->apply([$user], BulkUserActionService::ACTION_ENABLE_LOGIN);
        self::assertSame(1, $enabled);
        self::assertTrue($user->isEnabled());

        $disabled = $service->apply([$user], BulkUserActionService::ACTION_DISABLE_LOGIN);
        self::assertSame(1, $disabled);
        self::assertFalse($user->isEnabled());
    }

    public function testAssignGroup(): void
    {
        $service = new BulkUserActionService();
        $user = new User();
        $group = new Group('12');
        $group->setName('Editors');

        $updated = $service->apply([$user], BulkUserActionService::ACTION_ASSIGN_GROUP, $group);

        self::assertSame(1, $updated);
        self::assertTrue($user->hasGroup($group));
    }

    public function testChangeScope(): void
    {
        $service = new BulkUserActionService();
        $user = new User();
        $scope = new Scope();
        $scope->setName('Integrated');

        $updated = $service->apply([$user], BulkUserActionService::ACTION_CHANGE_SCOPE, null, $scope);

        self::assertSame(1, $updated);
        self::assertSame($scope, $user->getScope());
    }

    public function testResetTwoFactor(): void
    {
        $service = new BulkUserActionService();
        $user = new User();
        $user->setGoogleAuthenticatorSecret('my-secret');
        $user->setGoogleAuthenticatorEnabled(true);

        $updated = $service->apply([$user], BulkUserActionService::ACTION_RESET_2FA);

        self::assertSame(1, $updated);
        self::assertNull($user->getGoogleAuthenticatorSecret());
        self::assertFalse($user->isGoogleAuthenticatorEnabled());
    }
}

