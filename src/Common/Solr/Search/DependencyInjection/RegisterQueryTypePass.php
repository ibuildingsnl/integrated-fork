<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Common\Solr\Search\DependencyInjection;

use Integrated\Common\Solr\Search\Type\TypeExtensionInterface;
use Symfony\Component\DependencyInjection\Argument\IteratorArgument;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PriorityTaggedServiceTrait;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Reference;

class RegisterQueryTypePass implements CompilerPassInterface
{
    use PriorityTaggedServiceTrait;

    private string $service;
    private string $typeTag;
    private string $typeExtensionTag;

    public function __construct(string $service, string $typeTag, string $typeExtensionTag)
    {
        $this->service = $service;
        $this->typeTag = $typeTag;
        $this->typeExtensionTag = $typeExtensionTag;
    }

    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition($this->service)) {
            return;
        }

        $definition = $container->getDefinition($this->service);
        $definition->replaceArgument(0, $this->processTypes($container));
        $definition->replaceArgument(1, $this->processTypeExtensions($container));
    }

    private function processTypes(ContainerBuilder $container): Reference
    {
        $map = [];

        foreach ($container->findTaggedServiceIds($this->typeTag, true) as $id => $tags) {
            $map[$container->getDefinition($id)->getClass()] = new Reference($id);
        }

        return ServiceLocatorTagPass::register($container, $map);
    }

    private function processTypeExtensions(ContainerBuilder $container): array
    {
        $extensions = [];

        foreach ($this->findAndSortTaggedServices($this->typeExtensionTag, $container) as $reference) {
            $class = $container->getParameterBag()->resolveValue($container->getDefinition((string) $reference)->getClass());

            if (!is_a($class, TypeExtensionInterface::class, true)) {
                throw new InvalidArgumentException(\sprintf('The service "%s" is not an instance of "%s".', $reference, TypeExtensionInterface::class));
            }

            $extends = false;

            foreach ($class::getTypes() as $type) {
                $extensions[$type][] = $reference;
                $extends = true;
            }

            if (!$extends) {
                throw new InvalidArgumentException(\sprintf('The getTypes() method for service "%s" does not return any types.', $reference));
            }
        }

        foreach ($extensions as $type => $services) {
            $extensions[$type] = new IteratorArgument($services);
        }

        return $extensions;
    }
}
