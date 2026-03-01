<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\PageBundle\Document\Page;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Integrated\Bundle\PageBundle\Document\Page\Grid\Grid;
use Integrated\Bundle\PageBundle\Document\Page\Grid\Item;
use Integrated\Bundle\PageBundle\Document\Page\Grid\Row;
use Integrated\Common\Content\Channel\ChannelInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Page document.
 *
 * @author Ger Jan van den Bosch <gerjan@e-active.nl>
 *
 * @todo find a way to fix unique validation (INTEGRATED-481)
 */
abstract class AbstractPage
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    #[Assert\NotBlank]
    protected $path;

    /**
     * @var string
     */
    #[Assert\NotBlank]
    protected $layout;

    /**
     * @var int
     */
    protected $layoutVersion = 1;

    /**
     * @var array
     */
    protected $layoutPayload = [];

    /**
     * @var array
     */
    protected $layoutMeta = [];

    /**
     * @var array
     */
    protected $legacy = [];

    /**
     * @var Collection<Grid>
     */
    protected $grids;

    /**
     * @var string[]
     */
    protected $blockIds = [];

    /**
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * @var \DateTime
     */
    protected $updatedAt;

    /**
     * @var ChannelInterface
     */
    protected $channel;

    public function __construct()
    {
        $this->grids = new ArrayCollection();
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param string $path
     *
     * @return $this
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @return string
     */
    public function getLayout()
    {
        return $this->layout;
    }

    /**
     * @param string $layout
     *
     * @return $this
     */
    public function setLayout($layout)
    {
        $this->layout = $layout;

        return $this;
    }

    /**
     * @return int
     */
    public function getLayoutVersion()
    {
        return (int) $this->layoutVersion;
    }

    /**
     * @param int $layoutVersion
     *
     * @return $this
     */
    public function setLayoutVersion($layoutVersion)
    {
        $this->layoutVersion = (int) $layoutVersion;

        return $this;
    }

    /**
     * @return array
     */
    public function getLayoutPayload()
    {
        return $this->layoutPayload;
    }

    /**
     * @return $this
     */
    public function setLayoutPayload(array $layoutPayload = [])
    {
        $this->layoutPayload = $layoutPayload;

        return $this;
    }

    /**
     * @return array
     */
    public function getLayoutMeta()
    {
        return $this->layoutMeta;
    }

    /**
     * @return $this
     */
    public function setLayoutMeta(array $layoutMeta = [])
    {
        $this->layoutMeta = $layoutMeta;

        return $this;
    }

    /**
     * @return array
     */
    public function getLegacy()
    {
        return $this->legacy;
    }

    /**
     * @return $this
     */
    public function setLegacy(array $legacy = [])
    {
        $this->legacy = $legacy;

        return $this;
    }

    /**
     * @return Grid[]
     */
    public function getGrids()
    {
        return $this->grids->toArray();
    }

    /**
     * @return $this
     */
    public function setGrids(array $grids)
    {
        $this->grids = new ArrayCollection($grids);
        $this->updateBlockIdsFromGrids();

        return $this;
    }

    /**
     * @return $this
     */
    public function addGrid(Grid $grid)
    {
        $this->grids->add($grid);
        $this->updateBlockIdsFromGrids();

        return $this;
    }

    /**
     * @return $this
     */
    public function removeGrid(Grid $grid)
    {
        $this->grids->removeElement($grid);
        $this->updateBlockIdsFromGrids();

        return $this;
    }

    /**
     * @return string[]
     */
    public function getBlockIds(): array
    {
        return $this->blockIds;
    }

    /**
     * @param array<int, scalar> $blockIds
     */
    public function setBlockIds(array $blockIds): self
    {
        $indexed = [];
        foreach ($blockIds as $blockId) {
            $value = trim((string) $blockId);
            if ($value === '') {
                continue;
            }

            $indexed[$value] = true;
        }

        $this->blockIds = array_keys($indexed);

        return $this;
    }

    public function updateBlockIdsFromGrids(): self
    {
        $indexed = [];
        foreach ($this->grids as $grid) {
            $this->collectBlockIdsFromItems($grid->getItems(), $indexed);
        }

        $this->blockIds = array_keys($indexed);

        return $this;
    }

    /**
     * @param Item[]              $items
     * @param array<string, bool> $indexed
     */
    private function collectBlockIdsFromItems(array $items, array &$indexed): void
    {
        foreach ($items as $item) {
            $block = $item->getBlock();
            if ($block !== null) {
                $blockId = $block->getId();
                if ($blockId !== '') {
                    $indexed[$blockId] = true;
                }
            }

            $row = $item->getRow();
            if (!$row instanceof Row) {
                continue;
            }

            foreach ($row->getColumns() as $column) {
                $this->collectBlockIdsFromItems($column->getItems(), $indexed);
            }
        }
    }

    /**
     * @return int
     */
    public function indexOf(Grid $grid)
    {
        return $this->grids->indexOf($grid);
    }

    /**
     * @param string $id
     *
     * @return Grid|null
     */
    public function getGrid($id)
    {
        foreach ($this->grids as $grid) {
            if ($grid->getId() == $id) {
                return $grid;
            }
        }

        return null;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return $this
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @return $this
     */
    public function setUpdatedAt(\DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return ChannelInterface
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * @return $this
     */
    public function setChannel(ChannelInterface $channel)
    {
        $this->channel = $channel;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->getPath();
    }

    /**
     * @return string
     */
    public function getType()
    {
        return 'abstract';
    }

    /**
     * @return bool
     */
    public function isLocked()
    {
        return false;
    }

    /**
     * @return string|null
     */
    public function getDomain()
    {
        if (!$channel = $this->getChannel()) {
            return null;
        }

        if ($channel->getPrimaryDomain()) {
            return $channel->getPrimaryDomain();
        }

        if ($channel instanceof Channel) {
            $domains = $channel->getDomains();
            if (\count($domains)) {
                return $domains[0];
            }
        }

        return null;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getPath();
    }
}
