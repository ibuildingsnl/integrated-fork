<?php

namespace Integrated\Bundle\IQLBundle\Infrastructure;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

final class IQLExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
//        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));


    }
}
