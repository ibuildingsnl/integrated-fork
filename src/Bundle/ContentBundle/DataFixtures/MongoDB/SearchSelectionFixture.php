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
use Integrated\Bundle\ContentBundle\Document\SearchSelection\SearchSelection;

class SearchSelectionFixture extends AbstractFixture
{
    public function load(ObjectManager $manager): void
    {
        $selection = new SearchSelection();

        $selection->setTitle('All content');
        $selection->setPublic(true);

        $manager->persist($selection);
        $manager->flush();
    }
}
