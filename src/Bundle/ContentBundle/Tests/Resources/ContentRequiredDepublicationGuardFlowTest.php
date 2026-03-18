<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class ContentRequiredDepublicationGuardFlowTest extends TestCase
{
    public function testControllerGuardsRequiredDepublicationDateBeforeSave(): void
    {
        $controller = file_get_contents(__DIR__.'/../../Controller/ContentController.php');

        $this->assertIsString($controller);
        $this->assertStringContainsString('guardRequiredDepublicationDate(', $controller);
        $this->assertStringContainsString("getOption('required_depublication_date')", $controller);
        $this->assertStringContainsString('Please set a depublication date.', $controller);
    }
}
