<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\SolrBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 */
class RegisterTypePass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('integrated_solr.converter.type.registry_builder')) {
            return;
        }

        $definition = $container->getDefinition('integrated_solr.converter.type.registry_builder');

        foreach (array_keys($container->findTaggedServiceIds('integrated_solr.type')) as $service) {
            $definition->addMethodCall('addType', [new Reference($service)]);
        }

        foreach (array_keys($container->findTaggedServiceIds('integrated_solr.type_extension')) as $service) {
            $definition->addMethodCall('addTypeExtension', [new Reference($service)]);
        }
    }
}
