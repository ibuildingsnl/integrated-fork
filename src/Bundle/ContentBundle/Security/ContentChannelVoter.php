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
use Integrated\Common\Content\ChannelableInterface;
use Integrated\Common\Security\PermissionInterface;
use Integrated\Common\Security\Permissions;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class ContentChannelVoter implements VoterInterface
{
    /** @var array<string, bool> */
    private array $channelDecisionCache = [];

    /**
     * @var AccessDecisionManagerInterface
     */
    private $decisionManager;

    /**
     * @var array
     */
    private $permissions;

    public function __construct(
        AccessDecisionManagerInterface $decisionManager,
        array $permissions = [],
    ) {
        $this->decisionManager = $decisionManager;
        $this->permissions = $this->getOptionsResolver()->resolve($permissions);
    }

    /**
     * @return OptionsResolver
     */
    private function getOptionsResolver()
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

    public function vote(TokenInterface $token, mixed $content, array $attributes, ?Vote $vote = null): int
    {
        if (!$content instanceof ChannelableInterface) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        if (\in_array('ROLE_ADMIN', $user->getRoles())) {
            return VoterInterface::ACCESS_GRANTED;
        }

        if (!\count($content->getChannels())) {
            // Give everyone access if no channels are added
            return VoterInterface::ACCESS_ABSTAIN;
        }

        $result = VoterInterface::ACCESS_ABSTAIN;

        foreach ($attributes as $attribute) {
            if (!$this->supportsAttribute($attribute)) {
                continue;
            }

            $result = $attribute === $this->permissions['view'] ? VoterInterface::ACCESS_DENIED : VoterInterface::ACCESS_GRANTED;

            switch ($attribute) {
                case $this->permissions['view']:
                    foreach ($content->getChannels() as $channel) {
                        if ($this->decideChannelPermission($token, PermissionInterface::READ, $channel)) {
                            // Being in one of the group is enough to read
                            return VoterInterface::ACCESS_GRANTED;
                        }
                    }
                    break;

                case $this->permissions['create']:
                case $this->permissions['edit']:
                case $this->permissions['delete']:
                    foreach ($content->getChannels() as $channel) {
                        if (!$this->decideChannelPermission($token, PermissionInterface::WRITE, $channel)) {
                            // Need all channels to write
                            return VoterInterface::ACCESS_DENIED;
                        }
                    }
                    break;
            }
        }

        return $result;
    }

    private function decideChannelPermission(TokenInterface $token, string $permission, mixed $channel): bool
    {
        $cacheKey = $this->buildDecisionCacheKey($token, $permission, $channel);

        if (isset($this->channelDecisionCache[$cacheKey])) {
            return $this->channelDecisionCache[$cacheKey];
        }

        return $this->channelDecisionCache[$cacheKey] = $this->decisionManager->decide($token, [$permission], $channel);
    }

    private function buildDecisionCacheKey(TokenInterface $token, string $permission, mixed $channel): string
    {
        $channelId = \is_object($channel) && method_exists($channel, 'getId')
            ? trim((string) $channel->getId())
            : '';

        if ('' === $channelId) {
            $channelId = \is_object($channel) ? 'obj:'.spl_object_id($channel) : 'scalar:'.serialize($channel);
        }

        return spl_object_id($token).'|'.$permission.'|'.$channelId;
    }
}
