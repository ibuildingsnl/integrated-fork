<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Controller;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\ContentBundle\Controller\ContentTypeController;
use Integrated\Bundle\ContentBundle\Doctrine\ContentTypeManager;
use Integrated\Common\Form\Mapping\MetadataFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ContentTypeControllerTest extends TestCase
{
    public function testNewRedirectsToSelectWhenClassQueryIsMissing(): void
    {
        $metadataFactory = $this->getMockBuilder(MetadataFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getMetadata'])
            ->getMock();
        $metadataFactory
            ->expects(self::never())
            ->method('getMetadata');

        $controller = new TestableContentTypeController(
            $this->createStub(ContentTypeManager::class),
            $this->createStub(EventDispatcherInterface::class),
            $metadataFactory,
            $this->createStub(DocumentManager::class)
        );

        $response = $controller->new(new Request());

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame('integrated_content_content_type_select', $response->getTargetUrl());
    }
}

final class TestableContentTypeController extends ContentTypeController
{
    protected function isGranted(mixed $attribute, mixed $subject = null): bool
    {
        return true;
    }

    protected function redirectToRoute(string $route, array $parameters = [], int $status = 302): RedirectResponse
    {
        return new RedirectResponse($route, $status);
    }

    protected function render(string $view, array $parameters = [], ?Response $response = null): Response
    {
        return $response ?? new Response('', Response::HTTP_OK);
    }
}
