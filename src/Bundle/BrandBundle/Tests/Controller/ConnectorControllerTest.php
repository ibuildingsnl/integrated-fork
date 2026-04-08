<?php

namespace Integrated\Bundle\BrandBundle\Tests\Controller;

use Integrated\Bundle\BlockBundle\Document\Block\BlockRepository;
use Integrated\Bundle\BrandBundle\Controller\ConnectorController;
use Integrated\Bundle\BrandBundle\Document\Brand;
use Integrated\Bundle\BrandBundle\Document\ChannelLink;
use Integrated\Bundle\BrandBundle\Provider\ConnectorMissingThemeBlocksProvider;
use Integrated\Bundle\ContentBundle\Document\Channel\ChannelType;
use Integrated\Common\Channel\Connector\Adapter\RegistryInterface;
use Integrated\Common\Channel\Connector\Config\ConfigManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ConnectorControllerTest extends TestCase
{
    public function testExtractChannelPatternDetectsPrefixAndSuffixAroundChannel(): void
    {
        $controller = $this->createController();
        $method = new \ReflectionMethod(ConnectorController::class, 'extractChannelPattern');
        $method->setAccessible(true);

        self::assertSame(
            ['prefix' => 'premium_', 'suffix' => '_noaccess'],
            $method->invoke($controller, 'premium_testmerk_website_noaccess', 'testmerk_website')
        );
        self::assertSame(
            ['prefix' => 'headscripts_', 'suffix' => ''],
            $method->invoke($controller, 'headscripts_testmerk_website', 'testmerk_website')
        );
        self::assertNull($method->invoke($controller, 'premium_noaccess', 'testmerk_website'));
    }

    public function testBuildCandidateQueryPatternSupportsPrefixAndSuffixMatching(): void
    {
        $controller = $this->createController();
        $method = new \ReflectionMethod(ConnectorController::class, 'buildCandidateQueryPattern');
        $method->setAccessible(true);

        self::assertSame('^premium_(.+)_noaccess$', $method->invoke($controller, 'premium_', '_noaccess'));
        self::assertSame('^leaderboard_', $method->invoke($controller, 'leaderboard_', ''));
        self::assertSame('_noaccess$', $method->invoke($controller, '', '_noaccess'));
        self::assertNull($method->invoke($controller, '', ''));
    }

    public function testConfigureThrowsNotFoundIfLinkDoesNotBelongToBrand(): void
    {
        $configs = $this->createMock(ConfigManagerInterface::class);
        $configs->expects(self::never())->method('findByChannel');

        $controller = $this->createController($configs);

        $brand = new Brand();
        $brand->addChannelLink(new ChannelLink(new ChannelType('website', 'Website'), null, false));
        $unknownLink = new ChannelLink(new ChannelType('newsletter', 'Newsletter'), null, false);

        $this->expectException(NotFoundHttpException::class);
        $controller->configure(Request::create('/'), $brand, $unknownLink);
    }

    private function createController(?ConfigManagerInterface $configs = null): TestableConnectorController
    {
        return new TestableConnectorController(
            $configs ?? $this->createMock(ConfigManagerInterface::class),
            $this->createMock(RegistryInterface::class),
            $this->createMock(EventDispatcherInterface::class),
            $this->createMock(ConnectorMissingThemeBlocksProvider::class),
            $this->createMock(BlockRepository::class),
        );
    }
}

class TestableConnectorController extends ConnectorController
{
    protected function isGranted(mixed $attribute, mixed $subject = null): bool
    {
        return true;
    }
}
