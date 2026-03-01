<?php

declare(strict_types=1);

namespace Integrated\Bundle\PageBundle\Tests\PageBuilder\V2;

use Integrated\Bundle\PageBundle\Command\PageBuilderV2MigrateCommand;
use PHPUnit\Framework\TestCase;

final class PageBuilderV2MigrateCommandTest extends TestCase
{
    public function testDefinesRequiredMigrationOptions(): void
    {
        $command = new PageBuilderV2MigrateCommand();
        $definition = $command->getDefinition();

        self::assertTrue($definition->hasOption('dry-run'));
        self::assertTrue($definition->hasOption('execute'));
        self::assertTrue($definition->hasOption('batch'));
        self::assertTrue($definition->hasOption('resume-from'));
    }
}

