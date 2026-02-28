<?php

declare(strict_types=1);

namespace Integrated\Bundle\TaxonomyBundle\Tests\Features;

use PHPUnit\Framework\TestCase;

final class TaxonomyRepositoryHardeningTest extends TestCase
{
    public function testPagedReadsDirectlyFromMongoForImmediateConsistency(): void
    {
        $repository = file_get_contents(__DIR__.'/../../Infrastructure/ODMTaxonomyRepository.php');

        $this->assertIsString($repository);
        $this->assertStringContainsString('->createQueryBuilder(Taxonomy::class)', $repository);
        $this->assertStringContainsString("->field('contentType')", $repository);
        $this->assertStringContainsString("->equals(\$contentType)", $repository);
        $this->assertStringContainsString("->sort('rank', 'asc')", $repository);
        $this->assertStringContainsString("->sort('title', 'asc')", $repository);
        $this->assertStringContainsString('->skip($offset)', $repository);
        $this->assertStringContainsString('->limit($limit)', $repository);
        $this->assertStringNotContainsString('->getPlugin(\'postbigrequest\')', $repository);
        $this->assertStringNotContainsString('->select($query)', $repository);
    }
}
