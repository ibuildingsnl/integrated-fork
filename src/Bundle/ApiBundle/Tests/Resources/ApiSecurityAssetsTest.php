<?php

declare(strict_types=1);

namespace Integrated\Bundle\ApiBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

class ApiSecurityAssetsTest extends TestCase
{
    public function testSecurityConfigContainsApiFirewall(): void
    {
        $security = file_get_contents(__DIR__.'/../../../../../../../../../config/packages/security.yaml');

        self::assertIsString($security);
        self::assertStringContainsString('api_admin:', $security);
        self::assertStringContainsString('integrated_api.security.bearer_token_authenticator', $security);
        self::assertStringContainsString('{ path: ^/api/admin, roles: ROLE_API }', $security);
    }
}
