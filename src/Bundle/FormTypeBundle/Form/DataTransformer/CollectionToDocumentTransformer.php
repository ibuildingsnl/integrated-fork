<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\FormTypeBundle\Form\DataTransformer;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * @author Johan Liefers <johan@e-active.nl>
 */
class CollectionToDocumentTransformer implements DataTransformerInterface
{
    /**
     * @param Collection|null $value
     *
     * @return object|null
     *
     * @throws TransformationFailedException
     */
    public function transform($value): mixed
    {
        if (null === $value) {
            return null;
        }

        if ($value instanceof Collection) {
            return $this->extractDocument($value->toArray(), 'Collection');
        }

        if (\is_array($value)) {
            return $this->extractDocument($value, 'array');
        }

        throw new TransformationFailedException(\sprintf('Expected a Collection or array, "%s" given', \gettype($value)));
    }

    /**
     * @param object|null $value
     *
     * @return ArrayCollection
     */
    public function reverseTransform($value): mixed
    {
        if (null !== $value) {
            if (\is_object($value)) {
                return new ArrayCollection([$value]);
            }
            throw new TransformationFailedException(
                \sprintf('Expected an object, "%s" given', \gettype($value))
            );
        }

        return new ArrayCollection();
    }

    /**
     * @param array<mixed> $documents
     */
    private function extractDocument(array $documents, string $sourceType): mixed
    {
        if ([] === $documents) {
            return null;
        }

        $document = reset($documents);

        if (!\is_object($document)) {
            throw new TransformationFailedException(
                \sprintf('Expected an object in the %s, "%s" given', $sourceType, \gettype($document))
            );
        }

        return $document;
    }
}
