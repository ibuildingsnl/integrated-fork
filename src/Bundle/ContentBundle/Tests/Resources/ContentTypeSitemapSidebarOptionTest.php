<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class ContentTypeSitemapSidebarOptionTest extends TestCase
{
    public function testSidebarContainsSitemapOptionWithDefaultAndOverrides(): void
    {
        $formType = file_get_contents(__DIR__.'/../../Form/Type/ContentTypeFormType.php');

        $this->assertIsString($formType);
        $this->assertStringContainsString("'options_sitemap', ChoiceType::class", $formType);
        $this->assertStringContainsString("'property_path' => 'options[sitemap]'", $formType);
        $this->assertStringContainsString("'Use SitemapBundle default' => ''", $formType);
        $this->assertStringContainsString("'Include in sitemap' => 'enabled'", $formType);
        $this->assertStringContainsString("'Exclude from sitemap' => 'disabled'", $formType);
    }
}
