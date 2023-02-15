<?php

namespace Integrated\Bundle\TaxonomyBundle\Tests\Features\Doubles;

use Integrated\Common\ContentType\ContentTypeInterface;
use Integrated\Common\ContentType\Exception\InvalidArgumentException;
use Integrated\Common\ContentType\Iterator;
use Integrated\Common\ContentType\ResolverInterface;

class MemoryTypeResolver implements ResolverInterface
{
    /**
     * @var ContentTypeInterface[]
     */
    private $types;

    /**
     * Constructor.
     *
     * @param ContentTypeInterface[] $types
     */
    public function __construct(array $types = [])
    {
        $this->types = $types;
    }

    /**
     * {@inheritdoc}
     */
    public function getType($type)
    {
        if (isset($this->types[$type])) {
            return $this->types[$type];
        }

        throw new InvalidArgumentException(sprintf('Could not resolve the content type based on the given type "%s"', $type));
    }

    /**
     * {@inheritdoc}
     */
    public function hasType($type)
    {
        return isset($this->types[$type]);
    }

    /**
     * {@inheritdoc}
     */
    public function getTypes()
    {
        return new Iterator($this->types);
    }

    public function addType(ContentTypeInterface $type)
    {
        $this->types[$type->getId()] = $type;
    }
}
