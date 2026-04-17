<?php

declare(strict_types=1);

namespace Integrated\Bundle\WebsiteBundle\Tests\Resources;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ContentControllerEntityMappingContractTest extends TestCase
{
    #[DataProvider('controllerFilesProvider')]
    public function testContentControllersResolveDocumentsViaRequestSlug(string $relativePath, string $documentArgument): void
    {
        $controller = file_get_contents(__DIR__.'/../../Controller/Content/'.$relativePath);

        self::assertIsString($controller);
        self::assertStringContainsString('ContentTypePage $page, Request $request', $controller);
        self::assertStringContainsString('ContentDocumentResolver', $controller);
        self::assertStringContainsString('$'.$documentArgument.' = $this->contentDocumentResolver->resolve($request,', $controller);
    }

    /**
     * @return iterable<string, array{0: string, 1: string}>
     */
    public static function controllerFilesProvider(): iterable
    {
        yield 'article' => ['ArticleController.php', 'article'];
        yield 'company' => ['CompanyController.php', 'company'];
        yield 'event' => ['EventController.php', 'event'];
        yield 'job posting' => ['JobPostingController.php', 'jobPosting'];
        yield 'news' => ['NewsController.php', 'news'];
        yield 'person' => ['PersonController.php', 'person'];
        yield 'taxonomy' => ['TaxonomyController.php', 'taxonomy'];
    }
}
