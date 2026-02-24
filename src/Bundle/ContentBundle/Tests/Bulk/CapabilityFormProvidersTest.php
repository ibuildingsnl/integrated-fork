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

use Integrated\Bundle\ContentBundle\Bulk\BulkCapabilityResolver;
use Integrated\Bundle\ContentBundle\Bulk\CanonicalFormProvider;
use Integrated\Bundle\ContentBundle\Bulk\CanonicalHandler;
use Integrated\Bundle\ContentBundle\Bulk\FeaturedFormProvider;
use Integrated\Bundle\ContentBundle\Bulk\FeaturedHandler;
use Integrated\Bundle\ContentBundle\Bulk\PremiumFormProvider;
use Integrated\Bundle\ContentBundle\Bulk\PremiumHandler;
use Integrated\Bundle\ContentBundle\Bulk\PublishWindowFormProvider;
use Integrated\Bundle\ContentBundle\Bulk\PublishWindowHandler;
use Integrated\Bundle\ContentBundle\Document\Bulk\Action\CanonicalAction;
use Integrated\Bundle\ContentBundle\Document\Bulk\Action\FeaturedAction;
use Integrated\Bundle\ContentBundle\Document\Bulk\Action\PremiumAction;
use Integrated\Bundle\ContentBundle\Document\Bulk\Action\PublishWindowAction;
use Integrated\Bundle\ContentBundle\Form\Type\BulkActionCanonicalType;
use Integrated\Bundle\ContentBundle\Form\Type\BulkActionFeaturedType;
use Integrated\Bundle\ContentBundle\Form\Type\BulkActionPremiumType;
use Integrated\Bundle\ContentBundle\Form\Type\BulkActionPublishWindowType;
use Integrated\Common\Bulk\Form\Config;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CapabilityFormProvidersTest extends TestCase
{
    private BulkCapabilityResolver|MockObject $resolver;

    protected function setUp(): void
    {
        $this->resolver = $this->createMock(BulkCapabilityResolver::class);
    }

    public function testCanonicalProviderReturnsEmptyConfigWhenUnsupported(): void
    {
        $provider = new CanonicalFormProvider($this->resolver);

        $this->resolver->expects($this->once())
            ->method('supports')
            ->with([], 'canonical')
            ->willReturn(false);

        self::assertSame([], $provider->getConfig([]));
    }

    public function testCanonicalProviderReturnsConfigWhenSupported(): void
    {
        $provider = new CanonicalFormProvider($this->resolver);

        $this->resolver->expects($this->once())
            ->method('supports')
            ->with([], 'canonical')
            ->willReturn(true);

        $config = $provider->getConfig([]);

        self::assertCount(1, $config);
        self::assertInstanceOf(Config::class, $config[0]);
        self::assertSame(CanonicalHandler::class, $config[0]->getHandler());
        self::assertSame('canonical', $config[0]->getName());
        self::assertSame(BulkActionCanonicalType::class, $config[0]->getType());

        $action = (new CanonicalAction(CanonicalHandler::class))
            ->setSource('Reuters')
            ->setSourceUrl('https://example.org');
        self::assertTrue($config[0]->getMatcher()->match($action));
    }

    public function testFeaturedProviderReturnsConfigWhenSupported(): void
    {
        $provider = new FeaturedFormProvider($this->resolver);

        $this->resolver->expects($this->once())
            ->method('supports')
            ->with([], 'featured')
            ->willReturn(true);

        $config = $provider->getConfig([]);

        self::assertCount(1, $config);
        self::assertSame(FeaturedHandler::class, $config[0]->getHandler());
        self::assertSame('featured', $config[0]->getName());
        self::assertSame(BulkActionFeaturedType::class, $config[0]->getType());

        $action = (new FeaturedAction(FeaturedHandler::class))->setFeatured(true);
        self::assertTrue($config[0]->getMatcher()->match($action));
    }

    public function testPremiumProviderReturnsConfigWhenSupported(): void
    {
        $provider = new PremiumFormProvider($this->resolver);

        $this->resolver->expects($this->once())
            ->method('supports')
            ->with([], 'premium')
            ->willReturn(true);

        $config = $provider->getConfig([]);

        self::assertCount(1, $config);
        self::assertSame(PremiumHandler::class, $config[0]->getHandler());
        self::assertSame('premium', $config[0]->getName());
        self::assertSame(BulkActionPremiumType::class, $config[0]->getType());

        $action = (new PremiumAction(PremiumHandler::class))->setPremium(true);
        self::assertTrue($config[0]->getMatcher()->match($action));
    }

    public function testPublishWindowProviderReturnsConfigWhenSupported(): void
    {
        $provider = new PublishWindowFormProvider($this->resolver);

        $this->resolver->expects($this->once())
            ->method('supports')
            ->with([], 'publishTime')
            ->willReturn(true);

        $config = $provider->getConfig([]);

        self::assertCount(1, $config);
        self::assertSame(PublishWindowHandler::class, $config[0]->getHandler());
        self::assertSame('publishTime', $config[0]->getName());
        self::assertSame(BulkActionPublishWindowType::class, $config[0]->getType());

        $action = (new PublishWindowAction(PublishWindowHandler::class))
            ->setStartDate(new \DateTimeImmutable('2026-01-01 10:00:00'));
        self::assertTrue($config[0]->getMatcher()->match($action));
    }
}
