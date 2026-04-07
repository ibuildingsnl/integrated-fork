<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Controller;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\Persistence\ObjectRepository;
use Integrated\Bundle\ContentBundle\Controller\ContentController;
use Integrated\Bundle\ContentBundle\Doctrine\ContentTypeManager;
use Integrated\Bundle\TaxonomyBundle\Services\TaxonomyOptions;
use Integrated\Bundle\TaxonomyBundle\Services\TaxonomyOverview;
use Integrated\Common\ContentType\ContentTypeInterface;
use PHPUnit\Framework\TestCase;

final class ContentControllerTaxonomyCategoriesTest extends TestCase
{
    public function testGetTaxonomyCategoriesUsesScopedRelationLookupAndCachesTargetOverview(): void
    {
        $controller = (new \ReflectionClass(ContentController::class))->newInstanceWithoutConstructor();

        $sourceType = $this->createMock(ContentTypeInterface::class);
        $sourceType->method('getId')->willReturn('news');

        $targetType = $this->createMock(ContentTypeInterface::class);
        $targetType->method('getId')->willReturn('dossier');

        $content = new class() {
            public function getContentType(): string
            {
                return 'news';
            }
        };

        $relationOne = $this->getMockBuilder(\Integrated\Bundle\ContentBundle\Document\Relation\Relation::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getId', 'getTargets'])
            ->getMock();
        $relationOne->method('getId')->willReturn('relation-one');
        $relationOne->method('getTargets')->willReturn([$targetType]);

        $relationTwo = $this->getMockBuilder(\Integrated\Bundle\ContentBundle\Document\Relation\Relation::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getId', 'getTargets'])
            ->getMock();
        $relationTwo->method('getId')->willReturn('relation-two');
        $relationTwo->method('getTargets')->willReturn([$targetType]);

        $repository = $this->createMock(ObjectRepository::class);
        $repository
            ->expects(self::once())
            ->method('findBy')
            ->with([
                'sources.$id' => 'news',
                'type' => 'taxonomy_category',
            ])
            ->willReturn([$relationOne, $relationTwo]);

        $contentTypeManager = $this->createMock(ContentTypeManager::class);
        $contentTypeManager
            ->expects(self::once())
            ->method('getType')
            ->with('news')
            ->willReturn($sourceType);

        $documentManager = $this->createMock(DocumentManager::class);
        $documentManager
            ->expects(self::once())
            ->method('getRepository')
            ->with('Integrated\\Bundle\\ContentBundle\\Document\\Relation\\Relation')
            ->willReturn($repository);

        $taxonomyOverview = $this->createMock(TaxonomyOverview::class);
        $taxonomyOverview
            ->expects(self::once())
            ->method('overviewFor')
            ->with(
                'dossier',
                self::callback(static function (?TaxonomyOptions $options): bool {
                    return $options instanceof TaxonomyOptions && false === $options->includeUsageCounts;
                })
            )
            ->willReturn([['taxonomyId' => 'foo']]);

        $this->setProperty($controller, 'contentTypeManager', $contentTypeManager);
        $this->setProperty($controller, 'documentManager', $documentManager);
        $this->setProperty($controller, 'taxonomyIndexer', $taxonomyOverview);

        $method = new \ReflectionMethod(ContentController::class, 'getTaxonomyCategories');
        $method->setAccessible(true);

        $result = $method->invoke($controller, $content);

        self::assertSame([['taxonomyId' => 'foo']], $result['relation-one']);
        self::assertSame([['taxonomyId' => 'foo']], $result['relation-two']);
    }

    private function setProperty(object $object, string $property, mixed $value): void
    {
        $reflectionProperty = new \ReflectionProperty(ContentController::class, $property);
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($object, $value);
    }
}
