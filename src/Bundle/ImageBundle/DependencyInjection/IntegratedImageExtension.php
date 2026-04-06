<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ImageBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * @author Johnny Borg <johnny@e-active.nl>
 */
class IntegratedImageExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('gregwar_image.cache_dir', $config['cache_dir']);
        $container->setParameter('gregwar_image.cache_dir_mode', $config['cache_dir_mode']);
        $container->setParameter('gregwar_image.throw_exception', $config['throw_exception']);
        $container->setParameter('gregwar_image.fallback_image', $config['fallback_image']);
        $container->setParameter('gregwar_image.web_dir', $config['web_dir']);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        // Load the bundle service configuration
        $loader->load('converter.xml');
        $loader->load('services.xml');
        $loader->load('twig.xml');
        $loader->load('image.xml');
        $loader->load('validator.xml');
    }
}
