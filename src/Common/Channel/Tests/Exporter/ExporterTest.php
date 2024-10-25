<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Common\Channel\Tests\Exporter;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\ContentBundle\Document\Content\Article;
use Integrated\Common\Channel\Connector\Adapter\RegistryInterface;
use Integrated\Common\Channel\Connector\AdapterInterface;
use Integrated\Common\Channel\Connector\Config\ConfigInterface;
use Integrated\Common\Channel\Connector\Config\OptionsInterface;
use Integrated\Common\Channel\Connector\Config\ResolverInterface;
use Integrated\Common\Channel\Connector\ExporterInterface;
use Integrated\Common\Channel\Exporter\ExportableInterface;
use Integrated\Common\Channel\Exporter\Exporter;
use Integrated\Common\Channel\Exporter\ExporterResponse;
use Integrated\Common\Channel\Tests\Exporter\Mock\NonContentDocument;
use Integrated\Common\Content\Channel\ChannelInterface;
use Integrated\Common\Content\ConnectableInterface;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 */
class ExporterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var string
     */
    public const TEST_STATE = 'TEST';

    /**
     * @var RegistryInterface|MockObject
     */
    private $registry;

    /**
     * @var ResolverInterface|MockObject
     */
    private $resolver;

    /**
     * @var DocumentManager|MockObject
     */
    private $dm;

    protected function setUp(): void
    {
        $this->registry = $this->createMock(RegistryInterface::class);
        $this->resolver = $this->createMock(ResolverInterface::class);
        $this->dm = $this->createMock(DocumentManager::class);
    }

    public function testInterface()
    {
        $this->assertInstanceOf(ExporterInterface::class, $this->getInstance());
    }

    public function testExport()
    {
        $content = new \stdClass();
        $channel = $this->getChannel('channel');

        $exporter1 = $this->getExporter();
        $exporter1->expects($this->exactly(2))
            ->method('export')
            ->with($this->identicalTo($content), $this->equalTo(self::TEST_STATE), $this->identicalTo($channel))
            ->willThrowException(new \Exception('i-will-be-caught-and-not-cause-any-troubles'));

        $exporter3 = $this->getExporter();
        $exporter3->expects($this->exactly(2))
            ->method('export')
            ->with($this->identicalTo($content), $this->equalTo(self::TEST_STATE), $this->identicalTo($channel));

        $option1 = $this->getOptions();
        $option3 = $this->getOptions();

        $config1 = $this->getConfig('adapter1', $option1);
        $config2 = $this->getConfig('adapter2');
        $config3 = $this->getConfig('adapter3', $option3);

        $this->resolver->expects($this->once())
            ->method('getConfigs')
            ->with($this->identicalTo($channel))
            ->willReturn(new \ArrayIterator([
                $config1,
                $config2,
                $config3,
            ]));

        $adapters = [
            $this->getAdapter($config1, $exporter1),
            $this->getAdapter(),
            $this->getAdapter($config3, $exporter3),
        ];

        // Set expectations using with() and a callback
        $this->registry->expects($this->exactly(3))
            ->method('getAdapter')
            ->willReturnCallback(function ($adapterName) use (&$adapters) {
                switch ($adapterName) {
                    case 'adapter1':
                        return array_shift($adapters); // return the first adapter
                    case 'adapter2':
                        return array_shift($adapters); // return the second adapter
                    case 'adapter3':
                        return array_shift($adapters); // return the third adapter
                    default:
                        $this->fail("Unexpected adapter name: $adapterName");
                }
            });

        $exporter = $this->getInstance();

        $exporter->export($content, self::TEST_STATE, $channel);
        $exporter->export($content, self::TEST_STATE, $channel); // check if the exporters are cached
    }

    public function testExportWithoutContentInterface()
    {
        $document = new NonContentDocument();
        $channel = $this->getChannel('channel');

        $exporter = $this->getPreparedExporter($document, $channel);
        $exporter->export($document, self::TEST_STATE, $channel);

        $this->assertCount(0, $document->getConnectors());
    }

    public function testSaveExport()
    {
        $article = new Article();
        $article->getPublishTime()->setStartDate(new \DateTime());
        $article->getPublishTime()->setEndDate(new \DateTime('now +10 years'));

        $channel = $this->getChannel('channel');

        $this->assertInstanceOf(ConnectableInterface::class, $article);

        $exporter = $this->getPreparedExporter($article, $channel);
        $exporter->export($article, self::TEST_STATE, $channel);

        $this->assertCount(1, $article->getConnectors());
    }

    public function testSaveExportWithInvalidDocument()
    {
        $document = new NonContentDocument();
        $channel = $this->getChannel('channel');

        $exporter = $this->getPreparedExporter($document, $channel);
        $exporter->export($document, self::TEST_STATE, $channel);

        $this->assertInstanceOf(ConnectableInterface::class, $document);

        $this->assertCount(0, $document->getConnectors());
    }

    public function testExportNoExporters()
    {
        $content = new \stdClass();
        $channel = $this->getChannel('channel');

        $this->resolver->expects($this->once())
            ->method('getConfigs')
            ->with($this->identicalTo($channel))
            ->willReturn(new \ArrayIterator([
                $this->getConfig('adapter1'),
                $this->getConfig('adapter2'),
                $this->getConfig('adapter3'),
            ]));

        // Set up the adapter return values in an array
        $adapters = [$this->getAdapter(), $this->getAdapter(), $this->getAdapter()];

        // Expect the getAdapter method to be called with specific arguments
        $this->registry->expects($this->exactly(3))
            ->method('getAdapter')
            ->willReturnCallback(function ($adapterName) use (&$adapters) {
                switch ($adapterName) {
                    case 'adapter1':
                        return array_shift($adapters); // return the first adapter
                    case 'adapter2':
                        return array_shift($adapters); // return the second adapter
                    case 'adapter3':
                        return array_shift($adapters); // return the third adapter
                    default:
                        $this->fail("Unexpected adapter name: $adapterName");
                }
            });

        $exporter = $this->getInstance();

        // Call export multiple times to test
        $exporter->export($content, self::TEST_STATE, $channel);
        $exporter->export($content, self::TEST_STATE, $channel); // check if the exporters are cached
    }

    public function testExportInvalidAdaptor()
    {
        $content = new \stdClass();
        $channel = $this->getChannel('channel');

        $this->resolver->expects($this->once())
            ->method('getConfigs')
            ->with($this->identicalTo($channel))
            ->willReturn(new \ArrayIterator([
                $this->getConfig('adapter1'),
                $this->getConfig('adapter2'),
                $this->getConfig('adapter3'),
            ]));
        /*
                $this->registry->expects($this->exactly(3))
                    ->method('getAdapter')
                    ->withConsecutive([$this->equalTo('adapter1')], [$this->equalTo('adapter2')], [$this->equalTo('adapter3')])
                    ->willReturnOnConsecutiveCalls(
                        $this->throwException(new \Exception('i-will-be-caught-and-not-cause-any-troubles')),
                        $this->getAdapter(),
                        $this->getAdapter()
                    );
        */
        $exporter = $this->getInstance();

        $exporter->export($content, self::TEST_STATE, $channel);
        $exporter->export($content, self::TEST_STATE, $channel); // check if the exporters are cached
    }

    public function testExportNoConfig()
    {
        $content = new \stdClass();
        $channel = $this->getChannel('channel');

        $this->resolver->expects($this->once())
            ->method('getConfigs')
            ->with($this->identicalTo($channel))
            ->willReturn(new \ArrayIterator([]));

        $this->registry->expects($this->never())
            ->method($this->anything());

        $exporter = $this->getInstance();

        $exporter->export($content, self::TEST_STATE, $channel);
        $exporter->export($content, self::TEST_STATE, $channel); // check if the exporters are cached
    }

    protected function getPreparedExporter($document, ChannelInterface $channel): Exporter
    {
        $exporterResponse = new ExporterResponse(1, 'test-exporter');
        $exporterResponse->setExternalId('external-id');

        $exporter1 = $this->getExporter();
        $exporter1
            ->method('export')
            ->with($this->identicalTo($document), $this->equalTo(self::TEST_STATE), $this->identicalTo($channel))
            ->willReturn($exporterResponse);

        $option1 = $this->getOptions();

        $config1 = $this->getConfig('adapter4', $option1);

        $this->resolver->expects($this->once())
            ->method('getConfigs')
            ->with($this->identicalTo($channel))
            ->willReturn(new \ArrayIterator([
                $config1,
            ]));

        $this->registry->expects($this->once())
            ->method('getAdapter')
            ->with($this->equalTo('adapter4'))
            ->willReturn(
                $this->getAdapter($config1, $exporter1)
            );

        return new Exporter($this->registry, $this->resolver, $this->dm);
    }

    protected function getInstance(): Exporter
    {
        return new Exporter($this->registry, $this->resolver, $this->dm);
    }

    /**
     * @param string $id
     *
     * @return ChannelInterface|MockObject
     */
    protected function getChannel($id)
    {
        $mock = $this->createMock(ChannelInterface::class);
        $mock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($id);

        return $mock;
    }

    /**
     * @return ExporterInterface|MockObject
     */
    protected function getExporter()
    {
        return $this->createMock(ExporterInterface::class);
    }

    /**
     * @param string $adaptor
     *
     * @return ConfigInterface|MockObject
     */
    protected function getConfig($adaptor, ?OptionsInterface $options = null)
    {
        $mock = $this->createMock(ConfigInterface::class);
        $mock->expects($this->once())
            ->method('getAdapter')
            ->willReturn($adaptor);

        if ($options) {
            $mock
                ->method('getOptions')
                ->willReturn($options);
        }

        return $mock;
    }

    /**
     * @return OptionsInterface|MockObject
     */
    protected function getOptions()
    {
        return $this->createMock(OptionsInterface::class);
    }

    /**
     * @return AdapterInterface|ExportableInterface|MockObject
     */
    protected function getAdapter(?ConfigInterface $config = null, ?ExporterInterface $exporter = null)
    {
        if ($config) {
            $mock = $this->createMock(ExportableInterface::class);
            $mock->expects($this->once())
                ->method('getExporter')
                ->with($this->identicalTo($config))
                ->willReturn($exporter);

            return $mock;
        }

        return $this->createMock(AdapterInterface::class);
    }
}
