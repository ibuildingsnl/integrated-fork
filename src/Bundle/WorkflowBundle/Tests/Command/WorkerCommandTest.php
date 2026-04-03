<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\WorkflowBundle\Tests\Command;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\Persistence\ObjectRepository;
use Integrated\Bundle\ContentBundle\Command\ChannelDeleteCommand;
use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Integrated\Bundle\ContentBundle\Document\Channel\ChannelType;
use Integrated\Bundle\ContentBundle\Document\Content\Article;
use Integrated\Bundle\ContentBundle\Document\Content\Publication;
use Integrated\Bundle\ContentBundle\Services\ChannelDeletionProcessor;
use Integrated\Bundle\ContentBundle\Services\ChannelDeletionReport;
use Integrated\Bundle\ContentBundle\Services\ChannelDeletionSelfHealer;
use Integrated\Bundle\ContentBundle\Services\ContentReverseReferenceCleaner;
use Integrated\Bundle\ContentBundle\Services\SearchContentReferenced;
use Integrated\Bundle\WorkflowBundle\Command\WorkerCommand;
use Integrated\Common\Queue\QueueInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\EventDispatcher\EventDispatcher;

class WorkerCommandTest extends TestCase
{
    public function testChannelDeleteCommandReturnsSuccessForSuccessfulDeletion(): void
    {
        $channel = $this->createChannel('channel-success');
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->never())->method('error');

        $command = $this->createChannelDeleteCommand(
            $this->createSuccessProcessor($channel),
            $channel,
            $logger
        );

        $tester = new CommandTester($command);
        $exitCode = $tester->execute(['--channel-id' => 'channel-success']);

