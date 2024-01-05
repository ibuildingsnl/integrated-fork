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
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Integrated\Bundle\ContentBundle\DataFixtures\Util\StorageUtil;
use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Integrated\Bundle\ContentBundle\Document\Content\File;
use Integrated\Bundle\ContentBundle\Document\Content\Image;
use Integrated\Common\Storage\ManagerInterface;

class MediaFixtures extends AbstractFixture implements DependentFixtureInterface
{
    private const FILES_DOCUMENTS = [
        __DIR__.'/../../Resources/assets/fixtures/example.docx',
        __DIR__.'/../../Resources/assets/fixtures/example.pdf',
        __DIR__.'/../../Resources/assets/fixtures/example.xlsx',
    ];
    private const FILES_IMAGES = [
        __DIR__.'/../../Resources/assets/fixtures/landscape.png',
        __DIR__.'/../../Resources/assets/fixtures/portrait.png',
        __DIR__.'/../../Resources/assets/fixtures/square.png',
    ];

    private ManagerInterface $manager;
    private Generator $faker;

    public function __construct(ManagerInterface $manager, Generator $faker = null)
    {
        $this->manager = $manager;
        $this->faker = $faker ?: Factory::create();
    }

    public function load(ObjectManager $manager)
    {
        $this->faker->seed(123456789);

        try {
            for ($x = 0; $x < 15; ++$x) {
                $manager->persist($this->createFile());
            }

            for ($x = 0; $x < 15; ++$x) {
                $manager->persist($this->createImage());
            }

            $manager->flush();
        } finally {
            $this->faker->seed(); // reset seed to be random again just to be on the safe side
        }
    }

    private function createFile(): File
    {
        $object = new File();

        $object->setContentType('file');
        $object->setFile(StorageUtil::creatFromPath($this->manager, $this->faker->randomElement(self::FILES_DOCUMENTS)));
        $object->setTitle($this->faker->sentence());
        $object->setDescription($this->faker->paragraph());
        $object->setDisabled($this->faker->boolean(10));
        $object->setChannels([$this->getReference(ChannelFixtures::CHANNEL, Channel::class)]);

        return $object;
    }

    private function createImage(): Image
    {
        $object = new Image();

        $object->setContentType('image');
        $object->setFile(StorageUtil::creatFromPath($this->manager, $this->faker->randomElement(self::FILES_IMAGES)));
        $object->setTitle($this->faker->sentence());
        $object->setDescription($this->faker->paragraph());
        $object->setDisabled($this->faker->boolean(10));
        $object->setChannels([$this->getReference(ChannelFixtures::CHANNEL, Channel::class)]);

        return $object;
    }

    public function getDependencies()
    {
        return [
            ChannelFixtures::class,
        ];
    }
}
