<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\WebsiteBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Integrated\Bundle\ChannelBundle\Model\Config;
use Integrated\Bundle\ContentBundle\DataFixtures\MongoDB\ChannelFixtures;

class ConnectorFixtures extends AbstractFixture
{
    public function load(ObjectManager $manager): void
    {
        $config = new Config();

        $config->setName('My channel');
        $config->setAdapter('website');
        $config->setChannels([ChannelFixtures::CHANNEL]);

        $config->getOptions()->set('theme', 'default');

        $manager->persist($config);
        $manager->flush();
    }
}
