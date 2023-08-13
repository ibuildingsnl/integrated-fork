<?php

namespace Integrated\Bundle\BrandBundle\Infrastructure;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\Persistence\ObjectRepository;
use Integrated\Bundle\BrandBundle\Document\Brand;
use Integrated\Bundle\BrandBundle\Document\BrandRepository;

class ODMBrandRepository implements BrandRepository
{
    public function __construct(
        private readonly DocumentManager $manager,
        private readonly ObjectRepository $doctrineRepo,
    ) {}

    public function all(): array
    {
        return $this->doctrineRepo->findBy([], ['profile.name' => 'asc']);
    }

    public function withId(string $id): ?Brand
    {
        return $this->doctrineRepo->find($id);
    }

    public function add(Brand $brand): void
    {
        $this->manager->persist($brand);
    }

    public function remove(Brand $brand): void
    {
        $this->manager->remove($brand);
    }
}
