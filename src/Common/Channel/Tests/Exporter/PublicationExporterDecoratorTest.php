<?php

namespace Integrated\Common\Channel\Tests\Exporter;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\ContentBundle\Document\Content\Article;
use Integrated\Bundle\ContentBundle\Document\Content\Publication;
use Integrated\Bundle\ContentBundle\Document\Content\PublicationRepositoryInterface;
use Integrated\Common\Channel\Connector\Adapter\RegistryInterface;
use Integrated\Common\Channel\Connector\Config\ConfigInterface;
use Integrated\Common\Channel\Connector\Config\ResolverInterface;
use Integrated\Common\Channel\Connector\ExporterInterface as ConnectorExporterInterface;
use Integrated\Common\Channel\Exporter\ExportableInterface;
use Integrated\Common\Channel\Exporter\PublicationExporterDecorator;
use Integrated\Common\Content\Channel\ChannelInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PublicationExporterDecoratorTest extends TestCase
{
    private RegistryInterface&MockObject $registry;
    private ResolverInterface&MockObject $resolver;
    private DocumentManager&MockObject $manager;
    private PublicationRepositoryInterface&MockObject $repository;
    private ConnectorExporterInterface&MockObject $fallbackExporter;

    protected function setUp(): void
    {
        $this->registry = $this->createMock(RegistryInterface::class);
        $this->resolver = $this->createMock(ResolverInterface::class);
        $this->manager = $this->createMock(DocumentManager::class);
        $this->repository = $this->createMock(PublicationRepositoryInterface::class);
        $this->fallbackExporter = $this->createMock(ConnectorExporterInterface::class);
    }

    public function testExportUsesActiveConnectorForOlderPublication(): void
    {
        $channel = $this->createMock(ChannelInterface::class);
        $channel->method('getId')->willReturn('bakkers_in_bedrijf_linkedin');

        $content = new Article();
        $content->setDisabled(false);
        $content->getPublishTime()->setStartDate(new \DateTimeImmutable('-10 days'));
        $content->getPublishTime()->setEndDate(new \DateTimeImmutable('+10 years'));
        $content->addChannel($channel);

        $publication = new Publication(
            $content,
            $channel,
            $content->getPublishTime(),
            []
        );

        $this->repository->expects($this->once())
            ->method('getAvailable')
            ->with($this->identicalTo($content), $this->identicalTo($channel))
            ->willReturn([$publication]);

        $config = $this->createMock(ConfigInterface::class);
        $config->expects($this->once())
            ->method('getPublicationStartDate')
            ->willReturn(new \DateTime('yesterday'));
        $config->expects($this->once())
            ->method('getAdapter')
            ->willReturn('adapter-active');

        $this->resolver->expects($this->once())
            ->method('getConfigs')
            ->with($this->identicalTo($channel))
            ->willReturn(new \ArrayIterator([$config]));

        $connectorExporter = $this->createMock(ConnectorExporterInterface::class);
        $connectorExporter->expects($this->once())
            ->method('export')
            ->with(
                $this->identicalTo($content),
                $this->equalTo(ConnectorExporterInterface::STATE_ADD),
                $this->identicalTo($channel),
                $this->equalTo([])
            )
            ->willReturn(null);

        $adapter = $this->createMock(ExportableInterface::class);
        $adapter->expects($this->once())
            ->method('getExporter')
            ->with($this->identicalTo($config))
            ->willReturn($connectorExporter);

        $this->registry->expects($this->once())
            ->method('getAdapter')
            ->with($this->equalTo('adapter-active'))
            ->willReturn($adapter);

        $this->manager->expects($this->once())
            ->method('flush');
        $this->manager->expects($this->never())
            ->method('persist');

        $this->fallbackExporter->expects($this->never())
            ->method('export');

        $decorator = new PublicationExporterDecorator(
            $this->fallbackExporter,
            $this->registry,
            $this->resolver,
            $this->manager,
            $this->repository,
        );

        $decorator->export($content, ConnectorExporterInterface::STATE_ADD, $channel);
    }
}
