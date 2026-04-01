<?php

declare(strict_types=1);

namespace Integrated\Bundle\UserBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class UserTemplateContractTest extends TestCase
{
    public function testNewTemplateDefinesPageTitle(): void
    {
        $path = \dirname(__DIR__, 2).'/Resources/views/user/new.html.twig';
        self::assertFileExists($path);

        $content = file_get_contents($path);
        self::assertIsString($content);
        self::assertStringContainsString('{% block title %}{% trans %}New user{% endtrans %}{% endblock title %}', $content);
    }

    public function testEditTemplateDefinesPageTitle(): void
    {
        $path = \dirname(__DIR__, 2).'/Resources/views/user/edit.html.twig';
        self::assertFileExists($path);

        $content = file_get_contents($path);
        self::assertIsString($content);
        self::assertStringContainsString('{% block title %}{% trans %}Edit user{% endtrans %}{% endblock title %}', $content);
    }
}
