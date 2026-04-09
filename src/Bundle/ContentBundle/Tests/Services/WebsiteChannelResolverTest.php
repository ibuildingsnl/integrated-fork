<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Services;

use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Integrated\Bundle\ContentBundle\Document\Channel\ChannelType;
use Integrated\Bundle\ContentBundle\Services\WebsiteChannelResolver;
use Integrated\Common\Content\Channel\ChannelManagerInterface;
use PHPUnit\Framework\TestCase;

final class WebsiteChannelResolverTest extends TestCase
{
    public function testResolverUsesSchemaQueriesAndCachesResult(): void
    {
        $channelManager = $this->createMock(ChannelManagerInterface::class);
        $channelManager->expects(self::once())
            ->method('findBy')
            ->willReturnCallback(function (array $criteria): array {
                if (
                    $criteria === [
                        '$or' => [
                            ['type.$id' => 'website'],
                            ['type.name' => 'Website'],
                        ],
                    ]
                ) {
                    return [
                        $this->createChannel('zeta_site', 'Zeta', 'website', 'Website'),
                        $this->createChannel('alpha_site', 'Alpha', 'website', 'Website'),
                        $this->createChannel('beta_site', 'Beta', 'legacy_website', 'Website'),
                    ];
                }

                return [];
            });
        $channelManager->expects(self::never())->method('findAll');

        $resolver = new WebsiteChannelResolver($channelManager);

        self::assertSame(['alpha_site', 'beta_site', 'zeta_site'], $resolver->getWebsiteChannelIds());
        self::assertSame(['alpha_site', 'beta_site', 'zeta_site'], $resolver->getWebsiteChannelIds());
    }

    public function testResolverBuildsChoicesAndFallsBackToChannelIdAsLabel(): void
    {
        $channelManager = $this->createMock(ChannelManagerInterface::class);
        $channelManager->expects(self::once())
            ->method('findBy')
            ->with(
                [
                    '$or' => [
                        ['type.$id' => 'website'],
                        ['type.name' => 'Website'],
                    ],
                ],
                ['name' => 'asc']
            )
            ->willReturn(
                [$this->createChannel('website_nl', '', 'website', 'Website')]
            );
        $channelManager->expects(self::never())->method('findAll');

        $resolver = new WebsiteChannelResolver($channelManager);

        self::assertSame(['website_nl' => 'website_nl'], $resolver->getWebsiteChannelChoices());
    }

    private function createChannel(string $id, string $name, string $typeId, string $typeName): Channel
    {
        $channel = new Channel();
        $channel->setId($id);
        $channel->setName($name);
        $channel->setType(new ChannelType($typeId, $typeName));

        return $channel;
    }
}
