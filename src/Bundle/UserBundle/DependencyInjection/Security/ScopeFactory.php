<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\UserBundle\DependencyInjection\Security;

use Integrated\Bundle\UserBundle\Security\Firewall\ScopeListener;
use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\AuthenticatorFactoryInterface;
use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\FirewallListenerFactoryInterface;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ScopeFactory implements AuthenticatorFactoryInterface, FirewallListenerFactoryInterface
{
    public function createAuthenticator(ContainerBuilder $container, string $firewallName, array $config, string $userProviderId): string|array
    {
        return [];
    }

    public function createListeners(ContainerBuilder $container, string $firewallName, array $config): array
    {
        $listenerId = 'integrated_user.security.authentication.listener.scope.'.$firewallName;

        $container->setDefinition($listenerId, new ChildDefinition(ScopeListener::class))->replaceArgument(1, $firewallName);

        return [$listenerId];
    }

    public function getPosition()
    {
        return 'remember_me';
    }

    public function getPriority(): int
    {
        return -40;
    }

    public function getKey(): string
    {
        return 'scope';
    }

    public function addConfiguration(NodeDefinition $node)
    {
    }
}
