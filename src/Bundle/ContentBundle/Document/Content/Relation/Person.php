<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Document\Content\Relation;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Integrated\Bundle\ContentBundle\Document\Content\Embedded\Job;
use Integrated\Bundle\ContentBundle\Document\Content\Image;
use Integrated\Bundle\ContentBundle\Form\Type\Job\ContactPersonsType;
use Integrated\Bundle\ContentBundle\Form\Type\MediaGalleryType;
use Integrated\Bundle\SlugBundle\Mapping\Attributes\Slug;
use Integrated\Common\Content\Document\Storage\Embedded\StorageInterface;
use Integrated\Common\Form\Mapping\Attributes as Type;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

/**
 * Document type Relation\Person.
 *
 * @author Jeroen van Leeuwen <jeroen@e-active.nl>
 */
#[Type\Document('Person')]
class Person extends Relation
{
    /**
     * @var string
     */
    #[Type\Field(options: [
        'priority' => 990,
        'label' => 'First name',
        'attr' => ['style' => 'editor', 'state' => 'show'],
    ], location: 'editor')]
    protected $firstName;

    /**
     * @var string
     */
    #[Type\Field(options: [
        'priority' => 980,
        'label' => 'Last name',
        'attr' => ['style' => 'editor', 'state' => 'show'],
    ], location: 'editor')]
    protected $lastName;

    /**
     * @var string
     */
    #[Type\Field(type: ChoiceType::class, options: [
        'placeholder' => 'Select gender',
        'choices' => ['Male' => 'Male', 'Female' => 'Female'],
        'attr' => ['style' => 'sidebar', 'state' => 'show', 'icon' => 'female'],
    ], location: 'sidebar')]
    protected $gender;

    /**
     * @var string
     */
    #[Type\Field(options: ['attr' => ['style' => 'editor', 'state' => 'show']], location: 'sidebar')]
    protected $prefix;

    /**
     * @var string
     */
    #[Type\Field(options: ['attr' => ['style' => 'editor', 'state' => 'show']], location: 'sidebar')]
    protected $nickname;

    /**
     * @var string
     */
    #[Slug(fields: ['firstName', 'lastName'])]
    #[Type\Field(options: ['attr' => ['style' => 'sidebar', 'icon' => 'link']], location: 'sidebar')]
    protected $slug;

    /**
     * @var Collection Job[]
     */
    #[Type\Field(
        type: ContactPersonsType::class,
        options: ['attr' => ['style' => 'editor', 'state' => 'show']],
        location: 'editor'
    )]
    protected $jobs;

    /**
     * @var Image
     */
    #[Type\Field(type: MediaGalleryType::class, options: [
        'attr' => [
            'style' => 'sidebar',
            'icon' => 'media-image',
            'data-types' => '[{"type":"image","name":"Image"}]',
            'data-emptytext' => 'Select picture',
            'data-multiple' => 'true',
        ],
    ], location: 'sidebar')]
    protected $picture;

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->jobs = new ArrayCollection();
    }

    public function getTitle(): string
    {
        return $this->firstName.' '.$this->lastName;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(string $gender): static
    {
        $this->gender = $gender;

        return $this;
    }

    public function getPrefix(): ?string
    {
        return $this->prefix;
    }

    public function setPrefix(string $prefix): static
    {
        $this->prefix = $prefix;

        return $this;
    }

    public function getNickname(): ?string
    {
        return $this->nickname;
    }

    public function setNickname(string $nickname): static
    {
        $this->nickname = $nickname;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug($slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getJobs(): Collection
    {
        return $this->jobs;
    }

    public function setJobs(Collection $jobs): static
    {
        $this->jobs = $jobs;

        return $this;
    }

    public function addJob(mixed $job): static
    {
        if ($job instanceof Job) {
            if (!$this->jobs->contains($job)) {
                $this->jobs->add($job);
            }
        }

        return $this;
    }

    public function removeJob(mixed $job): bool
    {
        return $this->jobs->removeElement($job);
    }

    public function getPicture(): ?Image
    {
        return $this->picture;
    }

    public function setPicture(Image $picture = null): static
    {
        $this->picture = $picture;

        return $this;
    }

    public function getCover(): ?StorageInterface
    {
        if ($this->getPicture() instanceof Image) {
            if ($this->getPicture()->getFile() instanceof StorageInterface) {
                return $this->getPicture()->getFile();
            }
        }

        return null;
    }

    public function __toString(): string
    {
        return trim($this->firstName.' '.$this->lastName);
    }
}
