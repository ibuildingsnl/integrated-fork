<?php

declare(strict_types=1);

namespace Integrated\Bundle\BlockBundle\Tests\Security;

use Integrated\Bundle\BlockBundle\Document\Block\TextBlock;
use Integrated\Bundle\BlockBundle\Security\AllowedBlockClassInstantiator;
use Integrated\Bundle\BlockBundle\Security\AllowedBlockClassProvider;
use Integrated\Bundle\BlockBundle\Security\InvalidBlockClassException;
use Integrated\Common\Form\Mapping\MetadataFactoryInterface;
use Integrated\Common\Form\Mapping\MetadataInterface;
use PHPUnit\Framework\TestCase;

final class AllowedBlockClassInstantiatorTest extends TestCase
{
    public function testInstantiateReturnsNewBlockForAllowedClass(): void
    {
        $metadata = $this->createMock(MetadataInterface::class);
        $metadata->method('getClass')->willReturn(TextBlock::class);

        $metadataFactory = $this->createMock(MetadataFactoryInterface::class);
        $metadataFactory->method('getAllMetadata')->willReturn([$metadata]);

        $instantiator = new AllowedBlockClassInstantiator(new AllowedBlockClassProvider($metadataFactory));

        $block = $instantiator->instantiate(TextBlock::class);

        self::assertInstanceOf(TextBlock::class, $block);
    }

    public function testInstantiateRejectsUnregisteredClass(): void
    {
        $metadataFactory = $this->createMock(MetadataFactoryInterface::class);
        $metadataFactory->method('getAllMetadata')->willReturn([]);

        $instantiator = new AllowedBlockClassInstantiator(new AllowedBlockClassProvider($metadataFactory));

        $this->expectException(InvalidBlockClassException::class);

        $instantiator->instantiate(TextBlock::class);
    }
}
