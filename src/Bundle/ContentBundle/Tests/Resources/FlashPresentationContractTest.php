<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

class FlashPresentationContractTest extends TestCase
{
    public function testAlertStylesUseStandardizedFixedStackPosition(): void
    {
        $styles = file_get_contents(__DIR__.'/../../Resources/assets/sass/components/_alert.scss');

        $this->assertIsString($styles);
        $this->assertStringContainsString('#flash-messages {', $styles);
        $this->assertStringContainsString('position: fixed;', $styles);
        $this->assertStringContainsString('right: 1rem;', $styles);
        $this->assertStringContainsString('display: flex;', $styles);
        $this->assertStringContainsString('flex-direction: column;', $styles);
        $this->assertStringContainsString('gap: 0.75rem;', $styles);

        preg_match('/#flash-messages\\s*\\{([^}]*)\\}/s', $styles, $flashBlock);
        $this->assertNotEmpty($flashBlock);
        $this->assertStringContainsString('bottom: 1rem;', $flashBlock[1]);
        $this->assertStringNotContainsString('top:', $flashBlock[1]);

        preg_match('/\\.alert\\s*\\{([^}]*)\\}/s', $styles, $alertBlock);
        $this->assertNotEmpty($alertBlock);
        $this->assertStringNotContainsString('position: absolute;', $alertBlock[1]);
    }

    public function testFlashTemplatesRenderDismissibleCloseButtonContract(): void
    {
        $baseTemplate = file_get_contents(__DIR__.'/../../Resources/views/base.html.twig');
        $turboTemplate = file_get_contents(__DIR__.'/../../Resources/views/content/flash.turbo_stream.html.twig');

        $this->assertIsString($baseTemplate);
        $this->assertIsString($turboTemplate);

        $this->assertStringContainsString("component('integrated_admin:alert_box'", $baseTemplate);
        $this->assertStringContainsString('dismissible: true', $baseTemplate);
        $this->assertStringContainsString('bodyHtml: message|trans', $baseTemplate);

        $this->assertStringContainsString("component('integrated_admin:alert_box'", $turboTemplate);
        $this->assertStringContainsString('dismissible: true', $turboTemplate);
        $this->assertStringContainsString('bodyHtml: message|trans', $turboTemplate);
    }
}
