<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Controller;

use Integrated\Bundle\ContentBundle\Controller\ContentController;
use Integrated\Bundle\ContentBundle\Doctrine\ContentTypeManager;
use Integrated\Bundle\ContentBundle\Document\Content\Article;
use Integrated\Bundle\ContentBundle\Document\Content\File;
use Integrated\Bundle\ContentBundle\Document\Content\Image;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class ContentControllerNavigatorMediaFilterTest extends TestCase
{
    public function testHtmlNavigatorDefaultsToNonMediaContentTypes(): void
    {
        $controller = $this->createControllerWithContentTypes([
            $this->createContentType('article', Article::class),
            $this->createContentType('image', Image::class),
            $this->createContentType('file', File::class),
        ]);

        $result = $this->invokeFilter($controller, Request::create('/admin/content'), []);

        self::assertArrayNotHasKey('contenttypes', $result);
        self::assertSame(['image', 'file'], $result['exclude_contenttypes']);
    }

    public function testHtmlNavigatorPreservesExplicitContentTypeFilterSelections(): void
    {
        $controller = $this->createControllerWithContentTypes([
            $this->createContentType('article', Article::class),
            $this->createContentType('image', Image::class),
            $this->createContentType('file', File::class),
        ]);

        $result = $this->invokeFilter($controller, Request::create('/admin/content'), [
            'contenttypes' => ['image', 'article', 'file'],
        ]);

        self::assertSame(['image', 'article', 'file'], $result['contenttypes']);
        self::assertSame(['image', 'file'], $result['exclude_contenttypes']);
    }

    public function testJsonRequestsKeepRequestedMediaTypesForMediaSelectors(): void
    {
        $controller = $this->createControllerWithContentTypes([
            $this->createContentType('article', Article::class),
            $this->createContentType('image', Image::class),
        ]);

        $request = Request::create('/admin/content/json');
        $request->setRequestFormat('json');

        $result = $this->invokeFilter($controller, $request, [
            'contenttypes' => ['image'],
        ]);

        self::assertSame(['image'], $result['contenttypes']);
        self::assertArrayNotHasKey('exclude_contenttypes', $result);
    }

    /**
     * @param array<int, object> $contentTypes
     */
    private function createControllerWithContentTypes(array $contentTypes): ContentController
    {
        $controller = (new \ReflectionClass(ContentController::class))->newInstanceWithoutConstructor();

        $manager = $this->createMock(ContentTypeManager::class);
        $manager
            ->method('getAll')
            ->willReturn($contentTypes);

        $property = new \ReflectionProperty(ContentController::class, 'contentTypeManager');
        $property->setAccessible(true);
        $property->setValue($controller, $manager);

        return $controller;
    }

    private function createContentType(string $id, string $className): object
    {
        return new class($id, $className) {
            public function __construct(
                private readonly string $id,
                private readonly string $className,
            ) {
            }

            public function getId(): string
            {
                return $this->id;
            }

            public function getClass(): string
            {
                return $this->className;
            }
        };
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return array<string, mixed>
     */
    private function invokeFilter(ContentController $controller, Request $request, array $options): array
    {
        $method = new \ReflectionMethod(ContentController::class, 'applyNavigatorContentTypeFilter');
        $method->setAccessible(true);

        /** @var array<string, mixed> $result */
        $result = $method->invoke($controller, $request, $options);

        return $result;
    }
}
