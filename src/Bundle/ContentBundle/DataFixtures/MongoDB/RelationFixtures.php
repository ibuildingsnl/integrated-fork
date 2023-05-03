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
use Integrated\Bundle\ContentBundle\Document\ContentType\ContentType;
use Integrated\Bundle\ContentBundle\Document\Relation\Relation;

class RelationFixtures extends AbstractFixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $relation = new Relation();

        $relation->setId('keywords');
        $relation->setName('Keywords');
        $relation->setType('taxonomy');
        $relation->setMultiple(true);

        $relation->addSource($this->getReference('article', ContentType::class));
        $relation->addSource($this->getReference('blog', ContentType::class));

        $relation->addTarget($this->getReference('taxonomy', ContentType::class));

        $manager->persist($relation);

        $relation = new Relation();

        $relation->setId('media');
        $relation->setName('Media');
        $relation->setType('embedded');
        $relation->setMultiple(true);

        $relation->addSource($this->getReference('article', ContentType::class));
        $relation->addSource($this->getReference('blog', ContentType::class));

        $relation->addTarget($this->getReference('image', ContentType::class));

        $manager->persist($relation);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            ContentTypeFixtures::class,
        ];
    }
}
