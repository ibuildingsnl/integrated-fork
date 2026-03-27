<?php

declare(strict_types=1);

namespace Integrated\Bundle\PageBundle\Tests\Form\Type;

use PHPUnit\Framework\TestCase;

final class PageTypeSeoContractTest extends TestCase
{
    public function testPageTypeExposesSeoFields(): void
    {
        $source = file_get_contents(__DIR__.'/../../../Form/Type/PageType.php');

        $this->assertIsString($source);
        $this->assertStringContainsString("\$builder->add('seoMetadata'", $source);
        $this->assertStringContainsString("SeoMetaType::class", $source);
        $this->assertStringContainsString("\$builder->add('canonicalUrl'", $source);
        $this->assertStringContainsString("\$builder->add('paginationNoindexEnabled'", $source);
        $this->assertStringContainsString("\$builder->add('robotsDirective'", $source);
        $this->assertStringContainsString("\$builder->add('featuredImage'", $source);
        $this->assertStringContainsString("\$builder->add('twitterCard'", $source);
        $this->assertStringContainsString("'meta_title_fallback' => \$page ? (string) \$page->getTitle() : null", $source);
        $this->assertStringContainsString("'meta_description_fallback' => \$page ? (string) \$page->getDescription() : null", $source);
        $this->assertStringContainsString("? 'default'", $source);
        $this->assertStringContainsString("\$page->getRobotsDirective()", $source);
        $this->assertStringContainsString("\$page->getTwitterCard()", $source);
    }
}
