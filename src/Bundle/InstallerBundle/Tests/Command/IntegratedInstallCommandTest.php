<?php

namespace Integrated\Bundle\InstallerBundle\Tests\Command;

use Integrated\Bundle\InstallerBundle\Command\IntegratedInstallCommand;
use Integrated\Bundle\InstallerBundle\Test\BundleChecker;
use PHPUnit\Framework\TestCase;
use Solarium\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpKernel\KernelInterface;

class IntegratedInstallCommandTest extends TestCase
{
    public function testExecuteReturnsFailureWhenSubCommandFails(): void
    {
        $kernel = $this->createMock(KernelInterface::class);
        $kernel->method('getEnvironment')->willReturn('test');

        $command = new IntegratedInstallCommand(
            $this->createMock(Client::class),
            $this->createMock(BundleChecker::class),
            $kernel
        );

        $reflection = new \ReflectionProperty($command, 'php');
        $reflection->setAccessible(true);
        $reflection->setValue($command, '/definitely/not/a/php-binary');

        $exitCode = $command->execute(
            new ArrayInput(['--step' => ['cache']], $command->getDefinition()),
            new BufferedOutput()
        );

        self::assertSame(Command::FAILURE, $exitCode);
    }
}
