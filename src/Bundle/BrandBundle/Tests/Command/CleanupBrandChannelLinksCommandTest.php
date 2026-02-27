<?php

namespace Integrated\Bundle\BrandBundle\Tests\Command;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\BrandBundle\Command\CleanupBrandChannelLinksCommand;
use Integrated\Bundle\BrandBundle\Document\Brand;
use Integrated\Bundle\BrandBundle\Document\BrandProfile;
use Integrated\Bundle\BrandBundle\Document\ChannelLink;
use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Integrated\Bundle\ContentBundle\Document\Channel\ChannelType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class CleanupBrandChannelLinksCommandTest extends TestCase
{
    public function testRemovesNullChannelLinks(): void
    {
        $profile = new BrandProfile();
        $profile->name = 'Test brand';
        $brand = new Brand($profile);

        $typeA = new ChannelType('type-a', 'Type A');
        $typeB = new ChannelType('type-b', 'Type B');

        $channel = new Channel();
        $channel->setId('channel-1');

        $brand->addChannelLink(new ChannelLink($typeA, null, false));
        $brand->addChannelLink(new ChannelLink($typeB, $channel, false));

        $repo = $this->createMock(\Doctrine\ODM\MongoDB\Repository\DocumentRepository::class);
        $repo->method('findAll')->willReturn([$brand]);

        $dm = $this->createMock(DocumentManager::class);
        $dm->method('getRepository')->with(Brand::class)->willReturn($repo);
        $dm->expects(self::once())->method('persist')->with($brand);
        $dm->expects(self::once())->method('flush');

        $command = new CleanupBrandChannelLinksCommand($dm);
        $tester = new CommandTester($command);
        $tester->execute([]);

        self::assertSame(1, $brand->getChannelLinks()->count());
        $remaining = $brand->getChannelLinks()->first();
        self::assertNotNull($remaining);
        self::assertNotNull($remaining->channel);
    }
}
