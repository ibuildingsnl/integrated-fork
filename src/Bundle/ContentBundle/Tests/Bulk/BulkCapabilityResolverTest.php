<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Tests\Bulk;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\Persistence\ObjectRepository;
use Integrated\Bundle\ContentBundle\Bulk\BulkCapabilityResolver;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Document\ContentType\ContentType;
use Integrated\Bundle\ContentBundle\Document\ContentType\Embedded\Field;
use Integrated\Common\Content\ContentInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BulkCapabilityResolverTest extends TestCase
{
    private DocumentManager|MockObject $documentManager;

    private ObjectRepository|MockObject $repository;

    protected function setUp(): void
    {
        $this->documentManager = $this->createMock(DocumentManager::class);
        $this->repository = $this->createMock(ObjectRepository::class);
    }

    public function testSupportsReturnsFalseForUnknownFeature(): void
    {
        $resolver = $this->createResolverWithMap([
            'article' => $this->createContentType('article', ['source', 'sourceUrl']),
        ]);

        $content = $this->createCanonicalContent('article');

        self::assertFalse($resolver->supports([$content], 'does_not_exist'));
    }

    public function testSupportsReturnsFalseForNonContentInterface(): void
    {
        $this->documentManager->expects($this->never())
            ->method('getRepository');

        $resolver = new BulkCapabilityResolver($this->documentManager);

        self::assertFalse($resolver->supports([new \stdClass()], 'workflow'));
    }

    public function testSupportsCanonicalWhenAllItemsSupportIt(): void
    {
        $resolver = $this->createResolverWithMap([
            'article' => $this->createContentType('article', ['source', 'sourceUrl']),
        ]);

        $first = $this->createCanonicalContent('article');
        $second = $this->createCanonicalContent('article');

        self::assertTrue($resolver->supports([$first, $second], 'canonical'));
    }

    public function testSupportsCanonicalUsesStrictAllPolicy(): void
    {
        $resolver = $this->createResolverWithMap([
            'article' => $this->createContentType('article', ['sourceUrl']),
        ]);

        $supported = $this->createCanonicalContent('article');
        $unsupported = $this->createContent('article');

        self::assertFalse($resolver->supports([$supported, $unsupported], 'canonical'));
    }

    public function testSupportsWorkflowUsesStrictAllPolicyByContentTypeOptions(): void
    {
        $resolver = $this->createResolverWithMap([
            'a' => $this->createContentType('a', [], ['workflow' => 'default']),
            'b' => $this->createContentType('b', []),
        ]);

        $first = $this->createContent('a');
        $second = $this->createContent('b');

        self::assertFalse($resolver->supports([$first, $second], 'workflow'));
    }

    public function testSupportsCachesContentTypeLookups(): void
    {
        $contentType = $this->createContentType('news', [], ['workflow' => 'default']);

        $this->documentManager->expects($this->once())
            ->method('getRepository')
            ->with(ContentType::class)
            ->willReturn($this->repository);

        $this->repository->expects($this->once())
            ->method('find')
            ->with('news')
            ->willReturn($contentType);

        $resolver = new BulkCapabilityResolver($this->documentManager);

        $first = $this->createContent('news');
        $second = $this->createContent('news');

        self::assertTrue($resolver->supports([$first, $second], 'workflow'));
    }

    private function createResolverWithMap(array $map): BulkCapabilityResolver
    {
        $this->documentManager->expects($this->any())
            ->method('getRepository')
            ->with(ContentType::class)
            ->willReturn($this->repository);

        $this->repository->expects($this->any())
            ->method('find')
            ->willReturnCallback(function (string $id) use ($map) {
                return $map[$id] ?? null;
            });

        return new BulkCapabilityResolver($this->documentManager);
    }

    private function createContentType(string $id, array $fields, array $options = []): ContentType
    {
        $contentType = new ContentType();
        $contentType->setId($id);
        $contentType->setName($id);
        $contentType->setClass(Content::class);
        $contentType->setOptions($options);

        $contentTypeFields = [];
        foreach ($fields as $name) {
            $field = new Field();
            $field->setName($name);
            $contentTypeFields[] = $field;
        }

        $contentType->setFields($contentTypeFields);

        return $contentType;
    }

    private function createContent(string $contentType): ContentInterface
    {
        $content = new class() extends Content {
            public function __toString(): string
            {
                return '';
            }
        };
        $content->setContentType($contentType);

        return $content;
    }

    private function createCanonicalContent(string $contentType): ContentInterface
    {
        $content = new class() extends Content {
            private ?string $source = null;
            private ?string $sourceUrl = null;

            public function __toString(): string
            {
                return '';
            }

            public function setSource(?string $source): static
            {
                $this->source = $source;

                return $this;
            }

            public function setSourceUrl(?string $sourceUrl): static
            {
                $this->sourceUrl = $sourceUrl;

                return $this;
            }
        };
        $content->setContentType($contentType);

        return $content;
    }
}
