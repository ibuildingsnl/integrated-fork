<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ThemeBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Integrated\Bundle\ThemeBundle\Entity\Scraper\Block;

class Scraper
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $channelId;

    /**
     * @var string
     */
    private $templateName;

    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $template;

    /**
     * @var int
     */
    private $lastModified;

    /**
     * @var string
     */
    private $lastError;

    /**
     * @var ArrayCollection
     */
    private $blocks;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->lastModified = time();
        $this->blocks = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getChannelId(): ?string
    {
        return $this->channelId;
    }

    public function setChannelId(string $channelId): void
    {
        $this->channelId = $channelId;
    }

    /**
     * @return string
     */
    public function getTemplateName(): ?string
    {
        return $this->templateName;
    }

    public function setTemplateName(string $templateName): void
    {
        $this->templateName = $templateName;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): void
    {
        $this->url = $url;
    }

    public function getTemplate(): ?string
    {
        return $this->template;
    }

    public function setTemplate(?string $template): void
    {
        $this->template = $template;
    }

    public function getLastModified(): int
    {
        return $this->lastModified;
    }

    public function setLastModified(int $lastModified): void
    {
        $this->lastModified = $lastModified;
    }

    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    /**
     * @param string $lastError
     */
    public function setLastError(?string $lastError = null): void
    {
        $this->lastError = \is_string($lastError) ? substr($lastError, 0, 800) : null;
    }

    /**
     * @return Block[]
     */
    public function getBlocks(): array
    {
        return $this->blocks->toArray();
    }

    /**
     * @param Block[] $blocks
     */
    public function setBlocks(array $blocks): void
    {
        $this->blocks = new ArrayCollection($blocks);
    }

    public function addBlock(Block $block): void
    {
        if (!$this->blocks->contains($block)) {
            $this->blocks->add($block);
        }
    }

    public function hasBlock(Block $block): bool
    {
        return $this->blocks->contains($block);
    }

    public function removeBlock(Block $block): void
    {
        $this->blocks->removeElement($block);
    }
}
