<?php

declare(strict_types=1);

namespace Integrated\Bundle\BrandBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class ConnectorManageTemplateTest extends TestCase
{
    public function testConnectorManageTemplateRendersMissingThemeBlocksReport(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/brand/config_manage.html.twig');

        self::assertIsString($template);
        self::assertStringContainsString('{% if missingConnectorBlocks is not empty %}', $template);
        self::assertStringContainsString('{% trans %}Missing theme blocks{% endtrans %}', $template);
        self::assertStringContainsString('{% for missingBlock in missingConnectorBlocks %}', $template);
        self::assertStringContainsString('{{ missingBlock.id }}', $template);
        self::assertStringContainsString('{% for usage in missingBlock.usages %}', $template);
        self::assertStringContainsString('{% if missingBlock.duplicateCandidates is defined and missingBlock.duplicateCandidates is not empty %}', $template);
        self::assertStringContainsString("path('integrated_block_block_duplicate'", $template);
        self::assertStringContainsString('{% trans with {\'%id%\': candidate.id} %}Duplicate from %id%{% endtrans %}', $template);
    }
}
