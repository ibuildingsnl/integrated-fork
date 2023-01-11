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

use Integrated\Bundle\ContentBundle\Document\Content\Taxonomy;
use Integrated\Bundle\UserBundle\Model\UserInterface;
use Integrated\Common\ContentType\ResolverInterface;
use Integrated\Common\Security\PermissionInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class MenuVoter implements VoterInterface
{
    /**
     * @var ResolverInterface
     */
    private $resolver;

    /**
     * @var array`
     */
    private $permissions;

    public function __construct(ResolverInterface $resolver, array $permissions = [])
    {
        $this->resolver = $resolver;
        $this->permissions = $this->getOptionsResolver()->resolve($permissions);
    }

    /**
     * @return OptionsResolver
     */
    protected function getOptionsResolver()
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'read' => PermissionInterface::READ,
            'write' => PermissionInterface::WRITE,
        ]);

        return $resolver;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsAttribute($attribute)
    {
        return \in_array($attribute, $this->permissions);
    }

    /**
     * {@inheritdoc}
     */
    public function vote(TokenInterface $token, $menu, array $attributes)
    {
        if (!$menu instanceof Taxonomy) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        // No channels assignmed means it is visisble for everyone
        if (0 === \count($menu->getChannels())) {
            return VoterInterface::ACCESS_GRANTED;
        }

        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        if (\in_array('ROLE_ADMIN', $user->getRoles())) {
            return VoterInterface::ACCESS_GRANTED;
        }

        $userChannels = $user->getGroups();

        $userChannelNames = array_map(fn ($item) => $item->getName(), $userChannels);

        $menuChannelNames = [];
        foreach ($menu->getChannels() as $item) {
            $menuChannelNames[] = $item->getName();
        }

        /* We`re checking the following:
         * $userChannelNames = ["myChannel", "beer", "water"]
         * $menuChannelNames = ["myChannel", "oranges", "apples"]
         * $overlap = true, because of myChannel
         *
         * $userChannelNames = ["myChannel", "beer", "water"]
         * $menuChannelNames = ["bananas", "oranges", "apples"]
         * $overlap = false
        */
        $overlap = \count(array_intersect($userChannelNames, $menuChannelNames)) > 0;
        if (true === $overlap) {
            return VoterInterface::ACCESS_GRANTED;
        }

        return VoterInterface::ACCESS_DENIED;
    }
}
