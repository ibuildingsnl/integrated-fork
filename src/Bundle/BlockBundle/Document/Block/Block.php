<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\BlockBundle\Document\Block;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Integrated\Bundle\SlugBundle\Mapping\Attributes\Slug;
use Integrated\Bundle\UserBundle\Model\GroupInterface;
use Integrated\Common\Block\BlockInterface;
use Integrated\Common\Content\Embedded\RelationInterface;
use Integrated\Common\Form\Mapping\Attributes as Type;
use Symfony\Component\Validator\Constraints as Assert;

abstract class Block implements BlockInterface
{
    /**
     * @var string
     */
    #[Slug(fields: ['title'], separator: '_')]
    #[Type\Field(options: ['priority' => 980, 'required' => false])]
    protected $id;

    /**
     * @var string
     */
    #[Assert\NotBlank]
    #[Type\Field(options: ['priority' => 990])]
    protected $title;

    /**
     * @var string
     */
    #[Type\Field(options: ['required' => false])]
    protected $cssClass;

    /**
     * @var string
     */
    protected $layout;

    /**
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * @var \DateTime
     */
    protected $updatedAt;

    /**
     * @var \DateTime
     */
    #[Type\Field(type: 'Symfony\Component\Form\Extension\Core\Type\DateTimeType', options: [
        'attr' => [
            'data-set-date-text' => 'Set publication date',
        ],
        'html5' => true,
        'date_widget' => 'single_text',
        'time_widget' => 'single_text',
    ], location: 'custom')]
    protected $publishedAt;

    /**
     * @var \DateTime
     */
    #[Type\Field(type: 'Symfony\Component\Form\Extension\Core\Type\DateTimeType', options: [
        'placeholder' => ' ',
        'required' => false,
        'attr' => [
            'data-set-date-text' => 'Set depublication date',
        ],
        'html5' => true,
        'date_widget' => 'single_text',
        'time_widget' => 'single_text',
    ], location: 'custom')]
    protected $publishedUntil;

    /**
     * @var bool
     */
    #[Type\Field(type: 'Integrated\Bundle\ContentBundle\Form\Type\CheckboxSwitcherType', options: ['required' => false, 'attr' => ['align_with_widget' => true]], location: 'custom')]
    protected $disabled = false;

    /**
     * @var bool
     */
    protected $locked = false;

    /**
     * @var ArrayCollection
     */
    protected $relations;

    /**
     * @var array
     */
    protected $groups = [];

    /**
     * @param string|null $id
     */
    public function __construct($id = null)
    {
        if ($id) {
            $this->id = $id;
        }
        $this->createdAt = new \DateTime();
        $this->publishedAt = new \DateTime();
        $this->updatedAt = new \DateTime();
        $this->relations = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId(string $id)
    {
        $this->id = $id;
    }

    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    public function getCssClass()
    {
        return $this->cssClass;
    }

    /**
     * @param string $cssClass
     *
     * @return $this
     */
    public function setCssClass($cssClass)
    {
        $this->cssClass = $cssClass;

        return $this;
    }

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
     * @return \DateTime
     */
    public function getPublishedAt()
    {
        if ($this->publishedAt === null) {
            return new \DateTime();
        }

        return $this->publishedAt;
    }

    /**
     * @return $this
     */
    public function setPublishedAt(?\DateTime $publishedAt = null)
    {
        $this->publishedAt = $publishedAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getPublishedUntil()
    {
        return $this->publishedUntil;
    }

    /**
     * @return Block
     */
    public function setPublishedUntil(?\DateTime $publishedUntil = null)
    {
        $this->publishedUntil = $publishedUntil;

        return $this;
    }

    /**
     * @return bool
     */
    public function isPublished(?\DateTime $date = null)
    {
        if (null === $date) {
            $date = new \DateTime();
        }

        $published = true;

        if ($this->publishedAt && $this->publishedAt > $date) {
            $published = false;
        }

        if ($this->publishedUntil && $this->publishedUntil < $date) {
            $published = false;
        }

        return $published;
    }

    /**
     * @return bool
     */
    public function isDisabled()
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
        $this->disabled = (bool) $disabled;

        return $this;
    }

    /**
     * @return bool
     */
    public function isLocked()
    {
        return $this->locked;
    }

    /**
     * @param bool $locked
     *
     * @return Block
     */
    public function setLocked($locked)
    {
        $this->locked = (bool) $locked;

        return $this;
    }

    /**
     * @return int[]
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @return bool
     */
    public function hasGroup(int $group)
    {
        if (\in_array($group, $this->groups)) {
            return true;
        }

        return false;
    }

    /**
     * @param GroupInterface[] $groups
     *
     * @return bool
     */
    public function allowsGroupAccess(array $groups)
    {
        foreach ($groups as $group) {
            if ($this->hasGroup($group->getId())) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param int[]|GroupInterface[] $groups
     */
    public function setGroups(array $groups): void
    {
        $this->groups = [];

        foreach ($groups as $group) {
            if ($group instanceof GroupInterface) {
                $group = $group->getId();
            }
            $this->groups[] = (int) $group;
        }
    }

    public function getRelations(): Collection
    {
        // should always be instanceOf collection, but due to corrupt database can sometimes be null
        if (!$this->relations instanceof Collection) {
            $this->relations = new ArrayCollection();
        }

        return $this->relations;
    }

    public function setRelations(Collection $relations)
    {
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
            $exist->addReferences($relation->getReferences());
        } else {
            $this->getRelations()->add($relation);
        }

        return $this;
    }

    public function removeRelation(RelationInterface $relation)
    {
        $this->getRelations()->removeElement($relation);

        return $this;
    }

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
     * @return string
     */
    public function __toString()
    {
        return (string) ($this->id ?: $this->title ?: '');
    }
}
