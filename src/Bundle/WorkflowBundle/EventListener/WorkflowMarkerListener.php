<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\WorkflowBundle\EventListener;

use Integrated\Bundle\UserBundle\Model\User;
use Integrated\Bundle\UserBundle\Model\UserInterface;
use Solarium\Core\Event;
use Solarium\QueryType\Select\Query\Query;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;

/**
 * @author Jeroen van Leeuwen <jeroen@e-active.nl>
 */
class WorkflowMarkerListener implements EventSubscriberInterface
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var AuthorizationChecker
     */
    private $authorizationChecker;

    public function __construct(TokenStorageInterface $tokenStorage, AuthorizationChecker $authorizationChecker)
    {
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Event\Events::PRE_EXECUTE => 'preExecute',
        ];
    }

    public function preExecute(Event\PreExecute $event)
    {
        $query = $event->getQuery();

        if (!$query instanceof WorkflowMarkerInterface) {
            return;
        }

        if (!$query instanceof Query) {
            throw new \InvalidArgumentException(sprintf('$query must be of type %s', Query::class));
        }

        if ($this->authorizationChecker->isGranted('ROLE_ADMIN')) {
            // admin is always allowed to do everything
            return;
        }

        $user = null;
        if ($token = $this->tokenStorage->getToken()) {
            $user = $token->getUser();
        }

        $fq = $query->createFilterQuery('workflow');
        $fq
            ->addTag('workflow')
            ->addTag('security')
            ->setQuery('(*:* -security_workflow_read:[* TO *])');

        if (!$user instanceof UserInterface) {
            return;
        }

        $filterWorkflow = [];

        foreach ($user->getGroups() as $group) {
            $filterWorkflow[] = $group->getId();
        }

        // allow content with group access
        if ($filterWorkflow) {
            $fq->setQuery(
                $fq->getQuery().' OR security_workflow_read: ((%1%))',
                [implode(') OR (', $filterWorkflow)]
            );
        }

        // always allow access to assinged content
        $fq->setQuery($fq->getQuery().' OR facet_workflow_assigned_id: %1%', [$user->getId()]);

        if ($user instanceof User) {
            if ($person = $user->getRelation()) {
                $fq->setQuery($fq->getQuery().' OR author: %1%*', [$person->getId()]);
            }
        }
    }
}
