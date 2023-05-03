<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Integrated\Bundle\ContentBundle\Document\Channel\Channel;

class ChannelFixtures extends AbstractFixture
{
    public const CHANNEL = 'my_channel';

    public function load(ObjectManager $manager): void
    {
        $channel = new Channel();

        $channel->setId('my_channel');
        $channel->setName('My channel');
        $channel->setDomains(['localhost', 'localhost.e-active.nl']);
        $channel->setPrimaryDomain('localhost.e-active.nl');

        $this->addReference(self::CHANNEL, $channel);

        $manager->persist($channel);
        $manager->flush();
    }
}
