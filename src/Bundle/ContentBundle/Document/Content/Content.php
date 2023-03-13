<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Document\Content;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Integrated\Bundle\ContentBundle\Document\Content\Embedded\Metadata;
use Integrated\Bundle\ContentBundle\Document\Content\Embedded\PublishTime;
use Integrated\Bundle\SlugBundle\Mapping\Attributes\Slug;
use Integrated\Common\Content\Channel\ChannelInterface;
use Integrated\Common\Content\ChannelableInterface;
use Integrated\Common\Content\ConnectorInterface;
use Integrated\Common\Content\ConnectorTrait;
use Integrated\Common\Content\ContentInterface;
use Integrated\Common\Content\Embedded\RelationInterface;
use Integrated\Common\Content\ExtensibleInterface;
use Integrated\Common\Content\ExtensibleTrait;
use Integrated\Common\Content\FeaturedInterface;
use Integrated\Common\Content\MetadataInterface;
use Integrated\Common\Content\PremiumInterface;
use Integrated\Common\Content\PublishableInterface;
use Integrated\Common\Content\PublishTimeInterface;
use Integrated\Common\Content\RegistryInterface;
use Integrated\Common\Form\Mapping\Attributes as Type;

abstract class Content implements ContentInterface, ExtensibleInterface, MetadataInterface, ChannelableInterface, PublishableInterface, ConnectorInterface, FeaturedInterface, PremiumInterface
{
    use ConnectorTrait;
    use ExtensibleTrait;

    /**
     * @var string
     */
    protected $id;

    /**
     * @var Collection
     */
    protected $channels;

    /**
     * @var Channel
     */
    protected $primaryChannel;

    /**
     * @var string
     */
    #[Slug(fields: ['id'])]
    #[Type\Field(options: ['attr' => ['style' => 'sidebar']], location: 'sidebar')]
    protected $slug;

    /**
     * @var string the type of the ContentType
     */
    protected $contentType;

    /**
     * @var ArrayCollection
     */
    protected $relations;

    /**
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * @var \DateTime
     */
    protected $updatedAt;

    /**
     * @var PublishTime
     */
    #[Type\Field(type: 'Integrated\Bundle\ContentBundle\Form\Type\PublishTimeType', location: 'custom')]
    protected $publishTime;

    /**
     * @var bool
     */
    protected $published = true;

