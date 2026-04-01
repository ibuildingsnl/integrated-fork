<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Security;

use PHPUnit\Framework\TestCase;

final class VoterSignatureCompatibilityContractTest extends TestCase
{
    public function testIntegratedVotersUseSymfonyCompatibleVoteSignature(): void
    {
        $paths = [
            __DIR__.'/../../Security/ChannelVoter.php',
            __DIR__.'/../../Security/ContentChannelVoter.php',
            __DIR__.'/../../Security/ContentTypeVoter.php',
            __DIR__.'/../../Security/MenuVoter.php',
            __DIR__.'/../../Security/StaticPermissionVoter.php',
            __DIR__.'/../../../WorkflowBundle/Security/WorkflowVoter.php',
        ];

        foreach ($paths as $path) {
            self::assertFileExists($path);

            $content = file_get_contents($path);
            self::assertIsString($content);
            self::assertStringContainsString('?Vote $vote = null', $content, basename($path));
        }
    }
}
