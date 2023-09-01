<?php

namespace Integrated\Bundle\BrandBundle\Document;

class LinkType
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly bool $canBePrimary = true,
        public readonly ?string $formType = null,
    ){}
}
