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
