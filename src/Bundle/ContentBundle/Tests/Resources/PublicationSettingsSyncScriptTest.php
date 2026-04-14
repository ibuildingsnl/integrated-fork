<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class PublicationSettingsSyncScriptTest extends TestCase
{
    public function testEditScriptSyncsPublicationStartAndEndDatesAgainstMainPublishTime(): void
    {
        $source = file_get_contents(__DIR__.'/../../Resources/assets/js/edit.js');

        self::assertIsString($source);
        self::assertStringContainsString('function capturePublishTimeSnapshot(boundary)', $source);
        self::assertStringContainsString('function syncPublicationDateTimeFields(boundary)', $source);
        self::assertStringContainsString('publishTimeRoot.dataset[`prev${boundary}Date`]', $source);
        self::assertStringContainsString('publishTimeRoot.dataset[`prev${boundary}Time`]', $source);
        self::assertStringContainsString('input[name*="[settings][time][${boundary}][date]"]', $source);
        self::assertStringContainsString('input[name*="[settings][time][${boundary}][time]"]', $source);
        self::assertStringContainsString('setting.querySelector(`.${boundary} .date-text`)', $source);
        self::assertStringContainsString("capturePublishTimeSnapshot('startDate')", $source);
        self::assertStringContainsString("syncPublicationDateTimeFields('startDate')", $source);
        self::assertStringContainsString("capturePublishTimeSnapshot('endDate')", $source);
        self::assertStringContainsString("syncPublicationDateTimeFields('endDate')", $source);
    }
}
