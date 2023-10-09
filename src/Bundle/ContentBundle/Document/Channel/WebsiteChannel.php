<?php

namespace Integrated\Bundle\ContentBundle\Document\Channel;

use Integrated\Bundle\ContentBundle\Document\Content\Image;

class WebsiteChannel extends Channel
{
    protected ?Image $favicon = null;
    protected ?string $secondarycolor = null;
    protected ?array $domains = [];
    protected ?string $primaryDomain = null;
    protected ?bool $primaryDomainRedirect = false;

    public function getType(): string
    {
        return 'website';
    }

    public function canBePrimary(): bool
    {
        return true;
    }

    public function getFavicon(): ?Image
    {
        return $this->favicon;
    }

    public function setFavicon(?Image $favicon): static
    {
        $this->favicon = $favicon;

        return $this;
    }

    public function getSecondaryColor(): ?string
    {
        return $this->secondarycolor;
    }

    public function setSecondaryColor(?string $secondarycolor): static
    {
        $this->secondarycolor = $secondarycolor;

        return $this;
    }

    public function setDomains(array $domains): static
    {
        $this->domains = $domains;

        return $this;
    }

    public function getDomains(): array
    {
        return $this->domains ?: [];
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
        return $this->primaryDomainRedirect ?: false;
    }

    public function setPrimaryDomainRedirect(bool $primaryDomainRedirect)
    {
        $this->primaryDomainRedirect = $primaryDomainRedirect;
    }

    public function defaultPrimaryDomain(): void
    {
        if (!$this->primaryDomain && $this->domains) {
            $this->primaryDomain = reset($this->domains);
        }
    }
}
