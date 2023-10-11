<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ImportBundle\Document;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ODM\MongoDB\PersistentCollection;
use Integrated\Bundle\ContentBundle\Document\Relation\Relation;
use Integrated\Bundle\ImportBundle\Document\Embedded\ImportField;
use Integrated\Common\Content\Channel\ChannelInterface;

class ImportDefinition
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $contentType;

    /**
     * @var string
     */
    private $name;

    /**
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @var \DateTime
     */
    private $executedAt;

    /**
     * @var Collection
     */
    protected $channels;

    /**
     * @var string
     */
    private $fileId;

    /*
     * @var ImportField[]
     */
    private $fields;

    /**
     * @var string
     */
    private $websiteBaseUrl;

    /**
     * @var string
     */
    private $imageContentType;

    /**
     * @var Relation
     */
    private $imageRelation;

    /**
     * @var string
     */
    private $fileContentType;

    /**
     * @var Relation
     */
    private $fileRelation;

    /**
     * @var string
     */
    private $authorContentType;

    /**
     * @var string
     */
    private $connectionUrl;

    /**
     * @var string
     */
    private $connectionQuery;

    /**
     * ImportDefition constructor.
     */
    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->channels = new ArrayCollection();
        $this->fields = new ArrayCollection();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getContentType(): ?string
    {
        return $this->contentType;
    }

    public function setContentType(string $contentType): void
    {
        $this->contentType = $contentType;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
    }

    public function getExecutedAt(): \DateTime
    {
        return $this->executedAt;
    }

    public function setExecutedAt(\DateTime $executedAt)
    {
        $this->executedAt = $executedAt;
    }

    public function setChannels(Collection $channels)
    {
        $this->channels->clear();
        $this->channels = new ArrayCollection();

        foreach ($channels as $channel) {
            $this->addChannel($channel);
        }
    }

    public function getChannels(): array
    {
        return $this->channels->toArray();
    }

    public function addChannel(ChannelInterface $channel)
    {
        if (!$this->channels->contains($channel)) {
            $this->channels->add($channel);
        }
    }

    public function hasChannel(ChannelInterface $channel): bool
    {
        return $this->channels->contains($channel);
    }

    public function removeChannel(ChannelInterface $channel)
    {
        $this->channels->removeElement($channel);
    }

    public function getFileId(): ?string
    {
        return $this->fileId;
    }

    public function setFileId(?string $fileId): void
    {
        $this->fileId = $fileId;
    }

    public function getFields(): PersistentCollection
    {
        return $this->fields;
    }

    public function getField($name): ?ImportField
    {
        foreach ($this->getFields() as $field) {
            if ($field->getName() == $name) {
                return $field;
            }
        }

        return null;
    }

    public function hasField($name): bool
    {
        foreach ($this->getFields() as $field) {
            if ($field->getName() == $name) {
                return true;
            }
        }

        return false;
    }

    public function setFields(array $fields)
    {
        $this->fields = $fields;
    }

    public function getWebsiteBaseUrl(): ?string
    {
        return $this->websiteBaseUrl;
    }

    public function setWebsiteBaseUrl(string $websiteBaseUrl)
    {
        $this->websiteBaseUrl = $websiteBaseUrl;
    }

    public function getImageContentType(): ?string
    {
        return $this->imageContentType;
    }

    public function setImageContentType(?string $imageContentType)
    {
        $this->imageContentType = $imageContentType;
    }

    public function getImageRelation(): ?Relation
    {
        return $this->imageRelation;
    }

    public function setImageRelation(Relation $imageRelation)
    {
        $this->imageRelation = $imageRelation;
    }

    public function getFileContentType(): ?string
    {
        return $this->fileContentType;
    }

    public function setFileContentType(?string $fileContentType)
    {
        $this->fileContentType = $fileContentType;
    }

    public function getFileRelation(): ?Relation
    {
        return $this->fileRelation;
    }

    public function setFileRelation(?Relation $fileRelation): void
    {
        $this->fileRelation = $fileRelation;
    }

    public function getAuthorContentType(): ?string
    {
        return $this->authorContentType;
    }

    public function setAuthorContentType(?string $authorContentType): void
    {
        $this->authorContentType = $authorContentType;
    }

    public function __clone()
    {
        $this->id = null;
    }

    public function getConnectionUrl(): ?string
    {
        return $this->connectionUrl;
    }

    public function setConnectionUrl(?string $connectionUrl): void
    {
        $this->connectionUrl = $connectionUrl;
    }

    public function getConnectionQuery(): ?string
    {
        return $this->connectionQuery;
    }

    public function setConnectionQuery(?string $connectionQuery): void
    {
        $this->connectionQuery = $connectionQuery;
    }
}
