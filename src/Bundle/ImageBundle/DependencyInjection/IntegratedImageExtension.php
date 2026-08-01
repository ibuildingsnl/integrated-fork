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
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * @author Johnny Borg <johnny@e-active.nl>
 */
class IntegratedImageExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        // Load the bundle service configuration
        $loader->load('converter.xml');
        $loader->load('services.xml');
        $loader->load('twig.xml');
        $loader->load('validator.xml');
        $loader->load('liip.xml');
    }

    /**
     * Registers the base filter sets the image handling applies its runtime
     * configuration to. The application may override them by declaring filter
     * sets with the same name.
     */
    public function prepend(ContainerBuilder $container): void
    {
        if (!$container->hasExtension('liip_imagine')) {
            return;
        }

        $filterSets = [];

        // Used when a template renders an image without asking for a transformation.
        // The image is passed through unchanged, but LiipImagine still publishes it
        // to the public cache directory, which is what makes storage backed images
        // reachable: their local copy lives in the (non public) integrated cache dir.
        $filterSets['integrated_original'] = ['filters' => []];
        $filterSets['integrated_original_jpeg'] = ['format' => 'jpg', 'filters' => []];

        foreach (['inset', 'outbound'] as $mode) {
            $filters = ['thumbnail' => ['size' => [1000, 1000], 'mode' => $mode]];

            $filterSets['integrated_'.$mode] = ['filters' => $filters];
            $filterSets['integrated_'.$mode.'_jpeg'] = ['format' => 'jpg', 'filters' => $filters];
        }

        $container->prependExtensionConfig('liip_imagine', ['filter_sets' => $filterSets]);
    }
}
