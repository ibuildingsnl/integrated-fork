<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Common\Content\Tests\Channel;

use PHPUnit\Framework\TestCase;
use Integrated\Common\Content\Channel\ChannelContextInterface;
use Integrated\Common\Content\Channel\ChannelInterface;
use Integrated\Common\Content\Channel\ChannelContext;

/**
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 */
class ChannelContextTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(ChannelContextInterface::class, $this->getInstance());
    }

    public function testSetGetChannel()
    {
        $channel = $this->createMock(ChannelInterface::class);
        $instance = $this->getInstance();

        $this->assertNull($instance->getChannel());

        $instance->setChannel($channel);

        $this->assertSame($channel, $instance->getChannel());

        $instance->setChannel();

        $this->assertNull($instance->getChannel());
    }

    /**
     * @return ChannelContext
     */
    protected function getInstance()
    {
        return new ChannelContext();
    }
}
