<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Security;

use Doctrine\Persistence\ObjectRepository;
use Integrated\Bundle\UserBundle\Model\UserInterface;
use Integrated\Bundle\WorkflowBundle\Entity\Definition;
use Integrated\Common\ContentType\ContentTypeInterface;
use Integrated\Common\Security\PermissionInterface;
use Integrated\Common\Security\Resolver\PermissionResolver;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class ContentTypeVoter implements VoterInterface
{
    /**
     * @var ObjectRepository
     */
    private $repository;

    /**
     * @var array
     */
    private $permissions;

    public function __construct(ObjectRepository $repository, array $permissions = [])
    {
        $this->repository = $repository;
        $this->permissions = $this->getOptionsResolver()->resolve($permissions);
    }

    /**
     * @return OptionsResolver
     */
    private function getOptionsResolver()
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'read' => PermissionInterface::READ,
            'write' => PermissionInterface::WRITE,
        ]);

        return $resolver;
    }

    public function supportsAttribute($attribute)
    {
        return \in_array($attribute, $this->permissions);
    }

    public function vote(TokenInterface $token, mixed $contentType, array $attributes, mixed $vote = null): int
    {
        if (!$contentType instanceof ContentTypeInterface) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        if (\in_array('ROLE_ADMIN', $user->getRoles())) {
            return VoterInterface::ACCESS_GRANTED;
        }

        $permissionGroups = $contentType->getPermissions();

        if ($workflowId = $contentType->getOption('workflow')) {
            if ($workflow = $this->repository->find($workflowId)) {
                /** @var Definition $workflow */
                $state = $workflow->getDefault();

                if ($state instanceof Definition\State && \count($state->getPermissions())) {
                    // Workflow permissions overrules content type permissions
                    $permissionGroups = $state->getPermissions();
                }
            }
        }

        if (!\count($permissionGroups)) {
            // No permissions available
            return VoterInterface::ACCESS_GRANTED;
        }

        $permissions = PermissionResolver::getPermissions($user, $permissionGroups);
        $result = VoterInterface::ACCESS_ABSTAIN;

        foreach ($attributes as $attribute) {
            if (!$this->supportsAttribute($attribute)) {
                continue;
            }

            $result = VoterInterface::ACCESS_GRANTED;

            if ($this->permissions['read'] === $attribute) {
                if (!$permissions['read']) {
                    return VoterInterface::ACCESS_DENIED;
                }
            }

            if ($this->permissions['write'] === $attribute) {
                if (!$permissions['write']) {
                    return VoterInterface::ACCESS_DENIED;
                }
            }
        }

        return $result;
    }
}
