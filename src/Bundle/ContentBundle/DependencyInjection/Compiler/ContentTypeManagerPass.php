<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ContentTypeManagerPass implements CompilerPassInterface
{
    public const SERVICE_ID = 'integrated_content.resolver.xml_file.builder';

    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::SERVICE_ID)) {
            return;
        }

        foreach ($container->getParameter('kernel.bundles') as $bundle) {
            $reflection = new \ReflectionClass($bundle);

            if (is_file($file = \dirname($reflection->getFilename()).'/Resources/config/integrated/content_types.xml')) {
                $container->getDefinition(self::SERVICE_ID)->addMethodCall('registerFile', [$file]);
            }
        }

        if (\is_string($container->getParameter('kernel.project_dir'))) {
            if (is_file($file = $container->getParameter('kernel.project_dir').'/config/integrated/content_types.xml')) {
                $container->getDefinition(self::SERVICE_ID)->addMethodCall('registerFile', [$file]);
            }
        }
    }
}
