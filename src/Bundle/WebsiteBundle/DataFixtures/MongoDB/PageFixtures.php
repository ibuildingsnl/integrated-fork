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
use Integrated\Bundle\BlockBundle\Document\Block\Block;
use Integrated\Bundle\ContentBundle\DataFixtures\MongoDB\ChannelFixtures;
use Integrated\Bundle\ContentBundle\Document\Block\ContentBlock;
use Integrated\Bundle\ContentBundle\Document\Block\FacetBlock;
use Integrated\Bundle\ContentBundle\Document\Block\SearchBlock;
use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Integrated\Bundle\PageBundle\Document\Page\Grid\Column;
use Integrated\Bundle\PageBundle\Document\Page\Grid\Grid;
use Integrated\Bundle\PageBundle\Document\Page\Grid\Item;
use Integrated\Bundle\PageBundle\Document\Page\Grid\Row;
use Integrated\Bundle\PageBundle\Document\Page\Page;

class PageFixtures extends AbstractFixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $row = new Row();
        $row->addColumn($this->createColumn(8, [
            $this->createItem(1, $this->getReference(BlockFixtures::SEARCH_REFERENCE, SearchBlock::class)),
            $this->createItem(2, $this->getReference(BlockFixtures::CONTENT_REFERENCE, ContentBlock::class)),
        ]));

        $row->addColumn($this->createColumn(4, [
            $this->createItem(1, $this->getReference(BlockFixtures::FACET_REFERENCE, FacetBlock::class)),
        ]));

        $grid = new Grid();
        $grid->setId('main');
        $grid->setItems([$this->createItem(1, $row)]);

        $object = new Page();

        $object->setTitle('Search');
        $object->setPath('/search');
        $object->setLayout('@IntegratedWebsite/themes/default/base.html.twig');
        $object->setGrids([$grid]);
        $object->setChannel($this->getReference(ChannelFixtures::CHANNEL, Channel::class));

        $manager->persist($object);

        $manager->flush();
    }

    private function createColumn(int $size, array $items): Column
    {
        $object = new Column();
        $object->setSize($size);
        $object->setItems($items);

        return $object;
    }

    private function createItem(int $order, Block|Row $content): Item
    {
        $object = new Item();
        $object->setOrder($order);

        if ($content instanceof Block) {
            $object->setBlock($content);
        } else {
            $object->setRow($content);
        }

        return $object;
    }

    public function getDependencies()
    {
        return [
            BlockFixtures::class,
            ChannelFixtures::class,
        ];
    }
}
