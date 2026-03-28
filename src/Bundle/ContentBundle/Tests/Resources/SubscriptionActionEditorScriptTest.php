<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class SubscriptionActionEditorScriptTest extends TestCase
{
    public function testSourceResolvesMarkerClassesToOuterFormRows(): void
    {
        $source = file_get_contents(__DIR__.'/../../Resources/assets/js/edit.js');

        self::assertIsString($source);
        self::assertStringContainsString('function resolveSubscriptionActionRows(root, selector)', $source);
        self::assertStringContainsString("marker.closest('.form-item') || marker", $source);
        self::assertStringContainsString("row.classList.add('js-subscription-action-row--iframe');", $source);
        self::assertStringContainsString("row.classList.add('js-subscription-action-row--form');", $source);
        self::assertStringContainsString("resolveSubscriptionActionRows(root, '.js-subscription-action-row--iframe, .js-subscription-action-row--form')", $source);
        self::assertStringContainsString("row.style.display = shouldShow ? '' : 'none';", $source);
        self::assertStringContainsString("row.querySelectorAll('.js-subscription-action-row--iframe, .js-subscription-action-row--form').forEach(function(marker)", $source);
        self::assertStringContainsString("marker.style.display = shouldShow ? '' : 'none';", $source);
    }
}
