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

use Integrated\Common\Security\Permissions;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 */
class StaticPermissionVoter implements VoterInterface
{
    /**
     * @var array
     */
    private $permissions;

    /**
     * @var int
     */
    private $decision;

    /**
     * @param int $decision
     */
    public function __construct($decision = VoterInterface::ACCESS_GRANTED, array $permissions = [])
    {
        $this->permissions = $this->getOptionsResolver()->resolve($permissions);
        $this->decision = $decision;
    }

    /**
     * @return OptionsResolver
     */
    protected function getOptionsResolver()
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'view' => Permissions::VIEW,
            'create' => Permissions::CREATE,
            'edit' => Permissions::EDIT,
            'delete' => Permissions::DELETE,
        ]);

        return $resolver;
    }

    public function supportsAttribute($attribute)
    {
        return \in_array($attribute, $this->permissions);
    }

    public function supportsClass($class)
    {
        return true;
    }

    public function vote(TokenInterface $token, mixed $object, array $attributes, ?Vote $vote = null): int
    {
        foreach ($attributes as $attribute) {
            if ($this->supportsAttribute($attribute)) {
                return $this->decision;
            }
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }
}
