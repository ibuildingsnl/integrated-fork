<?php

declare(strict_types=1);

namespace Integrated\Bundle\BlockBundle\Tests\Security;

use Integrated\Bundle\BlockBundle\Document\Block\TextBlock;
use Integrated\Bundle\BlockBundle\Security\AllowedBlockClassProvider;
use Integrated\Common\Form\Mapping\MetadataFactoryInterface;
use Integrated\Common\Form\Mapping\MetadataInterface;
use PHPUnit\Framework\TestCase;

final class AllowedBlockClassProviderTest extends TestCase
{
    public function testIsAllowedRejectsEmptyClassName(): void
    {
        $metadataFactory = $this->createMock(MetadataFactoryInterface::class);
        $metadataFactory->expects(self::never())->method('getAllMetadata');

        $provider = new AllowedBlockClassProvider($metadataFactory);

        self::assertFalse($provider->isAllowed(''));
    }

    public function testIsAllowedRejectsNonExistingClass(): void
    {
        $metadataFactory = $this->createMock(MetadataFactoryInterface::class);
        $metadataFactory->expects(self::never())->method('getAllMetadata');

        $provider = new AllowedBlockClassProvider($metadataFactory);

        self::assertFalse($provider->isAllowed('Not\\Existing\\ClassName'));
    }

    public function testIsAllowedRejectsExistingNonBlockClass(): void
    {
        $metadataFactory = $this->createMock(MetadataFactoryInterface::class);
        $metadataFactory->expects(self::never())->method('getAllMetadata');

        $provider = new AllowedBlockClassProvider($metadataFactory);

        self::assertFalse($provider->isAllowed(\stdClass::class));
    }

    public function testIsAllowedReturnsTrueForRegisteredBlockClassAndCachesMetadataList(): void
    {
        $metadata = $this->createMock(MetadataInterface::class);
        $metadata->expects(self::once())
            ->method('getClass')
            ->willReturn(TextBlock::class);

        $metadataFactory = $this->createMock(MetadataFactoryInterface::class);
        $metadataFactory->expects(self::once())
            ->method('getAllMetadata')
            ->willReturn([$metadata]);

        $provider = new AllowedBlockClassProvider($metadataFactory);

        self::assertTrue($provider->isAllowed(TextBlock::class));
        self::assertTrue($provider->isAllowed('\\'.TextBlock::class));
    }

    public function testIsAllowedReturnsFalseForUnregisteredBlockClass(): void
    {
        $metadataFactory = $this->createMock(MetadataFactoryInterface::class);
        $metadataFactory->expects(self::once())
            ->method('getAllMetadata')
            ->willReturn([]);

        $provider = new AllowedBlockClassProvider($metadataFactory);

        self::assertFalse($provider->isAllowed(TextBlock::class));
    }
}
