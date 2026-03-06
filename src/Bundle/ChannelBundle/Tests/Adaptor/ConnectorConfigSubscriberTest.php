<?php

namespace Integrated\Bundle\ChannelBundle\Tests\Adaptor;

use Doctrine\ORM\EntityManagerInterface;
use Integrated\Bundle\ChannelBundle\Adaptor\ConnectorConfigSubscriber;
use Integrated\Bundle\ChannelBundle\Event\FormConfigEvent;
use Integrated\Bundle\ChannelBundle\Event\GetResponseConfigEvent;
use Integrated\Bundle\ChannelBundle\Model\Config;
use Integrated\Bundle\ChannelBundle\Model\OauthConfigInterface;
use Integrated\Bundle\ChannelBundle\Services\ChannelTokenService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ConnectorConfigSubscriberTest extends TestCase
{
    public function testOnSubmitDoesNotStoreExternalReturnIdWhenNoAuthUrlIsGenerated(): void
    {
        $oauth = $this->createMock(OauthConfigInterface::class);
        $oauth->method('getName')->willReturn('oauth-test');
        $oauth->method('prepareAuthLink')->willReturn(null);

        $subscriber = new ConnectorConfigSubscriber(
            $oauth,
            $this->createMock(UrlGeneratorInterface::class),
            $this->createMock(EntityManagerInterface::class),
            $this->mockChannelTokenServiceReturningNull(),
        );

        $request = new Request();
        $session = new Session(new MockArraySessionStorage());
        $request->setSession($session);

        $config = (new Config(42))
            ->setAdapter('oauth-test')
            ->setChannels(['channel-1']);
        $event = new FormConfigEvent($config, $request, $this->createMock(FormInterface::class));

        $subscriber->onSubmit($event);

        self::assertFalse($session->has('externalReturnId'));
        self::assertNull($event->getResponse());
    }

    public function testOnSubmitSkipsWhenNoChannelsAreConfigured(): void
    {
        $oauth = $this->createMock(OauthConfigInterface::class);
        $oauth->method('getName')->willReturn('oauth-test');
        $oauth->expects(self::never())->method('prepareAuthLink');

        $channelTokenService = $this->createMock(ChannelTokenService::class);
        $channelTokenService->expects(self::never())->method('getChannelTokenFor');

        $subscriber = new ConnectorConfigSubscriber(
            $oauth,
            $this->createMock(UrlGeneratorInterface::class),
            $this->createMock(EntityManagerInterface::class),
            $channelTokenService,
        );

        $request = new Request();
        $session = new Session(new MockArraySessionStorage());
        $request->setSession($session);

        $config = (new Config(42))
            ->setAdapter('oauth-test')
            ->setChannels([]);
        $event = new FormConfigEvent($config, $request, $this->createMock(FormInterface::class));

        $subscriber->onSubmit($event);

        self::assertFalse($session->has('externalReturnId'));
        self::assertNull($event->getResponse());
    }

    public function testOnRequestClearsExternalReturnIdAfterSuccessfulCallback(): void
    {
        $oauth = $this->createMock(OauthConfigInterface::class);
        $oauth->method('getName')->willReturn('oauth-test');
        $oauth->method('handleCallback')->willReturn(true);

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')->willReturn('/admin/channel/config/42');

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('flush');

        $subscriber = new ConnectorConfigSubscriber(
            $oauth,
            $urlGenerator,
            $entityManager,
            $this->mockChannelTokenServiceReturningNull(),
        );

        $request = new Request();
        $session = new Session(new MockArraySessionStorage());
        $session->set('externalReturnId', 42);
        $request->setSession($session);

        $config = (new Config(42))->setAdapter('oauth-test');
        $event = new GetResponseConfigEvent($config, $request);

        $subscriber->onRequest($event);

        $response = $event->getResponse();
        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame('/admin/channel/config/42', $response->getTargetUrl());
        self::assertFalse($session->has('externalReturnId'));
    }

    private function mockChannelTokenServiceReturningNull(): ChannelTokenService
    {
        $channelTokenService = $this->createMock(ChannelTokenService::class);
        $channelTokenService->method('getChannelTokenFor')->willReturn(null);

        return $channelTokenService;
    }
}
