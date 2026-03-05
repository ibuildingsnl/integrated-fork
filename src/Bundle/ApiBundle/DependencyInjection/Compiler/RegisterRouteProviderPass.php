<?php

namespace Integrated\Bundle\ApiBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Reference;

class RegisterRouteProviderPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('integrated_api.routing.route_provider_registry')) {
            return;
        }

        $definition = $container->getDefinition('integrated_api.routing.route_provider_registry');

        foreach ($container->findTaggedServiceIds('integrated_api.route_provider') as $serviceId => $tags) {
            foreach ($tags as $tag) {
                $contract = $tag['contract'] ?? null;
                $version = $tag['version'] ?? null;

                if (!$contract || !$version) {
                    throw new LogicException(sprintf('The service "%s" tagged with "integrated_api.route_provider" must define contract and version.', $serviceId));
                }

                $definition->addMethodCall('addProvider', [new Reference($serviceId), $contract, $version]);
            }
        }
    }
}
