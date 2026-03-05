<?php

namespace Integrated\Bundle\ApiBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Reference;

class RegisterEndpointPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('integrated_api.endpoint.registry')) {
            return;
        }

        $definition = $container->getDefinition('integrated_api.endpoint.registry');

        foreach ($container->findTaggedServiceIds('integrated_api.endpoint') as $serviceId => $tags) {
            foreach ($tags as $tag) {
                $contract = $tag['contract'] ?? null;
                $version = $tag['version'] ?? null;
                $resource = $tag['resource'] ?? null;
                $operation = $tag['operation'] ?? null;

                if (!$contract || !$version || !$resource || !$operation) {
                    throw new LogicException(sprintf('The service "%s" tagged with "integrated_api.endpoint" must define contract, version, resource and operation.', $serviceId));
                }

                $scopes = [];
                if (isset($tag['scopes']) && is_string($tag['scopes'])) {
                    $scopes = array_values(array_filter(array_unique(array_map('trim', explode(',', $tag['scopes'])))));
                }

                $definition->addMethodCall('register', [$contract, $version, $resource, $operation, new Reference($serviceId), $scopes]);
            }
        }
    }
}
