<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Common\Solr\Search\Type\Provider;

use Integrated\Common\Solr\Exception\InvalidArgumentException;
use Integrated\Common\Solr\Search\Type\TypeInterface;
use Integrated\Common\Solr\Search\Type\TypeProviderInterface;
use Psr\Container\ContainerInterface;

class DependencyInjectionProvider implements TypeProviderInterface
{
    private ContainerInterface $container;
    private array $extensions;

    /**
     * @param iterable[] $extensions
     */
    public function __construct(ContainerInterface $container, array $extensions)
    {
        $this->container = $container;
        $this->extensions = $extensions;
    }

    public function hasType(string $name): bool
    {
        return $this->container->has($name);
    }

    public function getType(string $name): TypeInterface
    {
        if (!$this->container->has($name)) {
            throw new InvalidArgumentException(\sprintf('The query type "%s" is not registered in the service container.', $name));
        }

        return $this->container->get($name);
    }

    public function hasExtensions(string $name): bool
    {
        return isset($this->extensions[$name]);
    }

    public function getExtensions(string $name): iterable
    {
        $extensions = [];

        foreach ($this->extensions[$name] ?? [] as $extension) {
            $extensions[] = $extension;

            $types = [];
            foreach ($extension::getTypes() as $type) {
                $types[] = $type;
            }

            // validate the result of getTypes() to ensure it is consistent with the service definition
            if (!\in_array($name, $types, true)) {
                throw new InvalidArgumentException(\sprintf(
                    'The type "%s" specified for the type extension class "%s" does not match any of the actual types (["%s"]).',
                    $name,
                    $extension::class,
                    implode('", "', $types)
                ));
            }
        }

        return $extensions;
    }
}