    /**
     * @var bool
     */
    #[Type\Field(type: 'Integrated\Bundle\ContentBundle\Form\Type\CheckboxSwitcherType', options: [
        'attr' => [
            'align_with_widget' => true,
        ],
    ], location: 'options')]
    protected $premium;

    /**
     * @var bool
     */
    #[Type\Field(type: 'Integrated\Bundle\ContentBundle\Form\Type\CheckboxSwitcherType', options: [
        'attr' => [
            'align_with_widget' => true,
        ],
    ], location: 'options')]
    protected $featured;

    /**
     * @var bool
     */
    #[Type\Field(type: 'Integrated\Bundle\ContentBundle\Form\Type\CheckboxSwitcherType', options: [
        'attr' => [
            'align_with_widget' => true,
        ],
    ], location: 'custom')]
    protected $disabled;

    /**
     * @var Metadata
     */
    protected $metadata;

    /**
     * @var Embedded\CustomFields
     */
    protected $customFields;

    /**
     * @var string
     */
    #[Type\Field(options: [
        'label' => 'Copyright restrictions',
        'attr' => ['style' => 'sidebar', 'icon' => 'copyright'],
    ], location: 'sidebar')]
    protected $copyrightRestrictions;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->relations = new ArrayCollection();
        $this->updatedAt = new \DateTime();
        $this->publishTime = new PublishTime();
        $this->channels = new ArrayCollection();
        $this->connectors = new ArrayCollection();
    }

    /**
     * @return string|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @param string $slug
     *
     * @return $this
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * {@inheritdoc}
     */
    public function setContentType($contentType)
    {
        $this->contentType = $contentType;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRelations()
    {
        // should always be instanceOf collection, but due to corrupt database can sometimes be null
        if (!$this->relations instanceof Collection) {
            $this->relations = new ArrayCollection();
        }

        return $this->relations;
    }

    /**
     * {@inheritdoc}
     */
    public function setRelations(Collection $relations)
    {
        foreach ($relations as $relation) {
            if ($relation instanceof RelationInterface) {
                $this->addRelation($relation);
            }
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addRelation(RelationInterface $relation)
    {
        if ($exist = $this->getRelation($relation->getRelationId())) {
            $exist->addReferences($relation->getReferences());
        } else {
            $this->getRelations()->add($relation);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeRelation(RelationInterface $relation)
    {
        $this->getRelations()->removeElement($relation);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRelation($relationId)
    {
        return $this->getRelations()->filter(function ($relation) use ($relationId) {
            if ($relation instanceof RelationInterface) {
                if ($relation->getRelationId() == $relationId) {
                    return true;
                }
            }

            return false;
        })->first();
    }

    /**
     * @return ArrayCollection|false
     */
    public function getRelationsByRelationType($relationType)
    {
        return $this->getRelations()->filter(function ($relation) use ($relationType) {
            if ($relation instanceof RelationInterface) {
                if ($relation->getRelationType() == $relationType) {
                    return true;
                }
            }

            return false;
        });
    }

    /**
     * @return array|bool
     */
    public function getReferencesByRelationType($relationType)
    {
        if ($relations = $this->getRelationsByRelationType($relationType)) {
            $references = [];

            /** @var RelationInterface $relation */
            foreach ($relations as $relation) {
                $references = array_merge($references, $relation->getReferences()->toArray());
            }

            return $references;
        }

        return false;
    }

    /**
     * @return array|bool
     */
    public function getReferencesByRelationTypes(array $relationTypes)
    {
        $references = [];
        foreach ($relationTypes as $relationType) {
            $references = array_merge($references, $this->getReferencesByRelationType($relationType));
        }

        if (\count($references) > 0) {
            return $references;
        }

        return false;
    }

    /**
     * @return Content|null
     */
    public function getReferenceByRelationType($relationType)
    {
        $references = $this->getReferencesByRelationType($relationType);

        if (\is_array($references) && \count($references)) {
            return $references[0];
        }

        return null;
    }

    /**
     * @param string $relationId
     * @param bool   $published
     * @param string $channelId
     *
     * @return ArrayCollection
     */
    public function getReferencesByRelationId($relationId, $published = true, ChannelInterface $channel = null): ArrayCollection|array
    {
        foreach ($this->getRelations() as $relation) {
            if ($relation instanceof RelationInterface &&
                $relation->getRelationId() == $relationId &&
                $references = $relation->getReferences()
            ) {
                return $references->filter(fn (ContentInterface $content) => $content instanceof self &&
                    (!$published || $content->isPublished()) &&
                    (!$channel || $content->hasChannel($channel)));
            }
        }

        return new ArrayCollection();
    }

    /**
     * @param string $relationId
     * @param bool   $published
     *
     * @return Content|null
     */
    public function getReferenceByRelationId($relationId, $published = true)
    {
        if ($references = $this->getReferencesByRelationId($relationId, $published)) {
            return $references->first();
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
     * {@inheritdoc}
     */
    public function getPublishTime(): PublishTimeInterface
    {
        return $this->publishTime;
    }

    /**
     * {@inheritdoc}
     */
    public function setPublishTime(PublishTimeInterface $publishTime)
    {
        $this->publishTime = $publishTime;

        return $this;
    }

    /**
     * @return bool
     *
     * @deprecated
     */
    public function getPublished()
    {
        return $this->isPublished();
    }

    /**
     * {@inheritdoc}
     */
    public function isPublished($checkPublishTime = true): bool
    {
        $published = true;

        if ($checkPublishTime && $this->publishTime instanceof PublishTime) {
            $published = $this->publishTime->isPublished();
        }

        return $published && !$this->disabled;
    }

    public function isPremium(): bool
    {
        return $this->premium ?: false;
    }

    public function setPremium(bool $premium): static
    {
        $this->premium = $premium;

        return $this;
    }

    public function isFeatured(): bool
    {
        return $this->featured ?: false;
    }

    public function setFeatured(bool $featured): static
    {
        $this->featured = $featured;

        return $this;
    }

    public function isDisabled(): bool
    {
        return $this->disabled ?: false;
    }

    /**
     * @param bool $disabled
     *
     * @return $this
     */
    public function setDisabled($disabled)
    {
        $this->disabled = $disabled;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata()
    {
        if (null === $this->metadata) {
            $this->metadata = new Metadata();
        }

        return $this->metadata;
    }

    /**
     * {@inheritdoc}
     */
    public function setMetadata(RegistryInterface $metadata = null)
    {
        if (null !== $metadata && !$metadata instanceof Metadata) {
            $metadata = new Metadata($metadata->toArray());
        }

        $this->metadata = $metadata;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setChannels(Collection $channels)
    {
        $this->channels->clear();
        $this->channels = new ArrayCollection();

        foreach ($channels as $channel) {
            $this->addChannel($channel); // type check
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getChannels()
    {
        return $this->channels?->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function addChannel(ChannelInterface $channel)
    {
        if (!$this->channels->contains($channel)) {
            $this->channels->add($channel);
        }

        if (null === $this->primaryChannel) {
            $this->setPrimaryChannel($channel);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function hasChannel(ChannelInterface $channel)
    {
        return $this->channels->contains($channel);
    }

    /**
     * {@inheritdoc}
     */
    public function removeChannel(ChannelInterface $channel)
    {
        $this->channels->removeElement($channel);

        return $this;
    }

    /**
     * @return Channel|null
     */
    public function getPrimaryChannel()
    {
        if (null === $this->primaryChannel && $this->channels->count()) {
            return $this->channels->first();
        }

        return $this->primaryChannel;
    }

    /**
     * @return $this
     */
    public function setPrimaryChannel(ChannelInterface $primaryChannel = null)
    {
        $this->primaryChannel = $primaryChannel;

        return $this;
    }

    /**
     * @return Embedded\CustomFields
     */
    public function getCustomFields()
    {
        if (null === $this->customFields) {
            $this->customFields = new Embedded\CustomFields();
        }

        return $this->customFields;
    }

    /**
     * @return $this
     */
    public function setCustomFields(RegistryInterface $customFields = null)
    {
        if (null !== $customFields && !$customFields instanceof Embedded\CustomFields) {
            $customFields = new Embedded\CustomFields($customFields->toArray());
        }

        $this->customFields = $customFields;

        return $this;
    }

    /**
     * @return ?string
     */
    public function getCopyrightRestrictions(): ?string
    {
        return $this->copyrightRestrictions;
    }

    /**
     * @param ?string $copyrightRestrictions
     */
    public function setCopyrightRestrictions(?string $copyrightRestrictions): self
    {
        $this->copyrightRestrictions = $copyrightRestrictions;

        return $this;
    }

    public function getShortClassname(): string
    {
        return (new \ReflectionClass($this))->getShortName();
    }

    /**
     * updateUpdatedAtOnPreUpdate.
     */
    public function updateUpdatedAtOnPreUpdate()
    {
        $this->updatedAt = new \DateTime();
    }

    /**
     * updatePublishTimeOnPreUpdate.
     */
    public function updatePublishTimeOnPreUpdate()
    {
        if (!$this->publishTime instanceof PublishTime) {
            return;
        }

        if (!$this->publishTime->getStartDate() instanceof \DateTime) {
            $this->publishTime->setStartDate($this->createdAt);
        }

        if (!$this->publishTime->getEndDate() instanceof \DateTime) {
            $this->publishTime->setEndDate(new \DateTime(PublishTimeInterface::DATE_MAX));
        }
    }

    /**
     * @return string
     */
    abstract public function __toString();
}
