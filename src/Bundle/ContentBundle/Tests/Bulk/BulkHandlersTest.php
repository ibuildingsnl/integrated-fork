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

use Integrated\Bundle\ContentBundle\Bulk\CanonicalHandler;
use Integrated\Bundle\ContentBundle\Bulk\FeaturedHandler;
use Integrated\Bundle\ContentBundle\Bulk\PremiumHandler;
use Integrated\Bundle\ContentBundle\Bulk\PublishWindowHandler;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Common\Content\ContentInterface;
use PHPUnit\Framework\TestCase;

class BulkHandlersTest extends TestCase
{
    public function testCanonicalHandlerUpdatesSourceAndSourceUrl(): void
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

        $handler = new CanonicalHandler('Reuters', 'https://example.org/source');
        $handler->execute($content);

        self::assertSame('Reuters', $content->getSource());
        self::assertSame('https://example.org/source', $content->getSourceUrl());
    }

    public function testCanonicalHandlerSkipsUnsupportedContent(): void
    {
        $content = $this->createMock(ContentInterface::class);
        $handler = new CanonicalHandler('Reuters', 'https://example.org/source');

        $handler->execute($content);

        self::assertTrue(true);
    }

    public function testFeaturedHandlerUpdatesContent(): void
    {
        $content = new class() extends Content {
            public function __toString(): string
            {
                return '';
            }
        };

        $handler = new FeaturedHandler(true);
        $handler->execute($content);

        self::assertTrue((bool) $content->isFeatured());
    }

    public function testPremiumHandlerUpdatesContent(): void
    {
        $content = new class() extends Content {
            public function __toString(): string
            {
                return '';
            }
        };

        $handler = new PremiumHandler(true);
        $handler->execute($content);

        self::assertTrue((bool) $content->isPremium());
    }

    public function testPublishWindowHandlerUpdatesDates(): void
    {
        $start = new \DateTimeImmutable('2026-01-01 10:00:00');
        $end = new \DateTimeImmutable('2026-01-02 20:00:00');

        $content = new class() extends Content {
            public function __toString(): string
            {
                return '';
            }
        };

        $handler = new PublishWindowHandler($start, $end);
        $handler->execute($content);

        self::assertEquals($start, $content->getPublishTime()->getStartDate());
        self::assertEquals($end, $content->getPublishTime()->getEndDate());
    }

    public function testPublishWindowHandlerSkipsUnsupportedContent(): void
    {
        $content = $this->createMock(ContentInterface::class);
        $handler = new PublishWindowHandler();

        $handler->execute($content);

        self::assertTrue(true);
    }
}
