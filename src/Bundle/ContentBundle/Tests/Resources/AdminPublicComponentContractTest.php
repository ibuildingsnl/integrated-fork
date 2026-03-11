<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Resources;

use Integrated\Bundle\ContentBundle\Tests\Support\AdminComponentContract;
use PHPUnit\Framework\TestCase;

final class AdminPublicComponentContractTest extends TestCase
{
    public function testAdminComponentGuideDocumentsThePublicComponentKeys(): void
    {
        $guide = file_get_contents(__DIR__.'/../../Twig/Component/Admin/README.md');

        self::assertIsString($guide);

        foreach (array_keys(AdminComponentContract::publicComponents()) as $componentKey) {
            self::assertStringContainsString('`'.$componentKey.'`', $guide);
        }
    }

    public function testLightweightRenderHelperAdvertisesItsSupportedPublicSubset(): void
    {
        $helper = file_get_contents(__DIR__.'/../Support/InteractsWithIntegratedAdminComponents.php');

        self::assertIsString($helper);

        foreach (AdminComponentContract::lightweightTestComponents() as $componentKey) {
            self::assertStringContainsString("'".$componentKey."'", $helper);
        }
    }
}