        $this->assertSame(Command::SUCCESS, $exitCode);
        $this->assertStringContainsString('status=success', $tester->getDisplay());
        $this->assertStringContainsString('Channel "channel-success" deleted.', $tester->getDisplay());
    }

    public function testChannelDeleteCommandReturnsSuccessWithWarningsAndVerboseSummary(): void
    {
        $channel = $this->createChannel('channel-warning');
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->never())->method('error');

        $command = $this->createChannelDeleteCommand(
            $this->createWarningProcessor($channel),
            $channel,
            $logger
        );

        $tester = new CommandTester($command);
        $exitCode = $tester->execute(
            [
                '--channel-id' => 'channel-warning',
                '--delete-referenced' => true,
            ],
            [
                'verbosity' => OutputInterface::VERBOSITY_VERBOSE,
            ]
        );

        $display = $tester->getDisplay();

        $this->assertSame(Command::SUCCESS, $exitCode);
        $this->assertStringContainsString('status=success_with_warnings', $display);
        $this->assertStringContainsString('warnings=1', $display);
        $this->assertStringContainsString(
            'warning_summary="delete '.Article::class.'(content-1): delete failed"',
            $display
        );
    }

    public function testChannelDeleteCommandReturnsFailureWhenProcessingThrows(): void
    {
        $channel = $this->createChannel('channel-exception');
        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects($this->once())
            ->method('error')
            ->with(
                'Background channel deletion failed',
                $this->callback(function (array $context): bool {
                    return $context['channel_id'] === 'channel-exception'
                        && $context['delete_referenced'] === false
                        && $context['exception'] instanceof \RuntimeException
                        && $context['exception']->getMessage() === 'Channel has related content/pages; deletion was not confirmed to remove related documents.';
                })
            );

        $command = $this->createChannelDeleteCommand(
            $this->createExceptionProcessor($channel),
            $channel,
            $logger
        );

        $tester = new CommandTester($command);
        $exitCode = $tester->execute(['--channel-id' => 'channel-exception']);

        $this->assertSame(Command::FAILURE, $exitCode);
        $this->assertStringContainsString(
            'Channel deletion failed: Channel has related content/pages; deletion was not confirmed to remove related documents.',
            $tester->getDisplay()
        );
    }

    public function testChannelDeleteCommandMapsFailedReportToFailureExitCode(): void
    {
        $report = new ChannelDeletionReport('channel-failed');

        $commandReflection = new \ReflectionClass(ChannelDeleteCommand::class);
        $formatReport = $commandReflection->getMethod('formatReportLine');
        $formatReport->setAccessible(true);
        $resolveExitCode = $commandReflection->getMethod('resolveExitCode');
        $resolveExitCode->setAccessible(true);

        $line = $formatReport->invoke(null, $report, false);
        $exitCode = $resolveExitCode->invoke(null, $report);

        $this->assertSame(Command::FAILURE, $exitCode);
        $this->assertStringContainsString('status=failed', $line);
        $this->assertStringContainsString('Channel "channel-failed" deletion failed.', $line);
        $this->assertStringNotContainsString('Channel "channel-failed" deleted.', $line);
    }

    public function testExecuteProcessesChannelDeleteMessages(): void
    {
        $queue = $this->createMock(QueueInterface::class);
        $input = $this->createMock(InputInterface::class);
        $output = $this->createMock(OutputInterface::class);

        $command = new class($queue, sys_get_temp_dir()) extends WorkerCommand {
            /** @var array<int, array{command: string, arguments: array<int, string>}> */
            public array $executedCommands = [];

            /**
             * @param array<string, mixed> $data
             */
            public function invokeHandleQueuePayload(array $data, InputInterface $input, OutputInterface $output): void
            {
                $this->handleQueuePayload($data, $input, $output);
            }

            /**
             * @param array<int, string> $arguments
             */
            protected function executeCommand(InputInterface $input, OutputInterface $output, string $command, array $arguments = []): void
            {
                $this->executedCommands[] = [
                    'command' => $command,
                    'arguments' => $arguments,
                ];
            }
        };

        $command->invokeHandleQueuePayload([
            'command' => 'channel-delete',
            'args' => [
                'channel_id' => 'bakkers-in-bedrijf',
                'delete_referenced' => true,
            ],
        ], $input, $output);

        $this->assertCount(1, $command->executedCommands);
        $this->assertSame('integrated:content:channel:delete', $command->executedCommands[0]['command']);
        $this->assertContains('--channel-id=bakkers-in-bedrijf', $command->executedCommands[0]['arguments']);
        $this->assertContains('--delete-referenced', $command->executedCommands[0]['arguments']);
    }

    public function testExecuteCommandRunsSubprocessOnce(): void
    {
        $tmp = sys_get_temp_dir().'/integrated-workflow-worker-'.bin2hex(random_bytes(8));
        $bin = $tmp.'/bin';
        $counterFile = $tmp.'/worker-command-invocations.log';

        mkdir($bin, 0777, true);

        $script = <<<'PHPFILE'
<?php
file_put_contents(__COUNTER_FILE__, implode(' ', $argv).PHP_EOL, FILE_APPEND);
PHPFILE;
        $script = str_replace('__COUNTER_FILE__', var_export($counterFile, true), $script);
        file_put_contents($bin.'/console', $script);

        $queue = $this->createMock(QueueInterface::class);
        $input = $this->createMock(InputInterface::class);
        $input->expects($this->once())->method('getOption')->with('env')->willReturn('test');
        $output = $this->createMock(OutputInterface::class);

        $command = new class($queue, $tmp) extends WorkerCommand {
            /**
             * @param array<int, string> $arguments
             */
            public function invokeExecuteCommand(InputInterface $input, OutputInterface $output, string $command, array $arguments = []): void
            {
                $this->executeCommand($input, $output, $command, $arguments);
            }
        };

        try {
            $command->invokeExecuteCommand($input, $output, 'workflow:index', ['--ignore', 'workflow-id']);

            $this->assertFileExists($counterFile);
            $lines = file($counterFile, \FILE_IGNORE_NEW_LINES | \FILE_SKIP_EMPTY_LINES);
            $this->assertIsArray($lines);
            $this->assertCount(1, $lines, 'Expected one subprocess execution per executeCommand call');
        } finally {
            @unlink($bin.'/console');
            @rmdir($bin);
            @unlink($counterFile);
            @rmdir($tmp);
        }
    }

    private function createChannelDeleteCommand(
        ChannelDeletionProcessor $processor,
        ?Channel $channel,
        LoggerInterface $logger
    ): ChannelDeleteCommand {
        $documentManager = $this->createMock(DocumentManager::class);
        $documentManager
            ->method('getRepository')
            ->with(Channel::class)
            ->willReturn(new class($channel) implements ObjectRepository {
                public function __construct(private readonly ?Channel $channel)
                {
                }

                public function find($id): ?object
                {
                    return $this->channel?->getId() === $id ? $this->channel : null;
                }

                public function findAll(): array
                {
                    return [];
                }

                public function findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null): array
                {
                    return [];
                }

                public function findOneBy(array $criteria): ?object
                {
                    return null;
                }

                public function getClassName(): string
                {
                    return Channel::class;
                }
            });

        return new ChannelDeleteCommand($documentManager, $processor, $logger);
    }

    private function createSuccessProcessor(Channel $channel): ChannelDeletionProcessor
    {
        $documentManager = $this->createDocumentManagerForProcessor($channel);
        $searchContentReferenced = $this->createMock(SearchContentReferenced::class);
        $searchContentReferenced
            ->expects($this->once())
            ->method('getReferencedDocuments')
            ->with($channel)
            ->willReturn([]);

        $cleanupSearch = $this->createMock(SearchContentReferenced::class);
        $cleanupSearch->expects($this->never())->method('getReferencedDocuments');

        return new ChannelDeletionProcessor(
            $documentManager,
            $searchContentReferenced,
            new ContentReverseReferenceCleaner($documentManager, $cleanupSearch),
            new EventDispatcher(),
            new ChannelDeletionSelfHealer()
        );
    }

    private function createWarningProcessor(Channel $channel): ChannelDeletionProcessor
    {
        $content = new Article();
        $content->setId('content-1');
        $content->addChannel($channel);

        $documentManager = $this->createDocumentManagerForProcessor($channel);
        $documentManager
            ->expects($this->exactly(2))
            ->method('remove')
            ->willReturnCallback(function (object $document) use ($content, $channel): void {
                if ($document === $content) {
                    throw new \RuntimeException('delete failed');
                }

                self::assertSame($channel, $document);
            });
        $documentManager->expects($this->once())->method('flush');
        $documentManager->expects($this->never())->method('persist');

        $searchContentReferenced = $this->createMock(SearchContentReferenced::class);
        $searchContentReferenced
            ->expects($this->once())
            ->method('getReferencedDocuments')
            ->with($channel)
            ->willReturn([$content]);

        $cleanupSearch = $this->createMock(SearchContentReferenced::class);
        $cleanupSearch
            ->expects($this->once())
            ->method('getReferencedDocuments')
            ->with($content)
            ->willReturn([]);

        return new ChannelDeletionProcessor(
            $documentManager,
            $searchContentReferenced,
            new ContentReverseReferenceCleaner($documentManager, $cleanupSearch),
            new EventDispatcher(),
            new ChannelDeletionSelfHealer()
        );
    }

    private function createExceptionProcessor(Channel $channel): ChannelDeletionProcessor
    {
        $content = new Article();
        $content->setId('content-2');
        $content->addChannel($channel);

        $documentManager = $this->createDocumentManagerForProcessor($channel);
        $documentManager->expects($this->never())->method('remove');
        $documentManager->expects($this->never())->method('flush');

        $searchContentReferenced = $this->createMock(SearchContentReferenced::class);
        $searchContentReferenced
            ->expects($this->once())
            ->method('getReferencedDocuments')
            ->with($channel)
            ->willReturn([$content]);

        $cleanupSearch = $this->createMock(SearchContentReferenced::class);
        $cleanupSearch->expects($this->never())->method('getReferencedDocuments');

        return new ChannelDeletionProcessor(
            $documentManager,
            $searchContentReferenced,
            new ContentReverseReferenceCleaner($documentManager, $cleanupSearch),
            new EventDispatcher(),
            new ChannelDeletionSelfHealer()
        );
    }

    /**
     * @param array<int, object> $publications
     * @param array<int, object> $brands
     */
    private function createDocumentManagerForProcessor(
        Channel $channel,
        array $publications = [],
        array $brands = []
    ): DocumentManager {
        $publicationQuery = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['toArray'])
            ->getMock();
        $publicationQuery
            ->method('toArray')
            ->willReturn($publications);

        $publicationQueryBuilder = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['field', 'equals', 'getQuery'])
            ->getMock();
        $publicationQueryBuilder
            ->method('field')
            ->with('channel.$id')
            ->willReturnSelf();
        $publicationQueryBuilder
            ->method('equals')
            ->with($channel->getId())
            ->willReturnSelf();
        $publicationQueryBuilder
            ->method('getQuery')
            ->willReturn($publicationQuery);

        $publicationRepository = new class($publicationQueryBuilder) implements ObjectRepository {
            public function __construct(private readonly object $queryBuilder)
            {
            }

            public function createQueryBuilder(): object
            {
                return $this->queryBuilder;
            }

            public function find($id): ?object
            {
                return null;
            }

            public function findAll(): array
            {
                return [];
            }

            public function findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null): array
            {
                return [];
            }

            public function findOneBy(array $criteria): ?object
            {
                return null;
            }

            public function getClassName(): string
            {
                return Publication::class;
            }
        };

        $brandRepository = new class($brands) implements ObjectRepository {
            /**
             * @param array<int, object> $brands
             */
            public function __construct(private readonly array $brands)
            {
            }

            public function find($id): ?object
            {
                return null;
            }

            public function findAll(): array
            {
                return $this->brands;
            }

            public function findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null): array
            {
                return [];
            }

            public function findOneBy(array $criteria): ?object
            {
                return null;
            }

            public function getClassName(): string
            {
                return \Integrated\Bundle\BrandBundle\Document\Brand::class;
            }
        };

        $documentManager = $this->createMock(DocumentManager::class);
        $documentManager
            ->method('getRepository')
            ->willReturnCallback(function (string $class) use ($publicationRepository, $brandRepository) {
                return match ($class) {
                    Publication::class => $publicationRepository,
                    \Integrated\Bundle\BrandBundle\Document\Brand::class => $brandRepository,
                    default => throw new \InvalidArgumentException('Unexpected repository request: '.$class),
                };
            });

        return $documentManager;
    }

    private function createChannel(string $id): Channel
    {
        $channel = new Channel();
        $channel->setId($id);
        $channel->setName($id);
        $channel->setType(new ChannelType('test-type', 'Test type'));

        return $channel;
    }
}
