<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\WebsiteBundle\DataFixtures\MongoDB;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Integrated\Bundle\ContentBundle\Document\SearchSelection\SearchSelection;

class SearchSelectionFixture extends AbstractFixture
{
    public const SEARCH_REFERENCE = '916c879a-f691-4bf2-9fa0-107266209f80';

    public function load(ObjectManager $manager): void
    {
        $selection = new SearchSelection();

        $selection->setTitle('Search content');
        $selection->setPublic(true);

        $this->setReference(self::SEARCH_REFERENCE, $selection);

        $manager->persist($selection);
        $manager->flush();
    }
}
