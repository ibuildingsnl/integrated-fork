<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Document\Bulk;

use Doctrine\Common\Collections\ArrayCollection;
use Integrated\Common\Bulk\BulkActionInterface;
use Integrated\Common\Content\ContentInterface;

/**
 * @author Patrick Mestebeld <patrick@e-active.nl>
 */
class BulkAction
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @var \DateTime
     */
    private $executedAt;

    /**
     * @var string|null
     */
    private $ownerId;

    /**
     * @var string|null
     */
    private $filters;

    /**
     * @var ArrayCollection|ContentInterface[]
     */
    private $selection;

    /**
     * @var ArrayCollection|BulkActionInterface[]
     */
    private $actions;

    /**
     * BulkAction constructor.
     */
    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->selection = new ArrayCollection();
        $this->actions = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
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
    public function getExecutedAt()
    {
        return $this->executedAt;
    }

    /**
     * @return $this
     */
    public function setExecutedAt(\DateTime $executedAt)
    {
        $this->executedAt = $executedAt;

        return $this;
    }

    public function getOwnerId(): ?string
    {
        return $this->ownerId;
    }

    public function setOwnerId(?string $ownerId = null): self
    {
        $this->ownerId = $ownerId;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getFilters()
    {
        return $this->filters === null ? null : json_decode($this->filters, true);
    }

    public function setFilters(?array $filters = null)
    {
        $this->filters = $filters === null ? null : json_encode($filters);
    }

    /**
     * @return ContentInterface[]
     */
    public function getSelection()
    {
        return $this->selection->toArray();
    }

    /**
     * @param ContentInterface[] $contents
     *
     * @return $this
     */
    public function setSelection(iterable $contents)
    {
        $this->selection->clear();

        foreach ($contents as $content) {
            $this->addSelection($content);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function addSelection(ContentInterface $content)
    {
        if (!$this->selection->contains($content)) {
            $this->selection->add($content);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function removeSelection(ContentInterface $content)
    {
        $this->selection->removeElement($content);

        return $this;
    }

    /**
     * @return BulkActionInterface[]
     */
    public function getActions()
    {
        return $this->actions->toArray();
    }

    /**
     * @param BulkActionInterface[] $actions
     *
     * @return $this
     */
    public function setActions(iterable $actions)
    {
        $this->actions->clear();

        foreach ($actions as $action) {
            $this->addAction($action);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function addAction(BulkActionInterface $action)
    {
        if (!$this->actions->contains($action)) {
            $this->actions->add($action);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function removeAction(BulkActionInterface $action)
    {
        $this->actions->removeElement($action);

        return $this;
    }
}
