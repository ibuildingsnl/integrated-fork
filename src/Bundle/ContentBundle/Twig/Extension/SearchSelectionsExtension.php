<?php

namespace Integrated\Bundle\ContentBundle\Twig\Extension;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\ContentBundle\Document\SearchSelection\SearchSelection;
use Integrated\Bundle\ContentBundle\Document\SearchSelection\SearchSelectionRepository;
use Integrated\Bundle\UserBundle\Model\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class SearchSelectionsExtension extends AbstractExtension
{
    /**
     * @var SearchSelectionRepository
     */
    private $repository;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    public function __construct(DocumentManager $manager, TokenStorageInterface $tokenStorage)
    {
        $this->repository = $manager->getRepository(SearchSelection::class);
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @return array|TwigFunction[]
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('getSearchSelections', [$this, 'getSearchSelections']),
        ];
    }

    /**
     * @return array|mixed
     *
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function getSearchSelections()
    {
        if (!$user = $this->getUser()) {
            return [];
        }

        return $this->repository->findPublicByUserId($user->getId());
    }

    private function getUser(): ?User
    {
        if (!$token = $this->tokenStorage->getToken()) {
            return null;
        }

        $user = $token->getUser();

        if (!$user instanceof User) {
            return null;
        }

        return $user;
    }
}
