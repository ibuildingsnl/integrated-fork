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
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Integrated\Bundle\ContentBundle\Document\Block\ContentBlock;
use Integrated\Bundle\ContentBundle\Document\Block\Embedded\FacetField;
use Integrated\Bundle\ContentBundle\Document\Block\FacetBlock;
use Integrated\Bundle\ContentBundle\Document\Block\SearchBlock;
use Integrated\Bundle\ContentBundle\Document\SearchSelection\SearchSelection;

class BlockFixtures extends AbstractFixture implements DependentFixtureInterface
{
    public const CONTENT_REFERENCE = ' 93ff81b3-2da8-4edb-a0ba-ad23697bc9fa ';
    public const SEARCH_REFERENCE = '3335ea27-f81b-4385-baff-8677f1836ba0';
    public const FACET_REFERENCE = '4c77a640-ee06-49ed-a7bb-8aa90d419fc3';

    public function load(ObjectManager $manager): void
    {
        $object = new ContentBlock();

        $object->setTitle('Search results');
        $object->setSearchSelection($this->getReference(SearchSelectionFixture::SEARCH_REFERENCE, SearchSelection::class));
        $object->setFacetFields(['facet_keyword']);
        $object->setLayout('search_results.html.twig');

        $this->setReference(self::CONTENT_REFERENCE, $object);

        $manager->persist($object);

        $object = new SearchBlock();

        $object->setTitle('Your search');
        $object->setUrl('/search');
        $object->setBlock($this->getReference(self::CONTENT_REFERENCE, ContentBlock::class));
        $object->setLayout('default.html.twig');

        $this->setReference(self::SEARCH_REFERENCE, $object);

        $manager->persist($object);

        $object = new FacetBlock();

        $object->setTitle('Facet');
        $object->setBlock($this->getReference(self::CONTENT_REFERENCE, ContentBlock::class));

        $field = new FacetField();
        $field->setName('Keywords');
        $field->setField('facet_keywords');

        $object->setFields([$field]);
        $object->setLayout('default.html.twig');

        $this->setReference(self::FACET_REFERENCE, $object);

        $manager->persist($object);
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            SearchSelectionFixture::class,
        ];
    }
}
