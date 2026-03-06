<?php

namespace Integrated\Bundle\ChannelBundle\Tests\Model;

use Integrated\Bundle\ChannelBundle\Model\ConfigRepository;
use PHPUnit\Framework\TestCase;

class ConfigRepositoryTest extends TestCase
{
    public function testFindByAdaptorUsesAdapterField(): void
    {
        $repository = $this->getMockBuilder(ConfigRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['findBy'])
            ->getMock();

        $repository
            ->expects(self::once())
            ->method('findBy')
            ->with(['adapter' => 'oauth-test'])
            ->willReturn([]);

        self::assertSame([], $repository->findByAdaptor('oauth-test'));
    }
}
