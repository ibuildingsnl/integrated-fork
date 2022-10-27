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
use Integrated\Common\Channel\ChannelInterface;
use Integrated\Common\ContentType\ResolverInterface;
use Integrated\Common\Security\PermissionInterface;
use Integrated\Common\Security\Resolver\PermissionResolver;
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

    /**
     * @param ResolverInterface $resolver
     * @param array             $permissions
     */
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

        //No channels assignmed means it is visisble for everyone
        if (0 === count($menu->getChannels())) {
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
        $userRoles = $user->getRoles();
        if (in_array("ROLE_ADMIN", $userRoles)) {
            dd("true");
        }

        $userChannelNames = array_map(fn ($item) => $item->getName(), $userChannels);

//        dd($userChannelNames);
        $menuChannelNames = [];
        foreach ($menu->getChannels() as $item) {
            $menuChannelNames[] = $item->getName();
        }

//        $arr1 = [1, 2, 2];
//        $arr2 = [3, 4, 5];
        $res = count(array_intersect($userChannelNames, $menuChannelNames)) > 0;
        if ($res === true) {
            return VoterInterface::ACCESS_GRANTED;
        } else {
            return VoterInterface::ACCESS_DENIED;
        }
//        dd(count($res) > 0);
        dump ("hi");
        dump($res);
//        $permissions = PermissionResolver::getPermissions($user, $menu->getChannels());
//        dd($permissions);
        $random = rand(0, 2) - 1;

        $result = VoterInterface::ACCESS_ABSTAIN;
        $result = $random;

//        foreach ($attributes as $attribute) {
//            if (!$this->supportsAttribute($attribute)) {
//                continue;
//            }
//
//            $result = VoterInterface::ACCESS_GRANTED;
//
//            if ($this->permissions['read'] == $attribute) {
//                if (!$permissions['read'] && !$permissions['write']) {
//                    return VoterInterface::ACCESS_DENIED;
//                }
//            }
//
//            if ($this->permissions['write'] == $attribute) {
//                if (!$permissions['write']) {
//                    return VoterInterface::ACCESS_DENIED;
//                }
//            }
//        }

        return $result;
    }
}
