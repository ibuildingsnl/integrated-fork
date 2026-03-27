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
        $this->assertStringContainsString("\$builder->add('seoTitle'", $source);
        $this->assertStringContainsString("\$builder->add('seoDescription'", $source);
        $this->assertStringContainsString("\$builder->add('canonicalUrl'", $source);
        $this->assertStringContainsString("\$builder->add('paginationNoindexEnabled'", $source);
    }
}
