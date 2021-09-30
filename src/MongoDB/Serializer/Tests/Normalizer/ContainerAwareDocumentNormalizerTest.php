<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\MongoDB\Serializer\Tests\Normalizer;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use ReflectionClass;
use Integrated\MongoDB\Serializer\Normalizer\ContainerAwareDocumentNormalizer;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 */
class ContainerAwareDocumentNormalizerTest extends TestCase
{
    /**
     * @var ContainerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $container;

    /**
     * @var ContainerAwareDocumentNormalizer
     */
    private $normalizer;

    protected function setUp(): void
    {
        $this->container = $this->createMock(ContainerInterface::class);
        $this->normalizer = new ContainerAwareDocumentNormalizer($this->container, 'the-service-id');
    }

    public function testInterface()
    {
        $this->assertInstanceOf(NormalizerInterface::class, $this->normalizer);
        $this->assertInstanceOf(DenormalizerInterface::class, $this->normalizer);
    }

    public function testGetDocumentManager()
    {
        $manger = $this->getMockBuilder(DocumentManager::class)->disableOriginalConstructor()->getMock();
        $this->container->expects($this->once())->method('get')->with($this->identicalTo('the-service-id'))->willReturn($manger);

        $class = new ReflectionClass($this->normalizer);

        $method = $class->getMethod('getDocumentManager');
        $method->setAccessible(true);

        $this->assertSame($manger, $method->invoke($this->normalizer));
        $this->assertSame($manger, $method->invoke($this->normalizer));
    }

    // I don't know what getClassMetadata does if a class can not be found ...
}
