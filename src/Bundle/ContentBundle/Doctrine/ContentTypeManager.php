<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Doctrine;

use Integrated\Common\ContentType\ContentTypeInterface;
use Integrated\Common\ContentType\Exception\InvalidArgumentException;
use Integrated\Common\ContentType\Exception\UnexpectedTypeException;
use Integrated\Common\ContentType\Iterator;
use Integrated\Common\ContentType\IteratorInterface;
use Integrated\Common\ContentType\Resolver\PriorityResolver;
use Integrated\Common\ContentType\ResolverInterface;

/**
 * @author Johan Liefers <johan@e-active.nl>
 */
class ContentTypeManager
{
    private ResolverInterface $resolver;

    /** @var ContentTypeInterface[] */
    private $contentTypes;

    public function __construct(ResolverInterface $resolver, $class)
    {
        $this->resolver = $resolver;

        if (!is_subclass_of($class, ContentTypeInterface::class)) {
            throw new InvalidArgumentException(sprintf('The class "%s" is not subclass of %s', $class, ContentTypeInterface::class));
        }
    }

    /** @return ContentTypeInterface[] */
    public function filterInstanceOf(string $className): array
    {
        $contentTypes = [];

        foreach ($this->getAll() as $contentType) {
            if (is_a($contentType->getClass(), $className, true)) {
                $contentTypes[] = $contentType;
            }
        }

        return $contentTypes;
    }

    /** @return IteratorInterface|ContentTypeInterface[] */
    public function getAll()
    {
        if (!$this->resolver instanceof PriorityResolver) {
            return $this->resolver->getTypes();
        }

        if (null === $this->contentTypes) {
            $contentTypes = [];

            foreach ($this->resolver->getResolvers() as $resolver) {
                $contentTypes = array_merge(iterator_to_array($resolver->getTypes()), $contentTypes);
            }

            sort($contentTypes);

            $this->contentTypes = new Iterator($contentTypes);
        }

        return $this->contentTypes;
    }

    /** @throws InvalidArgumentException if the content type can not be found */
    public function getType(string $type): ContentTypeInterface
    {
        return $this->resolver->getType($type);
    }

    public function hasType(string $type): bool
    {
        return $this->resolver->hasType($type);
    }
}
