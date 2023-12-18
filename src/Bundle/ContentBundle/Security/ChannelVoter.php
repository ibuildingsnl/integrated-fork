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

use Integrated\Bundle\UserBundle\Model\UserInterface;
use Integrated\Common\Content\Channel\ChannelInterface;
use Integrated\Common\Security\PermissionInterface;
use Integrated\Common\Security\Resolver\PermissionResolver;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class ChannelVoter implements VoterInterface
{
    public function __construct(private array $permissions = [])
    {
        $this->permissions = $this->getOptionsResolver()->resolve($permissions);
    }

    protected function getOptionsResolver(): OptionsResolver
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'read' => PermissionInterface::READ,
            'write' => PermissionInterface::WRITE,
        ]);

        return $resolver;
    }

    public function supportsAttribute($attribute): bool
    {
        return \in_array($attribute, $this->permissions);
    }

    public function vote(TokenInterface $token, $channel, array $attributes): int
    {
        if (!$channel instanceof ChannelInterface) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        if (!\count($channel->getPermissions())) {
            return VoterInterface::ACCESS_GRANTED;
        }

        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        if (\in_array('ROLE_ADMIN', $user->getRoles())) {
            return VoterInterface::ACCESS_GRANTED;
        }

        $permissions = PermissionResolver::getPermissions($user, $channel->getPermissions());
        $result = VoterInterface::ACCESS_ABSTAIN;

        foreach ($attributes as $attribute) {
            if (!$this->supportsAttribute($attribute)) {
                continue;
            }

            $result = VoterInterface::ACCESS_GRANTED;

            if ($this->permissions['read'] == $attribute) {
                if (!$permissions['read']) {
                    return VoterInterface::ACCESS_DENIED;
                }
            }

            if ($this->permissions['write'] == $attribute) {
                if (!$permissions['write']) {
                    return VoterInterface::ACCESS_DENIED;
                }
            }
        }

        return $result;
    }
}
