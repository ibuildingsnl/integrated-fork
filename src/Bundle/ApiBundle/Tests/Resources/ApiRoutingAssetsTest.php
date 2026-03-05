<?php

declare(strict_types=1);

namespace Integrated\Bundle\ApiBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

class ApiRoutingAssetsTest extends TestCase
{
    public function testWebsiteAggregateImportsApiRoutes(): void
    {
        $routing = file_get_contents(__DIR__.'/../../../IntegratedBundle/Resources/config/routing.website.xml');

        self::assertIsString($routing);
        self::assertStringContainsString('@IntegratedApiBundle/Resources/config/routing.xml', $routing);
    }

    public function testApiRoutesUseDynamicProviders(): void
    {
        $public = file_get_contents(__DIR__.'/../../Resources/config/routing/public.xml');
        $admin = file_get_contents(__DIR__.'/../../Resources/config/routing/admin.xml');

        self::assertIsString($public);
        self::assertStringContainsString('type="integrated_api_public_v1"', $public);

        self::assertIsString($admin);
        self::assertStringContainsString('type="integrated_api_admin_v1"', $admin);
    }
}
