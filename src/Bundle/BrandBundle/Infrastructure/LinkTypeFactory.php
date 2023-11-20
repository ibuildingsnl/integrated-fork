<?php

namespace Integrated\Bundle\BrandBundle\Infrastructure;

use Integrated\Bundle\BrandBundle\Document\LinkType;

class LinkTypeFactory
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly bool $canBePrimary = true,
        public readonly ?string $connector = null,
        public readonly ?string $publicationSettingsForm = null,
    ) {
    }

    public function create(): LinkType
    {
        return new LinkType(
            $this->id,
            $this->name,
            $this->canBePrimary,
            $this->connector,
            $this->publicationSettingsForm,
        );
    }
}
