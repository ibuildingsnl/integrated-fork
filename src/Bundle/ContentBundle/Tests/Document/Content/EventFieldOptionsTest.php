<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Document\Content;

use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Integrated\Bundle\ContentBundle\Document\Content\Event;
use Integrated\Common\Form\Mapping\Driver\AttributeDriver;
use Integrated\Common\Form\Mapping\MetadataFactory;
use Integrated\Common\Mapping\Registry\DriverRegistry;
use PHPUnit\Framework\TestCase;

final class EventFieldOptionsTest extends TestCase
{
    public function testEventDateFieldsDoNotRequireTimeInput(): void
    {
        $driverRegistry = new DriverRegistry();
        $driverRegistry->addDriver(new AttributeDriver(new class implements MappingDriver
        {
            public function loadMetadataForClass($className, $metadata): void
            {
            }

            public function getAllClassNames(): array
            {
                return [Event::class];
            }

            public function isTransient($className): bool
            {
                return false;
            }
        }));

        $metadata = (new MetadataFactory($driverRegistry))->getMetadata(Event::class);

        self::assertFalse($metadata->getField('startDate')->getOption('required'));
        self::assertFalse($metadata->getField('endDate')->getOption('required'));
    }
}
