<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Resources;

use Integrated\Bundle\ContentBundle\Tests\Support\AdminComponentContract;
use PHPUnit\Framework\TestCase;

final class AdminComponentRegistrationTest extends TestCase
{
    public function testTwigConfigurationExplicitlyRegistersAdminComponents(): void
    {
        $config = file_get_contents(__DIR__.'/../../Resources/config/twig.xml');

        self::assertIsString($config);

        foreach (AdminComponentContract::componentTemplates() as $key => $template) {
            self::assertStringContainsString('key="'.$key.'"', $config);
            self::assertStringContainsString('template="'.$template.'"', $config);
        }
    }

    public function testExpectedComponentMapStaysInSyncWithAdminComponentClassFiles(): void
    {
        $componentClasses = glob(__DIR__.'/../../Twig/Component/Admin/*.php');

        self::assertIsArray($componentClasses);
        self::assertCount(\count(AdminComponentContract::publicComponents()), $componentClasses);
    }

    public function testExpectedComponentMapStaysInSyncWithAdminComponentTemplates(): void
    {
        $componentTemplates = glob(__DIR__.'/../../Resources/views/components/admin/*.html.twig');

        self::assertIsArray($componentTemplates);
        self::assertCount(\count(AdminComponentContract::publicComponents()), $componentTemplates);
    }
}
