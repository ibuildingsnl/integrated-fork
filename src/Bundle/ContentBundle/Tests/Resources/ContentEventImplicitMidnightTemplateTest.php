<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class ContentEventImplicitMidnightTemplateTest extends TestCase
{
    public function testEditTemplateHidesImplicitMidnightForEventFields(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/content/edit.html.twig');

        $this->assertIsString($template);
        $this->assertStringContainsString('hideImplicitEventMidnightTime', $template);
        $this->assertStringContainsString("if (timeField.value === '00:00')", $template);
        $this->assertStringContainsString("timeField.value = '';", $template);
        $this->assertStringContainsString("form.startDate is defined and form.startDate.time is defined", $template);
        $this->assertStringContainsString("form.endDate is defined and form.endDate.time is defined", $template);
    }

    public function testIframeTemplateHidesImplicitMidnightForEventFields(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/content/edit.iframe.html.twig');

        $this->assertIsString($template);
        $this->assertStringContainsString('hideImplicitEventMidnightTime', $template);
        $this->assertStringContainsString("if (timeField.value === '00:00')", $template);
        $this->assertStringContainsString("document.addEventListener('turbo:frame-load', hideImplicitEventMidnightTime);", $template);
    }
}
