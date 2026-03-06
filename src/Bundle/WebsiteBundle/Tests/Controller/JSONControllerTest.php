<?php

declare(strict_types=1);

namespace Integrated\Bundle\WebsiteBundle\Tests\Controller;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\ContentBundle\Document\Block\ContentBlock;
use Integrated\Bundle\ContentBundle\Document\Content\ContentRepository;
use Integrated\Bundle\ContentBundle\Document\SearchSelection\SearchSelection;
use Integrated\Bundle\ContentBundle\Provider\SolariumProvider;
use Integrated\Bundle\ThemeBundle\Templating\ThemeManager;
use Integrated\Bundle\WebsiteBundle\Controller\JSONController;
use Knp\Component\Pager\PaginatorInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

final class JSONControllerTest extends TestCase
{
    public function testSearchSelectionJsonComputesMaxPagesWithoutExplicitLimit(): void
    {
        $documents = new FakePaginationResult(23, 10);

        $solariumProvider = $this->createMock(SolariumProvider::class);
        $solariumProvider
            ->expects(self::once())
            ->method('execute')
            ->with(self::callback(static fn (mixed $block): bool => $block instanceof ContentBlock && $block->getItemsPerPage() === 10), self::isInstanceOf(Request::class))
            ->willReturn($documents);

        $themeManager = $this->createMock(ThemeManager::class);
        $themeManager
            ->expects(self::once())
            ->method('locateTemplate')
            ->with('json/index.json.twig')
            ->willReturn('json/index.json.twig');

        $controller = $this->createController($solariumProvider, $themeManager);

        $request = new Request();
        $request->setRequestFormat('json');

        $response = $controller->searchSelectionJson($request, new SearchSelection());

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        self::assertSame(3, $controller->lastParameters['maxPages']);
        self::assertSame(23, $controller->lastParameters['totalCount']);
        self::assertSame($documents, $controller->lastParameters['documents']);
    }

    public function testSearchSelectionJsonClampsLargeLimitTo500(): void
    {
        $documents = new FakePaginationResult(1200, 500);

        $solariumProvider = $this->createMock(SolariumProvider::class);
        $solariumProvider
            ->expects(self::once())
            ->method('execute')
            ->with(
                self::callback(static fn (mixed $block): bool => $block instanceof ContentBlock && $block->getItemsPerPage() === 500),
                self::isInstanceOf(Request::class)
            )
            ->willReturn($documents);

        $themeManager = $this->createMock(ThemeManager::class);
        $themeManager
            ->expects(self::once())
            ->method('locateTemplate')
            ->with('json/index.json.twig')
            ->willReturn('json/index.json.twig');

        $controller = $this->createController($solariumProvider, $themeManager);

        $request = new Request(['limit' => 900]);
        $request->setRequestFormat('json');

        $response = $controller->searchSelectionJson($request, new SearchSelection());

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        self::assertSame(3, $controller->lastParameters['maxPages']);
    }

    private function createController(SolariumProvider $solariumProvider, ThemeManager $themeManager): TestableJSONController
    {
        return new TestableJSONController(
            $solariumProvider,
            $this->createMock(PaginatorInterface::class),
            $this->createMock(RequestStack::class),
            $this->createMock(DocumentManager::class),
            $themeManager,
            $this->createMock(ContentRepository::class)
        );
    }
}

final class TestableJSONController extends JSONController
{
    public array $lastParameters = [];

    public function render(string $view, array $parameters = [], Response $response = null): Response
    {
        $this->lastParameters = $parameters;

        return $response ?? new Response('', Response::HTTP_OK);
    }
}

final class FakePaginationResult
{
    public function __construct(
        private int $totalItemCount,
        private int $itemNumberPerPage
    ) {
    }

    public function getTotalItemCount(): int
    {
        return $this->totalItemCount;
    }

    public function getItemNumberPerPage(): int
    {
        return $this->itemNumberPerPage;
    }
}
