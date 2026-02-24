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

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectRepository;
use Integrated\Bundle\ContentBundle\Bulk\RelationAddHandler;
use Integrated\Bundle\ContentBundle\Bulk\RelationFormProvider;
use Integrated\Bundle\ContentBundle\Bulk\RelationRemoveHandler;
use Integrated\Bundle\ContentBundle\Document\Relation\Relation;
use Integrated\Bundle\ContentBundle\Document\ContentType\ContentType;
use Integrated\Bundle\TaxonomyBundle\Services\TaxonomyOverview;
use Integrated\Common\Content\ContentInterface;
use PHPUnit\Framework\TestCase;

class RelationFormProviderTest extends TestCase
{
    public function testUsesSourcesIdQueryAndBuildsRelationConfigs(): void
    {
        $relation = (new Relation())
            ->setId('tag')
            ->setName('Tag');

        $builder = new class([$relation]) {
            public string $fieldName = '';
            public array $inValues = [];

            private array $relations;

            public function __construct(array $relations)
            {
                $this->relations = $relations;
            }

            public function field(string $field): self
            {
                $this->fieldName = $field;

                return $this;
            }

            public function in(array $values): self
            {
                $this->inValues = $values;

                return $this;
            }

            public function getQuery(): object
            {
                return new class($this->relations) {
                    private array $relations;

                    public function __construct(array $relations)
                    {
                        $this->relations = $relations;
                    }

                    public function getIterator(): \Traversable
                    {
                        return new \ArrayIterator($this->relations);
                    }
                };
            }
        };

        $repository = new class($builder) implements ObjectRepository {
            private object $builder;

            public function __construct(object $builder)
            {
                $this->builder = $builder;
            }

            public function find($id): ?object
            {
                return null;
            }

            public function findAll(): array
            {
                return [];
            }

            public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
            {
                return [];
            }

            public function findOneBy(array $criteria): ?object
            {
                return null;
            }

            public function getClassName(): string
            {
                return Relation::class;
            }

            public function createQueryBuilder(string $alias): object
            {
                return $this->builder;
            }
        };

        $registry = $this->createMock(ManagerRegistry::class);
        $registry->expects($this->once())
            ->method('getRepository')
            ->with(Relation::class)
            ->willReturn($repository);

        $taxonomyOverview = $this->createMock(TaxonomyOverview::class);
        $taxonomyOverview->expects($this->never())->method('overviewFor');

        $provider = new RelationFormProvider($registry, $taxonomyOverview);
        $config = $provider->getConfig([
            $this->createContent('news'),
            $this->createContent('news'),
            $this->createContent('blog'),
        ]);

        self::assertSame('sources.$id', $builder->fieldName);
        self::assertSame(['news' => 'news', 'blog' => 'blog'], $builder->inValues);
        self::assertCount(2, $config);

        self::assertSame(RelationAddHandler::class, $config[0]->getHandler());
        self::assertSame('add_tag', $config[0]->getName());

        self::assertSame(RelationRemoveHandler::class, $config[1]->getHandler());
        self::assertSame('remove_tag', $config[1]->getName());
    }

    public function testAddsTaxonomyCategoriesForTaxonomyCategoryRelations(): void
    {
        $target = (new ContentType())
            ->setId('edition');

        $relation = (new Relation())
            ->setId('edition')
            ->setName('Editie')
            ->setType('taxonomy_category')
            ->setTargets([$target]);

        $builder = new class([$relation]) {
            public string $fieldName = '';
            public array $inValues = [];

            private array $relations;

            public function __construct(array $relations)
            {
                $this->relations = $relations;
            }

            public function field(string $field): self
            {
                $this->fieldName = $field;

                return $this;
            }

            public function in(array $values): self
            {
                $this->inValues = $values;

                return $this;
            }

            public function getQuery(): object
            {
                return new class($this->relations) {
                    private array $relations;

                    public function __construct(array $relations)
                    {
                        $this->relations = $relations;
                    }

                    public function getIterator(): \Traversable
                    {
                        return new \ArrayIterator($this->relations);
                    }
                };
            }
        };

        $repository = new class($builder) implements ObjectRepository {
            private object $builder;

            public function __construct(object $builder)
            {
                $this->builder = $builder;
            }

            public function find($id): ?object
            {
                return null;
            }

            public function findAll(): array
            {
                return [];
            }

            public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
            {
                return [];
            }

            public function findOneBy(array $criteria): ?object
            {
                return null;
            }

            public function getClassName(): string
            {
                return Relation::class;
            }

            public function createQueryBuilder(string $alias): object
            {
                return $this->builder;
            }
        };

        $registry = $this->createMock(ManagerRegistry::class);
        $registry->expects($this->once())
            ->method('getRepository')
            ->with(Relation::class)
            ->willReturn($repository);

        $taxonomyOverview = $this->createMock(TaxonomyOverview::class);
        $taxonomyOverview->expects($this->once())
            ->method('overviewFor')
            ->with('edition')
            ->willReturn(['taxonomy-item']);

        $provider = new RelationFormProvider($registry, $taxonomyOverview);
        $config = $provider->getConfig([$this->createContent('news')]);

        self::assertSame(['taxonomy-item'], $config[0]->getOptions()['taxonomy_categories']);
        self::assertSame(['taxonomy-item'], $config[1]->getOptions()['taxonomy_categories']);
    }

    private function createContent(string $contentType): ContentInterface
    {
        $content = $this->createMock(ContentInterface::class);
        $content->method('getContentType')->willReturn($contentType);

        return $content;
    }
}
