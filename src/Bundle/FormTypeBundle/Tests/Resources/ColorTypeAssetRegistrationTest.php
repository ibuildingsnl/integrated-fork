<?php

declare(strict_types=1);

namespace Integrated\Bundle\FormTypeBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class ColorTypeAssetRegistrationTest extends TestCase
{
    public function testColorTypeRegistersPickrStylesheetInBuildForm(): void
    {
        $type = file_get_contents(__DIR__.'/../../Form/Type/ColorType.php');

        $this->assertIsString($type);
        $this->assertStringContainsString(
            '$this->manager->add(\'bundles/integratedintegrated/pickr.css\');',
            $type
        );
    }

    public function testColorWidgetNoLongerRegistersStylesheetFromTwig(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/form/form_div_layout.html.twig');

        $this->assertIsString($template);
        $this->assertStringNotContainsString(
            "integrated_stylesheets mode='append' 'bundles/integratedintegrated/pickr.css'",
            $template
        );
        $this->assertStringContainsString(
            "integrated_javascripts mode='append' 'bundles/integratedintegrated/pickr.js'",
            $template
        );
    }
}
