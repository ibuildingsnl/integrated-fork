<?php

namespace Integrated\Bundle\SolrBundle\Tests\Command;

use Integrated\Bundle\SolrBundle\Command\IndexerRunCommand;
use Integrated\Bundle\SolrBundle\EventListener\DoctrineClearEventSubscriber;
use Integrated\Common\Queue\Provider\DBAL\QueueProvider;
use Integrated\Common\Solr\Indexer\Indexer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpKernel\KernelInterface;

class IndexerRunCommandTest extends TestCase
{
    public function testFullRunReturnsFailureWhenExternalProcessFails(): void
    {
        $indexer = $this->createMock(Indexer::class);
        $queueProvider = $this->createMock(QueueProvider::class);
        $clearSubscriber = $this->createMock(DoctrineClearEventSubscriber::class);

        $kernel = $this->createMock(KernelInterface::class);
        $kernel->method('getEnvironment')->willReturn('test');

        $command = new IndexerRunCommand(
            $indexer,
            $queueProvider,
            $clearSubscriber,
            $kernel,
            sys_get_temp_dir()
        );

        $tester = new CommandTester($command);
        $exitCode = $tester->execute(['--full' => true]);

        self::assertSame(Command::FAILURE, $exitCode);
    }
}
