<?php

declare(strict_types=1);

namespace Integrated\Bundle\BlockBundle\Tests\Provider;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\Persistence\ObjectRepository;
use Integrated\Bundle\BlockBundle\Provider\TemplateBlockUsageProvider;
use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Integrated\Bundle\PageBundle\Resolver\ThemeResolver;
use Integrated\Bundle\ThemeBundle\Templating\ThemeManager;
use PHPUnit\Framework\TestCase;
use Twig\Loader\FilesystemLoader;

final class TemplateBlockUsageProviderTest extends TestCase
{
    private string $themeRoot;

    protected function setUp(): void
    {
        $this->themeRoot = sys_get_temp_dir().'/integrated-block-template-usage-'.bin2hex(random_bytes(6));
        mkdir($this->themeRoot, 0777, true);
        mkdir($this->themeRoot.'/site', 0777, true);
        mkdir($this->themeRoot.'/default', 0777, true);
        mkdir($this->themeRoot.'/unused', 0777, true);

        file_put_contents(
            $this->themeRoot.'/site/base.html.twig',
            "{{ integrated_block('leaderboard_' ~ _channel.id) }}\n{{ integrated_block('premium_%s_noaccess'|format(app.request.get('_channel'))) }}\n{{ integrated_block('static_help_block'|format(app.request.get('_channel'))) }}"
        );
        file_put_contents(
            $this->themeRoot.'/default/footer.html.twig',
            "{{ integrated_channel_block('premium_noaccess', 'Premium', 'Class') }}\n{{ integrated_block('alea_publishers_footer_' ~ _channel.language|default('nl')) }}"
        );
        file_put_contents(
            $this->themeRoot.'/unused/ignore.html.twig',
            "{{ integrated_block('unused_static_block') }}"
        );
    }

    protected function tearDown(): void
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->themeRoot, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $file) {
            if ($file->isDir()) {
                rmdir($file->getPathname());
            } else {
                unlink($file->getPathname());
            }
        }

        @rmdir($this->themeRoot);
    }

    public function testGetUsageMapsScansOnlyConfiguredThemeChainAndExpandsChannelPatterns(): void
    {
        $channel = new Channel();
        $channel->setId('bakkersinbedrijf');
        $channel->setName('Bakkers in Bedrijf');
        $channel->setLanguage('nl');

        $repository = $this->createMock(ObjectRepository::class);
        $repository
            ->expects(self::once())
            ->method('findAll')
            ->willReturn([$channel]);

        $documentManager = $this->createMock(DocumentManager::class);
        $documentManager
            ->expects(self::once())
            ->method('getRepository')
            ->with(Channel::class)
            ->willReturn($repository);

        $themeResolver = $this->createMock(ThemeResolver::class);
        $themeResolver
            ->expects(self::once())
            ->method('getTheme')
            ->with($channel)
            ->willReturn('site');

        $themeManager = new ThemeManager(new FilesystemLoader(), $this->themeRoot);
        $themeManager->registerTheme('default', [$this->themeRoot.'/default']);
        $themeManager->registerTheme('site', [$this->themeRoot.'/site'], ['default']);
        $themeManager->registerTheme('unused', [$this->themeRoot.'/unused']);

        $provider = new TemplateBlockUsageProvider(
            $documentManager,
            $themeManager,
            $themeResolver,
            $this->themeRoot
        );

        $usageMaps = $provider->getUsageMaps();

        self::assertArrayHasKey('leaderboard_bakkersinbedrijf', $usageMaps['blockTemplates']);
        self::assertArrayHasKey('premium_bakkersinbedrijf_noaccess', $usageMaps['blockTemplates']);
        self::assertArrayHasKey('static_help_block', $usageMaps['blockTemplates']);
        self::assertArrayHasKey('premium_noaccess_bakkersinbedrijf', $usageMaps['blockTemplates']);
        self::assertArrayHasKey('alea_publishers_footer_nl', $usageMaps['blockTemplates']);
        self::assertArrayNotHasKey('unused_static_block', $usageMaps['blockTemplates']);
        self::assertEquals(
            ['leaderboard_bakkersinbedrijf' => 'leaderboard_bakkersinbedrijf', 'premium_bakkersinbedrijf_noaccess' => 'premium_bakkersinbedrijf_noaccess', 'static_help_block' => 'static_help_block', 'premium_noaccess_bakkersinbedrijf' => 'premium_noaccess_bakkersinbedrijf', 'alea_publishers_footer_nl' => 'alea_publishers_footer_nl'],
            $usageMaps['channelBlocks']['bakkersinbedrijf']
        );

        $premiumUsage = array_values($usageMaps['blockTemplates']['premium_bakkersinbedrijf_noaccess']);
        self::assertSame('bakkersinbedrijf', $premiumUsage[0]['channel_id']);
    }
}
