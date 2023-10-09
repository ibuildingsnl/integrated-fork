<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Document\Channel;

use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique as MongoDBUnique;
use Integrated\Bundle\ContentBundle\Document\Content\Image;
use Integrated\Bundle\SlugBundle\Mapping\Attributes\Slug;
use Integrated\Bundle\UserBundle\Model\Scope;
use Integrated\Common\Content\Channel\ChannelInterface;
use Integrated\Common\Security\PermissionTrait;
use Symfony\Component\Validator\Constraints as Assert;

/** @MongoDBUnique(fields="id") */
abstract class Channel implements ChannelInterface
{
    use PermissionTrait;

    #[Slug(fields: ['name'], separator: '_')]
    protected ?string $id = null;
    #[Assert\NotBlank]
    protected ?string $name = '';
    protected ?Image $logo = null;
    protected ?string $color = null;
    protected ?array $options = [];
    protected ?\DateTime $createdAt;
    protected ?bool $ipProtected = false;
    protected ?Scope $scopeInstance = null;
    protected ?string $scope = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function setId(string $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): static
    {
        $this->color = $color;

        return $this;
    }

    public function getLogo(): ?Image
    {
        return $this->logo;
    }

    public function setLogo(?Image $logo): static
    {
        $this->logo = $logo;

        return $this;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setOptions(array $options): static
    {
        $this->options = [];

        foreach ($options as $name => $value) {
            $this->setOption($name, $value);
        }

        return $this;
    }

    public function getOption(string $name): mixed
    {
        if (isset($this->options[$name])) {
            return $this->options[$name];
        }

        return null;
    }

    public function setOption(string $name, mixed $value = null): static
    {
        if ($value === null) {
            unset($this->options[$name]);
        } else {
            $this->options[$name] = $value;
        }

        return $this;
    }

    public function hasOption(string $name): bool
    {
        return isset($this->options[$name]);
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function isIpProtected(): bool
    {
        return $this->ipProtected;
    }

    public function setIpProtected(bool $protected = true): static
    {
        $this->ipProtected = $protected ? true : null;

        return $this;
    }

    public function getScope(): ?Scope
    {
        return $this->scopeInstance;
    }

    public function setScope(Scope $scope = null): static
    {
        $this->scopeInstance = $scope;
        $this->scope = $scope?->getId();

        return $this;
    }
}
