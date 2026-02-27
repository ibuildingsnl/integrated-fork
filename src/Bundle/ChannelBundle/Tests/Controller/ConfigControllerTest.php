<?php

namespace Integrated\Bundle\ChannelBundle\Tests\Controller;

use Integrated\Bundle\ChannelBundle\Controller\ConfigController;
use Integrated\Common\Channel\Connector\Adapter\RegistryInterface;
use Integrated\Common\Channel\Connector\Config\ConfigManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class ConfigControllerTest extends TestCase
{
    public function testExternalReturnUsesRequestSessionAndClearsExternalReturnId(): void
    {
        $controller = new TestableConfigController(
            $this->createMock(ConfigManagerInterface::class),
            $this->createMock(RegistryInterface::class),
            $this->createMock(PaginatorInterface::class),
            $this->createMock(EventDispatcherInterface::class),
        );

        $request = new Request();
        $session = new Session(new MockArraySessionStorage());
        $session->set('externalReturnId', '42');
        $request->setSession($session);

        $response = $controller->externalReturn($request);

        self::assertSame('edit:42', $response->getContent());
        self::assertFalse($session->has('externalReturnId'));
    }
}

class TestableConfigController extends ConfigController
{
    public function index(Request $request): Response
    {
        return new Response('index');
    }

    public function edit(Request $request, string $id): Response
    {
        return new Response('edit:'.$id);
    }
}
