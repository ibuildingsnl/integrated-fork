<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Document\Channel;

use Integrated\Bundle\ContentBundle\Document\Channel\ChannelType;
use PHPUnit\Framework\TestCase;

final class ChannelTypeTest extends TestCase
{
    public function testGetIconReturnsNullForLegacyHydratedDocumentWithoutIconField(): void
    {
        $reflection = new \ReflectionClass(ChannelType::class);
        $channelType = $reflection->newInstanceWithoutConstructor();

        $this->setProperty($channelType, 'id', 'website');
        $this->setProperty($channelType, 'name', 'Website');
        $this->setProperty($channelType, 'canBePrimary', true);
        $this->setProperty($channelType, 'canBeSetGlobally', true);

        self::assertNull($channelType->getIcon());
    }

    public function testGettersHandleLegacyHydratedDocumentWithoutRequiredFields(): void
    {
        $reflection = new \ReflectionClass(ChannelType::class);
        $channelType = $reflection->newInstanceWithoutConstructor();

        self::assertSame('', $channelType->getId());
        self::assertSame('', $channelType->getName());
        self::assertTrue($channelType->canBePrimary());
        self::assertTrue($channelType->canBeSetGlobally());
        self::assertNull($channelType->getConnector());
        self::assertNull($channelType->getPublicationSettingsForm());
    }

    private function setProperty(object $object, string $property, mixed $value): void
    {
        $reflection = new \ReflectionProperty($object, $property);
        $reflection->setValue($object, $value);
    }
}
