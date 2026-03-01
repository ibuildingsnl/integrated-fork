<?php

declare(strict_types=1);

namespace Integrated\Bundle\WebsiteBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class PageBuilderDefaultTemplatesContractTest extends TestCase
{
    public function testContainerTemplateExposesGridEditorHooks(): void
    {
        $path = dirname(__DIR__, 2) . '/Resources/views/themes/default/pagebuilder/components/container.html.twig';
        $content = file_get_contents($path);

        self::assertIsString($content);
        self::assertStringContainsString("app.request.attributes.get('integrated_block_edit')", $content);
        self::assertStringContainsString('integrated-website-grid integrated-website-droppable', $content);
        self::assertStringContainsString('data-block-type="row"', $content);
        self::assertStringContainsString('integrated-website-col integrated-website-droppable', $content);
        self::assertStringContainsString('data-block-type="column"', $content);
    }

    public function testBlockRefTemplateExposesBlockEditorHooks(): void
    {
        $path = dirname(__DIR__, 2) . '/Resources/views/themes/default/pagebuilder/components/block_ref.html.twig';
        $content = file_get_contents($path);

        self::assertIsString($content);
        self::assertStringContainsString('integrated-website-sortable', $content);
        self::assertStringContainsString('data-block-type="block"', $content);
        self::assertStringContainsString('data-id="{{ blockId }}"', $content);
    }
}

