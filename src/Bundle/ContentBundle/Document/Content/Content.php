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
use Integrated\Bundle\ContentBundle\Document\Content\Embedded\Metadata;
use Integrated\Bundle\ContentBundle\Document\Content\Embedded\PublishTime;
use Integrated\Bundle\ContentBundle\Document\Content\Embedded\Relation;
use Integrated\Bundle\SlugBundle\Mapping\Attributes\Slug;
use Integrated\Common\Content\Channel\ChannelInterface;
use Integrated\Common\Content\ChannelableInterface;
use Integrated\Common\Content\ConnectableInterface;
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

abstract class Content implements ContentInterface, ExtensibleInterface, MetadataInterface, ChannelableInterface, PublishableInterface, ConnectableInterface, FeaturedInterface, PremiumInterface
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
     * @var ChannelInterface|null
     */
    protected $primaryChannel;

    #[Slug(fields: ['id'])]
    #[Type\Field(options: ['attr' => ['style' => 'sidebar']], location: 'sidebar')]
    protected ?string $slug = null;

    /**
     * @var string the type of the ContentType
     */
    protected $contentType;

    /**
     * @var Collection
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
     * @var int|null
     */
    protected $featuredExpiration;

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
        $this->disabled = false;
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

    public function getContentType()
    {
        return $this->contentType;
    }

    public function setContentType($contentType)
    {
        $this->contentType = $contentType;

        return $this;
    }

    public function getRelations()
    {
        return $this->getRelationCollection()->toArray();
    }

    public function setRelations(iterable $relations)
    {
        $this->getRelationCollection()->clear();
        $this->relations = new ArrayCollection();

        foreach ($relations as $relation) {
            if ($relation instanceof RelationInterface) {
                $this->addRelation($relation);
            }
        }

        return $this;
    }

    public function addRelation(RelationInterface $relation)
    {
        if ($exist = $this->getRelation($relation->getRelationId())) {
            if (!$exist instanceof Relation) {
                $new = new Relation();

                $new->setRelationId($exist->getRelationId());
                $new->setRelationType($exist->getRelationType());
                $new->addReferences($exist->getReferences());

                $this->getRelationCollection()->removeElement($exist);
                $this->getRelationCollection()->add($exist = $new);
            }

            $exist->addReferences($relation->getReferences());
        } else {
            $this->getRelationCollection()->add($relation);
        }

        return $this;
    }

    public function removeRelation(RelationInterface $relation)
    {
        $this->getRelationCollection()->removeElement($relation);

        return $this;
    }

    public function getRelation($relationId)
    {
        $relation = $this->getRelationCollection()->filter(function (RelationInterface $relation) use ($relationId) {
            return $relation->getRelationId() == $relationId;
        })->first();

        return $relation === false ? null : $relation;
    }

    /**
     * @return RelationInterface[]
     */
    public function getRelationsByRelationType($relationType)
    {
        return $this->getRelationCollection()->filter(function (RelationInterface $relation) use ($relationType) {
            return $relation->getRelationType() == $relationType;
        })->toArray();
    }

    /**
     * @return ContentInterface[]
     */
    public function getReferencesByRelationType($relationType)
    {
        $references = [];

        if ($relations = $this->getRelationsByRelationType($relationType)) {
            foreach ($relations as $relation) {
                $references = array_merge($references, $relation->getReferences());
            }
        }

        return $references;
    }

    /**
     * @return ContentInterface[]
     */
    public function getReferencesByRelationTypes(array $relationTypes)
    {
        $references = [];

        foreach ($relationTypes as $relationType) {
            $references = array_merge($references, $this->getReferencesByRelationType($relationType));
        }

        return $references;
    }

    /**
     * @return ContentInterface|null
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
     */
    public function getReferencesByRelationId($relationId, $published = true, ?ChannelInterface $channel = null): array
    {
        foreach ($this->getRelations() as $relation) {
            if ($relation instanceof RelationInterface
                && $relation->getRelationId() == $relationId
                && $references = $relation->getReferences()
            ) {
                return array_filter($references, function (ContentInterface $content) use ($published, $channel) {
                    return (!$published || !$content instanceof PublishableInterface || $content->isPublished())
                    && (!$channel || !$content instanceof ChannelableInterface || $content->hasChannel($channel));
                });
            }
        }

        return [];
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
            if (\count($references)) {
                return reset($references);
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

    public function getPublishTime(): PublishTimeInterface
    {
        return $this->publishTime;
    }

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

    public function isPublished($checkPublishTime = true): bool
    {
        $published = true;

        if ($checkPublishTime && $this->publishTime instanceof PublishTime) {
            $published = $this->publishTime->isPublished();
        }

        return $published && !$this->disabled;
    }

    public function isPremium(): ?bool
    {
        return $this->premium;
    }

    public function setPremium(bool $premium): static
    {
        $this->premium = $premium;

        return $this;
    }

    public function isFeatured(): ?bool
    {
        return $this->featured;
    }

    public function setFeatured(bool $featured): static
    {
        $this->featured = $featured;

        return $this;
    }

    public function getFeaturedExpiration(): ?int
    {
        return $this->featuredExpiration;
    }

    public function setFeaturedExpiration(?int $featuredExpiration): static
    {
        $this->featuredExpiration = $featuredExpiration;

        return $this;
    }

    public function isDisabled(): ?bool
    {
        return $this->disabled;
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

    public function getMetadata()
    {
        if (null === $this->metadata) {
            $this->metadata = new Metadata();
        }

        return $this->metadata;
    }

    public function setMetadata(?RegistryInterface $metadata = null)
    {
        if (null !== $metadata && !$metadata instanceof Metadata) {
            $metadata = new Metadata($metadata->toArray());
        }

        $this->metadata = $metadata;

        return $this;
    }

    public function setChannels(iterable $channels)
    {
        $this->getChannelCollection()->clear();
        $this->channels = new ArrayCollection();

        foreach ($channels as $channel) {
            $this->addChannel($channel); // type check
        }

        $this->primaryChannel = $this->resolvePrimaryChannel($this->primaryChannel);

        return $this;
    }

    public function getChannels()
    {
        return $this->getChannelCollection()->toArray();
    }

    public function addChannel(ChannelInterface $channel)
    {
        $channels = $this->getChannelCollection();

        if (!$channels->contains($channel)) {
            $channels->add($channel);
        }

        if (null === $this->primaryChannel) {
            $this->setPrimaryChannel($channel);
        }

        return $this;
    }

    public function hasChannel(ChannelInterface $channel)
    {
        $channels = $this->getChannelCollection();

        if ($channels->contains($channel)) {
            return true;
        }

        $channelId = $channel->getId();
        if (!\is_string($channelId) || '' === $channelId) {
            return false;
        }

        foreach ($channels as $existingChannel) {
            if ($existingChannel->getId() === $channelId) {
                return true;
            }
        }

        return false;
    }

    public function removeChannel(ChannelInterface $channel)
    {
        $this->getChannelCollection()->removeElement($channel);
        $this->primaryChannel = $this->resolvePrimaryChannel($this->primaryChannel);

        return $this;
    }

    public function removeChannels()
    {
        $channels = $this->getChannelCollection();

        foreach ($channels as $channel) {
            $channels->removeElement($channel);
        }

        $this->primaryChannel = null;

        return $this;
    }

    /**
     * @return ChannelInterface|null
     */
    public function getPrimaryChannel()
    {
        $channels = $this->getChannelCollection();

        if ((null === $this->primaryChannel && $channels->count())
            || (!$channels->contains($this->primaryChannel) && $channels->count())) {
            $firstChannel = $channels->first();

            return $firstChannel === false ? null : $firstChannel;
        }

        return $this->primaryChannel;
    }

    /**
     * @return $this
     */
    public function setPrimaryChannel(?ChannelInterface $primaryChannel = null)
    {
        $this->primaryChannel = $this->resolvePrimaryChannel($primaryChannel);

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
    public function setCustomFields(?RegistryInterface $customFields = null)
    {
        if (null !== $customFields && !$customFields instanceof Embedded\CustomFields) {
            $customFields = new Embedded\CustomFields($customFields->toArray());
        }

        $this->customFields = $customFields;

        return $this;
    }

    public function getCopyrightRestrictions(): ?string
    {
        return $this->copyrightRestrictions;
    }

    public function setCopyrightRestrictions(?string $copyrightRestrictions): self
    {
        $this->copyrightRestrictions = $copyrightRestrictions;

        return $this;
    }

    /**
     * @return Collection<int, ChannelInterface>
     */
    private function getChannelCollection(): Collection
    {
        if (null === $this->channels) {
            $this->channels = new ArrayCollection();
        }

        return $this->channels;
    }

    /**
     * @return Collection<int, RelationInterface>
     */
    private function getRelationCollection(): Collection
    {
        if (null === $this->relations) {
            $this->relations = new ArrayCollection();
        }

        return $this->relations;
    }

    private function resolvePrimaryChannel(?ChannelInterface $primaryChannel = null): ?ChannelInterface
    {
        $channels = $this->getChannelCollection();

        if (0 === $channels->count()) {
            return null;
        }

        if (null !== $primaryChannel) {
            foreach ($channels as $channel) {
                if ($channel === $primaryChannel) {
                    return $channel;
                }

                $primaryChannelId = $primaryChannel->getId();
                if (\is_string($primaryChannelId) && '' !== $primaryChannelId && $channel->getId() === $primaryChannelId) {
                    return $channel;
                }
            }
        }

        $firstChannel = $channels->first();

        return $firstChannel === false ? null : $firstChannel;
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
