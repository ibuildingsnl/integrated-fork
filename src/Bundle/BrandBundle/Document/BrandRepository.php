<?php

namespace Integrated\Bundle\BrandBundle\Document;

interface BrandRepository
{
    /** @return Brand[] */
    public function all(): array;

    public function find(string $id): ?Brand;

    public function add(Brand $brand): void;

    public function remove(Brand $brand): void;
}
