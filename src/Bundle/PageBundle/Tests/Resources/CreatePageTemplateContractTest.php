<?php

declare(strict_types=1);

namespace Integrated\Bundle\PageBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class CreatePageTemplateContractTest extends TestCase
{
    public function testWebsiteCreatePageTemplateUsesCardLayoutInsteadOfTableFormTheme(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/website/create_page.html.twig');

        self::assertIsString($template);
        self::assertStringContainsString('@IntegratedPage/form/form_div_layout.html.twig', $template);
        self::assertStringNotContainsString('form_table_layout.html.twig', $template);
        self::assertStringContainsString('integrated-page-website-create-page__actions', $template);
        self::assertStringContainsString('{{ form_start(form', $template);
    }
}
