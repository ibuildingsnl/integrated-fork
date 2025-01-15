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

use Solarium\QueryType\Select\Query\Query;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ResolvedType implements ResolvedTypeInterface
{
    private TypeInterface $type;

    /**
     * @var TypeExtensionInterface[]
     */
    private array $extensions = [];

    private ?ResolvedTypeInterface $parent = null;
    private ?OptionsResolver $options = null;

    /**
     * @param TypeExtensionInterface[] $extensions
     */
    public function __construct(TypeInterface $type, array $extensions = [], ?ResolvedTypeInterface $parent = null)
    {
        $this->type = $type;
        $this->extensions = $extensions;
        $this->parent = $parent;
    }

    public function build(Query $query, array $options): void
    {
        if ($this->parent !== null) {
            $this->parent->build($query, $options);
        }

        $this->type->build($query, $options);

        foreach ($this->extensions as $extension) {
            $extension->build($query, $options);
        }
    }

    public function getOptions(): OptionsResolver
    {
        if (null === $this->options) {
            $this->options = $this->parent !== null ? clone $this->parent->getOptions() : new OptionsResolver();

            $this->type->configureOptions($this->options);

            foreach ($this->extensions as $extension) {
                $extension->configureOptions($this->options);
            }
        }

        return $this->options;
    }

    public function getType(): TypeInterface
    {
        return $this->type;
    }

    public function getTypeExtensions(): iterable
    {
        return $this->extensions;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }
}
