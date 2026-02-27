<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Controller;

use Integrated\Bundle\ContentBundle\Controller\ContentController;
use Integrated\Bundle\ContentBundle\Document\Content\Article;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
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
