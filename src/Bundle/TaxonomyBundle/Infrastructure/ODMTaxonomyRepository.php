<?php

namespace Integrated\Bundle\TaxonomyBundle\Infrastructure;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\Persistence\ObjectRepository;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Document\Content\Taxonomy;
use Integrated\Bundle\TaxonomyBundle\Domain\TaxonomyRepositoryInterface;
use Integrated\Bundle\UserBundle\Model\GroupInterface;
use Integrated\Bundle\UserBundle\Model\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class ODMTaxonomyRepository implements TaxonomyRepositoryInterface
{
    public function __construct(
        private readonly DocumentManager $manager,
        private readonly ObjectRepository $doctrineRepo,
        private readonly TokenStorageInterface $tokenStorage,
    ) {
    }

    public function all(): array
    {
        return $this->doctrineRepo->findAll();
    }

    public function slice(string $contentType, int $offset, int $limit): array
    {
        /** @var User $user */
        $user = $this->tokenStorage->getToken()->getUser();
        $criteria = ['contentType' => $contentType];
        if (!in_array('ROLE_ADMIN', $user->getRoles())) {
            $criteria['channels'] = $user->getGroups();//array_map(fn(GroupInterface $group) => $group->getId(), $user->getGroups());
        }

        $qb = $this->manager->createQueryBuilder(Content::class);


        dd($criteria, $user->getGroups());
        return $this->doctrineRepo->findBy(
            $criteria,
            ['rank' => 'asc', 'title' => 'asc'],
            $limit,
            $offset,
        );
    }

    public function byId(string $id): ?Taxonomy
    {
        return $this->doctrineRepo->find($id);
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
