<?php

declare(strict_types=1);

namespace Integrated\Bundle\BrandBundle\Tests\Provider;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\Persistence\ObjectRepository;
use Integrated\Bundle\BlockBundle\Document\Block\Block;
use Integrated\Bundle\BlockBundle\Provider\BlockUsageProvider;
use Integrated\Bundle\BrandBundle\Provider\ConnectorMissingThemeBlocksProvider;
use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Integrated\Bundle\ContentBundle\Document\Channel\ChannelType;
use Integrated\Bundle\PageBundle\Resolver\ThemeResolver;
use Integrated\Bundle\ThemeBundle\Templating\ThemeManager;
use PHPUnit\Framework\TestCase;
use Twig\Loader\FilesystemLoader;

final class ConnectorMissingThemeBlocksProviderTest extends TestCase
{
    public function testGetMissingBlocksForChannelFiltersByCurrentChannelAndThemeChain(): void
    {
        $channel = new Channel();
        $channel->setId('bakkers_in_bedrijf');
        $channel->setName('Bakkers in Bedrijf');
        $channel->setType(new ChannelType('website', 'Website'));

        $usageProvider = $this->createMock(BlockUsageProvider::class);
        $usageProvider
            ->expects(self::once())
            ->method('getTemplateUsagesPerBlock')
            ->willReturn([
                'existing_shared_block' => [
                    'base.html.twig|default|' => [
                        'template' => 'themes/default/base.html.twig',
                        'theme' => 'default',
                    ],
                ],
                'missing_current_channel_block' => [
                    'footer.html.twig|site|bakkers_in_bedrijf' => [
                        'template' => 'themes/site/footer.html.twig',
                        'theme' => 'site',
                        'channel_id' => 'bakkers_in_bedrijf',
                    ],
                ],
                'missing_fallback_block' => [
                    'base.html.twig|default|' => [
                        'template' => 'themes/default/base.html.twig',
                        'theme' => 'default',
                    ],
                ],
                'other_channel_block' => [
                    'footer.html.twig|site|other_channel' => [
                        'template' => 'themes/site/footer.html.twig',
                        'theme' => 'site',
                        'channel_id' => 'other_channel',
                    ],
                ],
                'other_theme_block' => [
                    'landing.html.twig|campaign|' => [
                        'template' => 'themes/campaign/landing.html.twig',
                        'theme' => 'campaign',
                    ],
                ],
            ]);

        $themeResolver = $this->createMock(ThemeResolver::class);
        $themeResolver
            ->expects(self::once())
            ->method('getTheme')
            ->with($channel)
            ->willReturn('site');

        $themeManager = new ThemeManager(new FilesystemLoader(), sys_get_temp_dir());
        $themeManager->registerTheme('default', [sys_get_temp_dir()]);
        $themeManager->registerTheme('site', [sys_get_temp_dir()], ['default']);
        $themeManager->registerTheme('campaign', [sys_get_temp_dir()]);

        $existingBlock = $this->createMock(Block::class);
        $existingBlock
            ->expects(self::once())
            ->method('getId')
            ->willReturn('existing_shared_block');

        $repository = $this->createMock(ObjectRepository::class);
        $repository
            ->expects(self::once())
            ->method('findBy')
            ->with(self::callback(static function (array $criteria): bool {
                if (!isset($criteria['id']['$in']) || !is_array($criteria['id']['$in'])) {
                    return false;
                }

                $ids = $criteria['id']['$in'];
                sort($ids);

                return $ids === ['existing_shared_block', 'missing_current_channel_block', 'missing_fallback_block'];
            }))
            ->willReturn([$existingBlock]);

        $documentManager = $this->createMock(DocumentManager::class);
        $documentManager
            ->expects(self::once())
            ->method('getRepository')
            ->with(Block::class)
            ->willReturn($repository);

        $provider = new ConnectorMissingThemeBlocksProvider(
            $usageProvider,
            $themeResolver,
            $themeManager,
            $documentManager,
        );

        self::assertSame(
            [
                [
                    'id' => 'missing_current_channel_block',
                    'usages' => [
                        [
                            'template' => 'themes/site/footer.html.twig',
                            'theme' => 'site',
                            'channel_id' => 'bakkers_in_bedrijf',
                        ],
                    ],
                ],
                [
                    'id' => 'missing_fallback_block',
                    'usages' => [
                        [
                            'template' => 'themes/default/base.html.twig',
                            'theme' => 'default',
                        ],
                    ],
                ],
            ],
            $provider->getMissingBlocksForChannel($channel)
        );
    }
}
