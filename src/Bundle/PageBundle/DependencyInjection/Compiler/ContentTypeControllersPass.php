<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\PageBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\LogicException;

class ContentTypeControllersPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('integrated_page.services.content_type_controller_manager')) {
            return;
        }

        $services = [];

        foreach ($container->findTaggedServiceIds('integrated_page.contenttype_controller') as $id => $tags) {
            $definition = $container->findDefinition($id);

            foreach ($tags as $attributes) {
                if (!($attributes['class'] ?? null)) {
                    throw new LogicException(sprintf('The tag integrated_page.contenttype_controller for service "%s" does not have the required class attribute set.', $id));
                }

                if ($services[$attributes['class']] ?? null) {
                    throw new LogicException(sprintf('There is more the one service tagged with integrated_page.contenttype_controller for the content class "%s".', $attributes['class']));
                }

                $services[$attributes['class']] = [
                    'serviceId' => $id,
                    'class' => $definition->getClass(),
                    'actions' => $this->getActions($attributes, $definition->getClass()),
                ];
            }
        }

        $definition = $container->findDefinition('integrated_page.services.content_type_controller_manager');
        $definition->setArgument(0, $services);
    }

    private function getActions(array $attributes, string $class): array
    {
        if ($attributes['actions'] ?? null) {
            return array_filter(array_unique(array_map('trim', explode(',', $attributes['actions']))));
        }

        $actions = [];

        foreach ((new \ReflectionClass($class))->getMethods() as $method) {
            if ($method->isPublic() && !$method->isConstructor() && $method->getDeclaringClass()->getName() === $class) {
                $actions[] = $method->getName();
            }
        }

        return $actions;
    }
}
