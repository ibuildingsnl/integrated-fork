<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Common\Solr\Search\Type;

use Integrated\Common\Solr\Exception\ExceptionInterface;
use Integrated\Common\Solr\Exception\InvalidArgumentException;
use Integrated\Common\Solr\Exception\LogicException;

class Registry implements RegistryInterface
{
    private array $providers;
    private ResolvedTypeFactoryInterface $factory;

    /**
     * @var ResolvedTypeInterface[]
     */
    private array $types = [];

    private array $checked = [];

    /**
     * @param TypeProviderInterface[] $providers
     */
    public function __construct(array $providers, ResolvedTypeFactoryInterface $factory)
    {
        $this->providers = $providers;
        $this->factory = $factory;
    }

    public function hasType(string $name): bool
    {
        if (isset($this->types[$name])) {
            return true;
        }

        try {
            $this->getType($name);
        } catch (ExceptionInterface $e) {
            return false;
        }

        return true;
    }

    public function getType(string $name): ResolvedTypeInterface
    {
        if (!isset($this->types[$name])) {
            $type = null;

            foreach ($this->providers as $provider) {
                if ($provider->hasType($name)) {
                    $type = $provider->getType($name);
                    break;
                }
            }

            if (!$type) {
                if (!class_exists($name)) {
                    throw new InvalidArgumentException(\sprintf('Could not load type "%s": class does not exist.', $name));
                }

                if (!is_subclass_of($name, TypeInterface::class)) {
                    throw new InvalidArgumentException(\sprintf('Could not load type "%s": class does not implement "%s".', $name, TypeInterface::class));
                }

                $type = new $name();
            }

            $this->types[$name] = $this->resolveType($type);
        }

        return $this->types[$name];
    }

    private function resolveType(TypeInterface $type): ResolvedTypeInterface
    {
        if (isset($this->checked[$type::class])) {
            throw new LogicException(\sprintf(
                'Circular reference detected for type "%s" (%s).',
                $type::class,
                implode(' > ', array_merge(array_keys($this->checked), [$type::class]))
            ));
        }

        $this->checked[$type::class] = true;

        try {
            $extensions = [];

            foreach ($this->providers as $provider) {
                $extensions[] = $provider->getExtensions($type::class);
            }

            $parent = $type->getParent();

            return $this->factory->create(
                $type,
                array_merge([], ...$extensions),
                $parent ? $this->getType($parent) : null
            );
        } finally {
            unset($this->checked[$type::class]);
        }
    }
}
