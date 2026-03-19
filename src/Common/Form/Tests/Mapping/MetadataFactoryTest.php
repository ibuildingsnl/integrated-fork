<?php

declare(strict_types=1);

namespace Integrated\Common\Form\Tests\Mapping;

use Integrated\Common\Form\Mapping\MetadataFactory;
use Integrated\Common\Mapping\Registry\DriverRegistry;
use PHPUnit\Framework\TestCase;

final class MetadataFactoryTest extends TestCase
{
    public function testGetMetadataReturnsNullForEmptyClass(): void
    {
        $factory = new TestableMetadataFactory(new DriverRegistry());

        self::assertNull($factory->getMetadata(''));
        /** @var string|null $class */
        $class = null;
        self::assertNull($factory->getMetadata($class));
        self::assertSame(0, $factory->loadCalls);
    }
}

final class TestableMetadataFactory extends MetadataFactory
{
    public int $loadCalls = 0;

    protected function loadMetadata($class)
    {
        ++$this->loadCalls;

        return parent::loadMetadata($class);
    }
}
