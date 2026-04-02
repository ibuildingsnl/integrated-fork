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

use Integrated\Bundle\WorkflowBundle\Command\WorkerCommand;
use Integrated\Common\Queue\QueueInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class WorkerCommandTest extends TestCase
{
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
}
