<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Controller;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Integrated\Bundle\ContentBundle\Controller\ContentController;
use Integrated\Bundle\ContentBundle\Document\Content\Article;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Document\Content\Publication;
use PHPUnit\Framework\TestCase;

class ContentControllerSavePublicationGuardTest extends TestCase
{
    public function testGetPublicationsReturnsEmptyForUnsavedContentWithoutIdentifier(): void
    {
        $controller = (new \ReflectionClass(ContentController::class))->newInstanceWithoutConstructor();
        $content = new Article();

        $result = $this->invokeGetPublications($controller, $content);

        self::assertSame([], $result);
    }

    public function testGetPublicationsReturnsEmptyForInvalidNonStringIdentifier(): void
    {
        $controller = (new \ReflectionClass(ContentController::class))->newInstanceWithoutConstructor();
        $content = $this->createMock(Article::class);
        $content
            ->method('getId')
            ->willReturn(123);

        $result = $this->invokeGetPublications($controller, $content);

        self::assertSame([], $result);
    }

    public function testGetPublicationsQueriesRepositoryByContentIdentifier(): void
    {
        $controller = (new \ReflectionClass(ContentController::class))->newInstanceWithoutConstructor();

        $repository = $this->createMock(DocumentRepository::class);
        $repository
            ->expects(self::once())
            ->method('findBy')
            ->with(['content.$id' => 'existing-id'])
            ->willReturn([$this->createMock(Publication::class)]);

        $documentManager = $this->createMock(DocumentManager::class);
        $documentManager
            ->method('getRepository')
            ->willReturn($repository);

        $property = new \ReflectionProperty(ContentController::class, 'documentManager');
        $property->setAccessible(true);
        $property->setValue($controller, $documentManager);

        $content = new Article();
        $content->setId('existing-id');

        self::assertCount(1, $this->invokeGetPublications($controller, $content));
    }

    /**
     * @return array<int, mixed>
     */
    private function invokeGetPublications(ContentController $controller, Content $content): array
    {
        $method = new \ReflectionMethod(ContentController::class, 'getPublications');
        $method->setAccessible(true);

        /** @var array<int, mixed> $result */
        $result = $method->invoke($controller, $content);

        return $result;
    }
}
