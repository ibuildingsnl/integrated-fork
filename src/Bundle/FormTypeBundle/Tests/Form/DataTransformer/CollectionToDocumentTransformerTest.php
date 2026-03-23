<?php

declare(strict_types=1);

namespace Integrated\Bundle\FormTypeBundle\Tests\Form\DataTransformer;

use Doctrine\Common\Collections\ArrayCollection;
use Integrated\Bundle\FormTypeBundle\Form\DataTransformer\CollectionToDocumentTransformer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Exception\TransformationFailedException;

final class CollectionToDocumentTransformerTest extends TestCase
{
    public function testTransformReturnsFirstObjectFromArrayCollection(): void
    {
        $document = (object) ['id' => 'first'];
        $transformer = new CollectionToDocumentTransformer();

        self::assertSame($document, $transformer->transform(new ArrayCollection([$document])));
    }

    public function testTransformReturnsFirstObjectFromArray(): void
    {
        $document = (object) ['id' => 'first'];
        $transformer = new CollectionToDocumentTransformer();

        self::assertSame($document, $transformer->transform([$document]));
    }

    public function testTransformReturnsNullForEmptyArray(): void
    {
        $transformer = new CollectionToDocumentTransformer();

        self::assertNull($transformer->transform([]));
    }

    public function testTransformRejectsScalarInsideArray(): void
    {
        $transformer = new CollectionToDocumentTransformer();

        $this->expectException(TransformationFailedException::class);
        $this->expectExceptionMessage('Expected an object in the array, "string" given');

        $transformer->transform(['not-an-object']);
    }
}
