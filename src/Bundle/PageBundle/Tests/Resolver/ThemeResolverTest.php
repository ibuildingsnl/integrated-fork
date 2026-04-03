<?php

declare(strict_types=1);

namespace Integrated\Bundle\PageBundle\Tests\Resolver;

use Integrated\Bundle\PageBundle\Resolver\ThemeResolver;
use Integrated\Bundle\ThemeBundle\Templating\ThemeManager;
use Integrated\Common\Channel\Connector\Config\ResolverInterface;
use Integrated\Common\Content\Channel\ChannelInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class ThemeResolverTest extends TestCase
{
    public function testGetThemeCachesResolvedThemeForChannel(): void
    {
        $cache = new ArrayAdapter();
        $channel = $this->createChannel('bakkersinbedrijf');
        $themeManager = $this->createThemeManager(['twindigital']);

        $resolver = $this->createMock(ResolverInterface::class);
        $resolver
            ->expects($this->once())
            ->method('getConfigs')
            ->with($channel)
            ->willReturn([
                $this->createConfig('website', 'twindigital'),
            ]);

        $themeResolver = new ThemeResolver($resolver, $themeManager, $cache);

        self::assertSame('twindigital', $themeResolver->getTheme($channel));
        self::assertSame('twindigital', $themeResolver->getTheme($channel));
    }

    public function testGetThemeCachesDefaultWhenNoValidWebsiteThemeExists(): void
    {
        $cache = new ArrayAdapter();
        $channel = $this->createChannel('bakkersinbedrijf');
        $themeManager = $this->createThemeManager(['twindigital']);

        $resolver = $this->createMock(ResolverInterface::class);
        $resolver
            ->expects($this->once())
            ->method('getConfigs')
            ->with($channel)
            ->willReturn([
                $this->createConfig('website', 'unknown-theme'),
            ]);

        $themeResolver = new ThemeResolver($resolver, $themeManager, $cache);

        self::assertSame('default', $themeResolver->getTheme($channel));
        self::assertSame('default', $themeResolver->getTheme($channel));
    }

    public function testGetThemeUsesPersistentCacheAcrossResolverInstances(): void
    {
        $cache = new ArrayAdapter();
        $channel = $this->createChannel('bakkersinbedrijf');
        $themeManager = $this->createThemeManager(['twindigital']);

        $warmupResolver = $this->createMock(ResolverInterface::class);
        $warmupResolver
            ->expects($this->once())
            ->method('getConfigs')
            ->with($channel)
            ->willReturn([
                $this->createConfig('website', 'twindigital'),
            ]);

        $warmupThemeResolver = new ThemeResolver($warmupResolver, $themeManager, $cache);
        self::assertSame('twindigital', $warmupThemeResolver->getTheme($channel));

        $cachedResolver = $this->createMock(ResolverInterface::class);
        $cachedResolver
            ->expects($this->never())
            ->method('getConfigs');

        $cachedThemeResolver = new ThemeResolver($cachedResolver, $themeManager, $cache);
        self::assertSame('twindigital', $cachedThemeResolver->getTheme($channel));
    }

    public function testInvalidateForChannelIdForcesFreshResolution(): void
    {
        $cache = new ArrayAdapter();
        $channel = $this->createChannel('bakkersinbedrijf');
        $themeManager = $this->createThemeManager(['twindigital']);

        $resolver = $this->createMock(ResolverInterface::class);
        $resolver
            ->expects($this->exactly(2))
            ->method('getConfigs')
            ->with($channel)
            ->willReturn([
                $this->createConfig('website', 'twindigital'),
            ]);

        $themeResolver = new ThemeResolver($resolver, $themeManager, $cache);

        self::assertSame('twindigital', $themeResolver->getTheme($channel));
        $themeResolver->invalidateForChannelId('bakkersinbedrijf');
        self::assertSame('twindigital', $themeResolver->getTheme($channel));
    }

    private function createChannel(string $id): ChannelInterface
    {
        $channel = $this->createMock(ChannelInterface::class);
        $channel->method('getId')->willReturn($id);

        return $channel;
    }

    /**
     * @param array<int, string> $themes
     */
    private function createThemeManager(array $themes): ThemeManager
    {
        $manager = $this->createMock(ThemeManager::class);
        $manager
            ->method('hasTheme')
            ->willReturnCallback(static fn (?string $theme): bool => \in_array((string) $theme, $themes, true));

        return $manager;
    }

    private function createConfig(string $adapter, string $theme): object
    {
        return new class($adapter, $theme) {
            private string $adapter;
            private object $options;

            public function __construct(string $adapter, string $theme)
            {
                $this->adapter = $adapter;
                $this->options = new class($theme) {
                    private string $theme;

                    public function __construct(string $theme)
                    {
                        $this->theme = $theme;
                    }

                    public function get(string $key): ?string
                    {
                        if ($key !== 'theme') {
                            return null;
                        }

                        return $this->theme;
                    }
                };
            }

            public function getAdapter(): string
            {
                return $this->adapter;
            }

            public function getOptions(): object
            {
                return $this->options;
            }
        };
    }
}
