<?php

namespace Integrated\Bundle\TaxonomyBundle\Infrastructure;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\Persistence\ObjectRepository;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Document\Content\Taxonomy;
use Integrated\Bundle\TaxonomyBundle\Domain\TaxonomyRepository;

final class ODMTaxonomyRepository implements TaxonomyRepository
{
    public function __construct(
        private readonly DocumentManager $manager,
        private readonly ObjectRepository $doctrineRepo,
    ) {
    }

    public function all(): array
    {
        return $this->doctrineRepo->findAll();
    }

    public function byType(string $contentType): array
    {
        return $this->doctrineRepo->findBy(['contentType' => $contentType]);
    }

    public function add(Taxonomy $taxonomy): void
    {
        $this->manager->persist($taxonomy);
    }

    public function countUsages(Taxonomy $taxonomy): int
    {
        return $this->manager->createQueryBuilder(Content::class)
            ->field('relations.references.$id')
            ->equals($taxonomy->getId())
            ->count()
            ->hydrate(false)
            ->getQuery()
            ->execute();
    }
}
