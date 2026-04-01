<?php

declare(strict_types=1);

namespace Integrated\Bundle\PageBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class PageEditPersistenceContractTest extends TestCase
{
    public function testEditActionPersistsPageBeforeFlush(): void
    {
        $source = file_get_contents(__DIR__.'/../../Controller/PageController.php');

        $this->assertIsString($source);
        $matched = preg_match(
            '/public function edit\(.*?\)\: Response.*?if \(\$form->isValid\(\)\) \{(.*?)\}/s',
            $source,
            $matches
        );

        $this->assertSame(1, $matched);
        $this->assertStringContainsString('$this->documentManager->persist($page);', $matches[1]);
        $this->assertStringContainsString('$this->documentManager->flush();', $matches[1]);
    }
}
