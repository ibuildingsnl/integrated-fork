<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Common\Mapping\Registry;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DriverRegistryPass implements CompilerPassInterface
{
    private string $service;

    private string $tag;

    public function __construct(string $service, string $tag)
    {
        $this->service = $service;
        $this->tag = $tag;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition($this->service)) {
            return;
        }

        $builder = $container->getDefinition($this->service);

        foreach ($container->findTaggedServiceIds($this->tag) as $service => $tags) {
            $service = $container->getDefinition($service);

            foreach ($tags as $tag) {
                $builder->addMethodCall('addDriver', [$service]);
            }
        }
    }
}
