<?php

declare(strict_types=1);

namespace Integrated\Bundle\ApiBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

class ApiBundleWiringTest extends TestCase
{
    public function testApiBundleIsRegisteredInApplicationKernel(): void
    {
        $bundles = require __DIR__.'/../../../../../../../../../config/bundles.php';

        self::assertArrayHasKey(\Integrated\Bundle\ApiBundle\IntegratedApiBundle::class, $bundles);
    }
}
