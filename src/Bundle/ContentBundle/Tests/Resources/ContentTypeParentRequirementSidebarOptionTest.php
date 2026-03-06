<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class ContentTypeParentRequirementSidebarOptionTest extends TestCase
{
    public function testTaxonomySidebarContainsParentRequirementOption(): void
    {
        $formType = file_get_contents(__DIR__.'/../../Form/Type/ContentTypeFormType.php');

        $this->assertIsString($formType);
        $this->assertStringContainsString('if ($metadata->isTypeOf(Taxonomy::class)) {', $formType);
        $this->assertStringContainsString('\'options_enforce_parent\', CheckboxSwitcherType::class, [', $formType);
        $this->assertStringContainsString('\'property_path\' => \'options[enforce_parent]\',', $formType);
        $this->assertStringContainsString('\'label\' => \'Enforce parent\',', $formType);
        $this->assertStringContainsString('\'attr\' => [\'location\' => \'sidebar\', \'style\' => \'sidebar\', \'state\' => \'show\', \'icon\' => \'tree\'],', $formType);
    }
}
