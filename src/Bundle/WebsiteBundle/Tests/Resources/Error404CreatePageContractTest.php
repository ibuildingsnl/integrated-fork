<?php

declare(strict_types=1);

namespace Integrated\Bundle\WebsiteBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

class Error404CreatePageContractTest extends TestCase
{
    public function test404TemplateOnlyLoadsCreatePagePanelInWebsiteEditMode(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/themes/default/error/404.html.twig');

        self::assertIsString($template);
        self::assertStringContainsString("app.request.attributes.get('integrated_block_edit')", $template);
        self::assertStringContainsString("'integrated_website_edit': app.request.query.get('integrated_website_edit')", $template);
        self::assertStringContainsString('if (!response.ok)', $template);
        self::assertStringContainsString('.catch(function()', $template);
    }
}
