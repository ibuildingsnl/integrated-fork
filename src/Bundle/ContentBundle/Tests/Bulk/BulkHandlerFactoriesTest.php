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

use Integrated\Bundle\ContentBundle\Bulk\CanonicalHandlerFactory;
use Integrated\Bundle\ContentBundle\Bulk\FeaturedHandlerFactory;
use Integrated\Bundle\ContentBundle\Bulk\PremiumHandlerFactory;
use Integrated\Bundle\ContentBundle\Bulk\PublishWindowHandlerFactory;
use Integrated\Bundle\ContentBundle\Bulk\WorkflowAssignHandler;
use Integrated\Bundle\ContentBundle\Bulk\WorkflowAssignHandlerFactory;
use Integrated\Bundle\ContentBundle\Bulk\WorkflowStateHandler;
use Integrated\Bundle\ContentBundle\Bulk\WorkflowStateHandlerFactory;
use Integrated\Bundle\UserBundle\Model\UserManagerInterface;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Common\ContentType\ResolverInterface;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\Exception\ExceptionInterface;

class BulkHandlerFactoriesTest extends TestCase
{
    public function testCanonicalFactoryCreatesHandler(): void
    {
        $factory = new CanonicalHandlerFactory();
        $handler = $factory->createHandler([
            'source' => 'Reuters',
            'sourceUrl' => 'https://example.org/source',
        ]);

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

            public function getSource(): ?string
            {
                return $this->source;
            }

            public function setSourceUrl(?string $sourceUrl): static
            {
                $this->sourceUrl = $sourceUrl;

                return $this;
            }

            public function getSourceUrl(): ?string
            {
                return $this->sourceUrl;
            }
        };

        $handler->execute($content);

        self::assertSame('Reuters', $content->getSource());
        self::assertSame('https://example.org/source', $content->getSourceUrl());
    }

    public function testCanonicalFactoryUsesNullSourceAndSourceUrlWhenOptionIsMissing(): void
    {
        $factory = new CanonicalHandlerFactory();
        $handler = $factory->createHandler([
            'source' => null,
            'sourceUrl' => null,
        ]);

        $content = new class() extends Content {
            private ?string $source = 'initial-source';
            private ?string $sourceUrl = 'initial';

            public function __toString(): string
            {
                return '';
            }

            public function setSource(?string $source): static
            {
                $this->source = $source;

                return $this;
            }

            public function getSource(): ?string
            {
                return $this->source;
            }

            public function setSourceUrl(?string $sourceUrl): static
            {
                $this->sourceUrl = $sourceUrl;

                return $this;
            }

            public function getSourceUrl(): ?string
            {
                return $this->sourceUrl;
            }
        };

        $handler->execute($content);

        self::assertNull($content->getSource());
        self::assertNull($content->getSourceUrl());
    }

    public function testFeaturedFactoryCreatesHandler(): void
    {
        $factory = new FeaturedHandlerFactory();
        $handler = $factory->createHandler(['featured' => true]);

        $content = new class() extends Content {
            public function __toString(): string
            {
                return '';
            }
        };
        $handler->execute($content);

        self::assertTrue((bool) $content->isFeatured());
    }

    public function testPremiumFactoryCreatesHandler(): void
    {
        $factory = new PremiumHandlerFactory();
        $handler = $factory->createHandler(['premium' => true]);

        $content = new class() extends Content {
            public function __toString(): string
            {
                return '';
            }
        };
        $handler->execute($content);

        self::assertTrue((bool) $content->isPremium());
    }

    public function testPublishWindowFactoryCreatesHandler(): void
    {
        $start = new \DateTimeImmutable('2026-01-01 08:00:00');
        $end = new \DateTimeImmutable('2026-01-03 12:00:00');

        $factory = new PublishWindowHandlerFactory();
        $handler = $factory->createHandler([
            'startDate' => $start,
            'endDate' => $end,
        ]);

        $content = new class() extends Content {
            public function __toString(): string
            {
                return '';
            }
        };
        $handler->execute($content);

        self::assertEquals($start, $content->getPublishTime()->getStartDate());
        self::assertEquals($end, $content->getPublishTime()->getEndDate());
    }

    public function testFeaturedFactoryThrowsOnInvalidType(): void
    {
        $this->expectException(ExceptionInterface::class);

        $factory = new FeaturedHandlerFactory();
        $factory->createHandler(['featured' => 1]);
    }

    public function testWorkflowStateFactoryCreatesHandler(): void
    {
        $factory = new WorkflowStateHandlerFactory(
            $this->createMock(EntityManagerInterface::class),
            $this->createMock(ResolverInterface::class)
        );

        $handler = $factory->createHandler(['state' => 'state-1']);

        self::assertInstanceOf(WorkflowStateHandler::class, $handler);
    }

    public function testWorkflowAssignFactoryCreatesHandler(): void
    {
        $factory = new WorkflowAssignHandlerFactory(
            $this->createMock(EntityManagerInterface::class),
            $this->createMock(ResolverInterface::class),
            $this->createMock(UserManagerInterface::class)
        );

        $handler = $factory->createHandler(['assigned' => 'user-1']);

        self::assertInstanceOf(WorkflowAssignHandler::class, $handler);
    }

    public function testWorkflowAssignFactoryAcceptsNullAssignment(): void
    {
        $factory = new WorkflowAssignHandlerFactory(
            $this->createMock(EntityManagerInterface::class),
            $this->createMock(ResolverInterface::class),
            $this->createMock(UserManagerInterface::class)
        );

        $handler = $factory->createHandler(['assigned' => null]);

        self::assertInstanceOf(WorkflowAssignHandler::class, $handler);
    }
}
