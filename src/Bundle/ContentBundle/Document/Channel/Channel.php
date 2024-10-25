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
use Integrated\Bundle\SlugBundle\Mapping\Attributes\Slug;
use Integrated\Bundle\UserBundle\Model\Scope;
use Integrated\Common\Content\Channel\ChannelInterface;
use Integrated\Common\Security\PermissionTrait;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Channel document.
 *
 * @author Jeroen van Leeuwen <jeroen@e-active.nl>
 *
 * @MongoDBUnique(fields="id")
 */
class Channel implements ChannelInterface
{
    use PermissionTrait;

    /**
     * @var string
     */
    #[Slug(fields: ['name'], separator: '_')]
    protected $id;

    /**
     * @var string the name of the channel
     */
    #[Assert\NotBlank]
    protected $name;

    protected ?ChannelType $type = null;

    /**
     * @var array
     */
    protected $domains;

    /**
     * @var string
     */
    protected $primaryDomain;

    /**
     * @var bool
     */
    protected $primaryDomainRedirect;

    /**
     * @var mixed[]
     */
    protected $options = [];

    /**
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * @var bool
     */
    protected $ipProtected = false;

    /**
     * @var string
     */
    protected $language = 'nl';

    /**
     * @var Scope
     */
    protected $scopeInstance;

    /**
     * @var null
     */
    protected $scope;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getName(): ?string
    {
        return $this->name ?: '';
    }

    public function getType(): ?ChannelType
    {
        return $this->type;
    }

    public function setType(?ChannelType $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function setDomains(array $domains): void
    {
        $this->domains = $domains;
    }

    public function getDomains(): array
    {
        return $this->domains ?: [];
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setOptions(array $options): void
    {
        $this->options = [];

        foreach ($options as $name => $value) {
            $this->setOption($name, $value);
        }
    }

    public function getOption(string $name): mixed
    {
        return $this->options[$name] ?? null;
    }

    public function setOption(string $name, mixed $value = null): void
    {
        if ($value === null) {
            unset($this->options[$name]);
        } else {
            $this->options[$name] = $value;
        }
    }

    public function hasOption(string $name): bool
    {
        return isset($this->options[$name]);
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getPrimaryDomain(): ?string
    {
        return $this->primaryDomain;
    }

    public function setPrimaryDomain(string $primaryDomain): void
    {
        $this->primaryDomain = $primaryDomain;
    }

    public function getPrimaryDomainRedirect(): bool
    {
        return $this->primaryDomainRedirect;
    }

    public function setPrimaryDomainRedirect(bool $primaryDomainRedirect): void
    {
        $this->primaryDomainRedirect = $primaryDomainRedirect;
    }

    public function defaultPrimaryDomain(): void
    {
        if (!$this->primaryDomain && $this->domains) {
            $this->primaryDomain = reset($this->domains);
        }
    }

    public function isIpProtected(): bool
    {
        return (bool) $this->ipProtected;
    }

    public function setIpProtected(bool $protected): void
    {
        $this->ipProtected = $protected;
    }

    public function getLanguage(): ?string
    {
        return $this->language;
    }

    public function setLanguage(string $language): void
    {
        $this->language = $language;
    }

    public function getScope(): ?Scope
    {
        return $this->scopeInstance;
    }

    public function setScope(?Scope $scope): void
    {
        $this->scopeInstance = $scope;
        $this->scope = $scope ? $scope->getId() : null;
    }
}
