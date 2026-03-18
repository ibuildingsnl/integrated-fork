<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class ContentTypeDepublicationRequirementSidebarOptionTest extends TestCase
{
    public function testSidebarContainsRequiredDepublicationDateOption(): void
    {
        $formType = file_get_contents(__DIR__.'/../../Form/Type/ContentTypeFormType.php');

        $this->assertIsString($formType);
        $this->assertStringContainsString("'options_required_depublication_date', CheckboxSwitcherType::class, [", $formType);
        $this->assertStringContainsString("'property_path' => 'options[required_depublication_date]'", $formType);
        $this->assertStringContainsString("'label' => 'Required depublication date'", $formType);
    }
}
