<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class FlashDismissibleAlertsScriptTest extends TestCase
{
    public function testGlobalScriptObservesDynamicFlashMessagesForAutoDismiss(): void
    {
        $script = file_get_contents(__DIR__.'/../../Resources/assets/js/global.js');

        $this->assertIsString($script);
        $this->assertStringContainsString('new MutationObserver', $script);
        $this->assertStringContainsString("document.getElementById('flash-messages')", $script);
        $this->assertStringContainsString('dismissibleAlertBound', $script);
        $this->assertStringContainsString('DISMISSIBLE_ALERT_TIMEOUT_MS', $script);
    }

    public function testPersistedFlashMessagesDropRuntimeDismissBindingBeforeRestore(): void
    {
        $script = file_get_contents(__DIR__.'/../../Resources/assets/js/global.js');

        $this->assertIsString($script);
        $this->assertStringContainsString("removeAttribute('data-dismissible-alert-bound')", $script);
    }
}
