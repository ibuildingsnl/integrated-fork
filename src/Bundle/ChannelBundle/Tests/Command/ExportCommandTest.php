<?php

namespace Integrated\Bundle\ChannelBundle\Tests\Command;

use Integrated\Bundle\ChannelBundle\Command\ExportCommand;
use Integrated\Common\Channel\Exporter\QueueExporterInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpKernel\KernelInterface;

class ExportCommandTest extends TestCase
{
    public function testFullModeReturnsFailureWhenExternalProcessFails(): void
    {
        $kernel = $this->createMock(KernelInterface::class);
        $kernel->method('getEnvironment')->willReturn('test');

        $command = new ExportCommand(
            $this->createMock(QueueExporterInterface::class),
            $kernel,
            $this->createMock(LoggerInterface::class),
            '/tmp',
        );

        $tester = new CommandTester($command);
        $exitCode = $tester->execute(['--full' => true]);

        self::assertSame(Command::FAILURE, $exitCode);
    }
}

