<?php

namespace Integrated\Bundle\ImageBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\HttpKernel\Kernel;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('gregwar_image');
        $rootNode = $treeBuilder->getRootNode();

        $webDirectory = '%kernel.project_dir%/public';

        if (Kernel::MAJOR_VERSION < 4) {
            $webDirectory = '%kernel.root_dir%/../web';
        }

        $rootNode
            ->children()
            ->scalarNode('cache_dir')->defaultValue('cache')->end()
            ->scalarNode('cache_dir_mode')->defaultNull()->example('0755')->end()
            ->booleanNode('throw_exception')->defaultFalse()->end()
            ->scalarNode('fallback_image')->defaultNull()->end()
            ->scalarNode('web_dir')->defaultValue($webDirectory)->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
